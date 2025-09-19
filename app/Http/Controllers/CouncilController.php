<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Residency;
use App\Models\CollectorCompany;
use App\Models\CouncilRequest;
use App\Models\Collection;
use App\Models\Council;
use App\Models\Area;
use App\Models\Issue;
use App\Models\Payment;
use App\Models\UserBill;
use App\Services\WasteManagementService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;

class CouncilController extends Controller
{
    protected $wasteManagementService;

    public function __construct(WasteManagementService $wasteManagementService)
    {
        $this->middleware(['auth', 'role:council_admin']);
        $this->wasteManagementService = $wasteManagementService;
    }


 public function index()
    {
        $councilId = Auth::user()->council_id;

        // Count residents (users with role 'resident' in this council)
        $residentCount = User::where('council_id', $councilId)
            ->where('role', 'resident')
            ->count();

        // Residents who have paid
        $paidCount = User::where('council_id', $councilId)
            ->where('role', 'resident')
            ->where('payment_status', 'paid')
            ->count();

        // Residents who have not paid
        $unpaidCount = User::where('council_id', $councilId)
            ->where('role', 'resident')
            ->where('payment_status', 'pending')
            ->count();

        // Issues summary by status
        $issuesSummary = Issue::select('status', DB::raw('count(*) as total'))
            ->where('council_id', $councilId)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Last completed pickup
        $lastPickup = Collection::whereHas('resident', function ($query) use ($councilId) {
                $query->where('council_id', $councilId);
            })
            ->whereHas('schedules', function ($query) {
                $query->where('status', 'completed');
            })
            ->with(['wasteType', 'resident', 'schedules'])
            ->orderByDesc('collection_schedules.end_date')
            ->join('collection_schedules', 'collections.id', '=', 'collection_schedules.collection_id')
            ->first();

        // Next scheduled pickup
        $nextPickup = Collection::whereHas('resident', function ($query) use ($councilId) {
                $query->where('council_id', $councilId);
            })
            ->whereHas('schedules', function ($query) {
                $query->where('status', 'scheduled')
                      ->where('scheduled_date', '>', now());
            })
            ->with(['wasteType', 'resident', 'schedules'])
            ->orderBy('collection_schedules.scheduled_date', 'asc')
            ->join('collection_schedules', 'collections.id', '=', 'collection_schedules.collection_id')
            ->first();

          $collections = Collection::whereHas('resident', function ($query) use ($councilId) {
    $query->where('council_id', $councilId);
})
->with(['resident.residency.area', 'collector', 'wasteType'])
->paginate(10);

    return view('council.dashboard', compact(
        'residentCount', 'issuesSummary', 'lastPickup', 'nextPickup', 'paidCount', 'unpaidCount', 'collections'
    ));

       
    }




    public function pickups(Request $request)
    {
        try {
            $query = Collection::whereHas('resident', function ($query) use ($request) {
                $query->where('council_id', Auth::user()->council_id);
            })
                ->with(['resident', 'collector', 'wasteType', 'schedules']);

            if ($request->filled('status')) {
                $query->whereHas('schedules', function ($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            if ($request->filled('waste_type_id')) {
                $query->where('waste_type_id', $request->waste_type_id);
            }

            if ($request->filled('collector_id')) {
                $query->whereHas('schedules', function ($q) use ($request) {
                    $q->where('assigned_collector_id', $request->collector_id);
                });
            }

            if ($request->filled('date_range')) {
                [$start, $end] = explode(' to ', $request->date_range);
                $query->whereHas('schedules', function ($q) use ($start, $end) {
                    $q->whereBetween('scheduled_date', [$start, $end]);
                })->orWhere(function ($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end])
                      ->whereHas('schedules', function ($s) {
                          $s->where('status', 'pending');
                      });
                });
            }

            $sort = $request->input('sort', 'created_at');
            $direction = $request->input('direction', 'desc');
            if ($sort == 'resident_name') {
                $query->join('users', 'collections.resident_id', '=', 'users.id')
                      ->orderBy('users.name', $direction);
            } elseif ($sort == 'collector_name') {
                $query->leftJoin('collection_schedules', 'collections.id', '=', 'collection_schedules.collection_id')
                      ->leftJoin('users as collectors', 'collection_schedules.assigned_collector_id', '=', 'collectors.id')
                      ->orderBy('collectors.name', $direction);
            } else {
                $query->leftJoin('collection_schedules', 'collections.id', '=', 'collection_schedules.collection_id')
                      ->orderBy('collection_schedules.' . $sort, $direction);
            }

            $pickups = $query->paginate(20);
            $collectors = $this->wasteManagementService->getCollectors();

            return view('council.pickups', compact('pickups', 'collectors'));
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading pickups', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load pickups: ' . $e->getMessage());
        }
    }

    public function editPickup($id)
    {
        $pickup = Collection::with(['resident', 'wasteType', 'schedules'])->findOrFail($id);

        if (!$pickup->resident || $pickup->resident->council_id !== Auth::user()->council_id) {
            abort(403, 'Unauthorized');
        }

        $collectors = $this->wasteManagementService->getCollectors();

        return view('council.pickup-edit', compact('pickup', 'collectors'));
    }

    public function cancelPickup(Request $request, $id)
    {
        $pickup = Collection::with(['resident', 'schedules'])->findOrFail($id);

        if (!$pickup->resident || $pickup->resident->council_id !== Auth::user()->council_id) {
            abort(403, 'Unauthorized');
        }

        $schedule = $pickup->schedules()->where('status', 'scheduled')->first();
        if (!$schedule) {
            return redirect()->back()->with('error', 'No scheduled pickup found to cancel.');
        }

        $schedule->update([
            'status' => 'pending',
            'scheduled_date' => null,
            'assigned_collector_id' => null,
        ]);

        return redirect()->route('council.pickups')->with('success', 'Pickup cancelled successfully.');
    }

    public function schedulePickup(Request $request, $pickupId)
    {
        $request->validate([
            'collector_id' => 'required|uuid|exists:users,id',
            'scheduled_date' => 'required|date|after:today',
        ]);

        try {
            $pickup = Collection::with('resident')->findOrFail($pickupId);
            if (!$pickup->resident || $pickup->resident->council_id !== Auth::user()->council_id) {
                throw new \Exception('Unauthorized: Pickup does not belong to your council');
            }

            $pickup->schedules()->updateOrCreate(
                ['collection_id' => $pickup->id],
                [
                    'assigned_collector_id' => $request->collector_id,
                    'scheduled_date' => $request->scheduled_date,
                    'status' => 'scheduled',
                    'updated_at' => now(),
                ]
            );

            broadcast(new \App\Events\CollectionUpdated());

            $this->wasteManagementService->sendNotification(
                $pickup->resident_id,
                'Pickup Scheduled',
                "Your {$pickup->wasteType->name} pickup is scheduled for " . \Carbon\Carbon::parse($request->scheduled_date)->toDateString()
            );

            $this->wasteManagementService->logAction(
                'schedule_pickup',
                "Council admin scheduled pickup {$pickupId} with collector {$request->collector_id}",
                Auth::id(),
                'collection',
                $pickupId
            );

            return redirect()->route('council.pending-pickups')->with('success', 'Pickup scheduled successfully.');
        } catch (\Exception $e) {
            Log::error('CouncilController: Error scheduling pickup', [
                'error' => $e->getMessage(),
                'pickup_id' => $pickupId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

   
   

  

    public function assignCollectorCompanyToArea(Request $request)
    {
        $request->validate([
            'collector_company_id' => 'required|exists:collector_companies,id',
            'area_id' => 'required|exists:areas,id',
        ]);

        $councilId = Auth::user()->council_id;

        // Verify area belongs to council
        $area = Area::where('id', $request->area_id)->where('council_id', $councilId)->first();
        if (!$area) {
            return redirect()->back()->with('error', 'Selected area does not belong to your council.');
        }

        // Verify company assigned to council
        $companyAssigned = DB::table('council_collector_companies')
            ->where('council_id', $councilId)
            ->where('collector_company_id', $request->collector_company_id)
            ->exists();

        if (!$companyAssigned) {
            return redirect()->back()->with('error', 'Collector company is not assigned to your council.');
        }

        // Insert or update assignment in collector_company_areas
        DB::table('collector_company_areas')->updateOrInsert(
            ['collector_company_id' => $request->collector_company_id, 'area_id' => $request->area_id],
            ['created_at' => now()]
        );

        return redirect()->route('council.collectors')->with('success', 'Area assigned to collector company successfully.');
    }

    public function showCreateCollectorForm()
    {
        $councilId = Auth::user()->council_id;
        $council = Council::findOrFail($councilId);

        $collectorCompanies = [];
        if (!$council->employs_collectors) {
            $collectorCompanyIds = DB::table('council_collector_companies')
                ->where('council_id', $councilId)
                ->pluck('collector_company_id');
            $collectorCompanies = CollectorCompany::whereIn('id', $collectorCompanyIds)->get();
        }

        return view('council.collectors-create', compact('collectorCompanies', 'council'));
    }

    public function editCollector($id)
    {
        $collector = User::where('role', 'collector')->findOrFail($id);

        if ($collector->council_id !== Auth::user()->council_id) {
            abort(403, 'Unauthorized');
        }

        $council = Council::findOrFail(Auth::user()->council_id);

        $collectorCompanies = [];
        if (!$council->employs_collectors) {
            $collectorCompanyIds = DB::table('council_collector_companies')
                ->where('council_id', $council->id)
                ->pluck('collector_company_id');
            $collectorCompanies = CollectorCompany::whereIn('id', $collectorCompanyIds)->get();
        }

        return view('council.collectors-edit', compact('collector', 'collectorCompanies', 'council'));
    }

    public function storeCollector(Request $request)
    {
        $councilId = Auth::user()->council_id;
        $council = Council::findOrFail($councilId);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ];

        if (!$council->employs_collectors) {
            $rules['collector_company_id'] = 'required|exists:collector_companies,id';
        }

        $validated = $request->validate($rules);

        $collectorData = [
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => 'collector',
            'password' => bcrypt($validated['password']),
            'council_id' => $councilId,
            'is_active' => true,
        ];

        if (!$council->employs_collectors) {
            $collectorData['collector_company_id'] = $validated['collector_company_id'];
        }

        User::create($collectorData);

        return redirect()->route('council.collectors')->with('success', 'Collector created successfully.');
    }

    public function storeCompanyAdmin(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'collector_company_id' => 'required|exists:collector_companies,id',
        ];

        $validated = $request->validate($rules);

        $adminData = [
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => 'company_admin',
            'password' => bcrypt($validated['password']),
            'collector_company_id' => $validated['collector_company_id'],
            'is_active' => true,
        ];

        try {
            User::create($adminData);
            return redirect()->route('council.collectors')->with('success', 'Company admin created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create company admin: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to create company admin.');
        }
    }





    public function resetCompanyAdminPassword($id)
    {
        $admin = User::where('id', $id)->where('role', 'company_admin')->firstOrFail();

        $newPassword = Str::random(10);
        $admin->password = bcrypt($newPassword);
        $admin->save();

        return redirect()->back()->with('success', "Password reset successfully. New password: $newPassword");
    }

    public function updateCollector(Request $request, $id)
    {
        $collector = User::where('role', 'collector')->findOrFail($id);

        if ($collector->council_id !== Auth::user()->council_id) {
            abort(403, 'Unauthorized');
        }

        $council = Council::findOrFail(Auth::user()->council_id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $collector->id,
        ];

        if (!$council->employs_collectors) {
            $rules['collector_company_id'] = 'required|exists:collector_companies,id';
        }

        $validated = $request->validate($rules);

        $collector->name = $validated['name'];
        $collector->email = $validated['email'];

        if (!$council->employs_collectors) {
            $collector->collector_company_id = $validated['collector_company_id'];
        } else {
            $collector->collector_company_id = null;
        }

        $collector->save();

        return redirect()->route('council.collectors')->with('success', 'Collector updated successfully.');
    }

    public function deactivateCollector($id)
    {
        $collector = User::where('role', 'collector')->findOrFail($id);

        if ($collector->council_id !== Auth::user()->council_id) {
            abort(403, 'Unauthorized');
        }

        $collector->is_active = !$collector->is_active;
        $collector->save();

        return redirect()->route('council.collectors')->with('success', 'Collector status updated.');
    }

    public function storeCompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:collector_companies,name',
        ]);

        CollectorCompany::create(['name' => $request->name]);

        return redirect()->route('council.collectors')->with('success', 'Collector company created.');
    }

    public function updateCompany(Request $request, $id)
    {
        $company = CollectorCompany::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:collector_companies,name,' . $company->id,
        ]);

        $company->update(['name' => $request->name]);

        return redirect()->route('council.collectors')->with('success', 'Collector company updated.');
    }

    public function editCompany($id)
    {
        $company = CollectorCompany::findOrFail($id);
        return view('council.companies-edit', compact('company'));
    }


    public function exportPickups(Request $request)
    {
        try {
            return Excel::download(new PickupsExport($request, Auth::user()->council_id), 'pickups_' . now()->format('Y-m-d_His') . '.csv');
        } catch (\Exception $e) {
            Log::error('CouncilController: Error exporting pickups', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to export pickups: ' . $e->getMessage());
        }
    }

    public function users()
    {
        try {
            $councilId = Auth::user()->council_id;
            $requests = $this->wasteManagementService->getUserRequests($councilId);
            $collectorCompanies = $this->wasteManagementService->getCollectorCompanies()
                ->filter(function ($company) use ($councilId) {
                    return DB::table('council_collector_companies')
                        ->where('council_id', $councilId)
                        ->where('collector_company_id', $company->id)
                        ->exists();
                });

            $residents = $this->wasteManagementService->getResidents()
                ->filter(function ($resident) use ($councilId) {
                    return Residency::where('user_id', $resident->id)
                        ->where('council_id', $councilId)
                        ->exists();
                })
                ->map(function ($resident) {
                    $residency = Residency::where('user_id', $resident->id)->first();
                    $areaName = $residency && $residency->area ? $residency->area->name : null;
                    return [
                        'id' => $resident->id,
                        'name' => $resident->name,
                        'email' => $resident->email,
                        'address' => $residency->billing_address ?? $resident->address,
                        'payment_status' => $resident->payment_status,
                        'user_status' => $resident->user_status,
                        'area_name' => $areaName,
                    ];
                });

            $collectors = $this->wasteManagementService->getCollectors();
            return view('council.users', compact('requests', 'collectorCompanies', 'residents', 'collectors'));
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading users', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load users: ' . $e->getMessage());
        }
    }

    public function getUsers()
    {
        try {
            $councilId = Auth::user()->council_id;
            $requests = $this->wasteManagementService->getUserRequests($councilId);

            $residents = User::where('role', 'resident')
                ->where('council_id', $councilId)
                ->with(['residency.area'])
                ->get();

            $residentsData = $residents->map(function ($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'email' => $r->email,
                    'address' => $r->residency->billing_address ?? $r->address,
                    'payment_status' => $r->payment_status,
                    'user_status' => $r->user_status,
                    'area_name' => $r->residency && $r->residency->area ? $r->residency->area->name : null,
                ];
            })->values();

            $collectors = $this->wasteManagementService->getCollectors();
            $collectorCompanies = $this->wasteManagementService->getCollectorCompanies()
                ->filter(function ($company) use ($councilId) {
                    return DB::table('council_collector_companies')
                        ->where('council_id', $councilId)
                        ->where('collector_company_id', $company->id)
                        ->exists();
                });

            return view('council.users', [
                'requests' => $requests,
                'residents' => $residents,
                'residentsData' => $residentsData,
                'collectors' => $collectors,
                'collectorCompanies' => $collectorCompanies,
            ])->render();
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading users partial', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return response()->json(['error' => 'Failed to load users'], 500);
        }
    }

    public function getUsers2()
    {
        try {
            $councilId = Auth::user()->council_id;
            $requests = $this->wasteManagementService->getUserRequests($councilId);

            $residents = User::where('role', 'resident')
                ->where('council_id', $councilId)
                ->get();

            $residentsData = $residents->map(function ($r) {
                $residency = Residency::where('user_id', $r->id)->first();
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'email' => $r->email,
                    'address' => $residency ? $residency->billing_address : $r->address,
                    'payment_status' => $r->payment_status,
                    'user_status' => $r->user_status,
                    'area_name' => $residency && $residency->area ? $residency->area->name : null,
                ];
            })->values();

            $collectors = $this->wasteManagementService->getCollectors();
            $collectorCompanies = $this->wasteManagementService->getCollectorCompanies()
                ->filter(function ($company) use ($councilId) {
                    return DB::table('council_collector_companies')
                        ->where('council_id', $councilId)
                        ->where('collector_company_id', $company->id)
                        ->exists();
                });

            return view('council.users', [
                'requests' => $requests,
                'residents' => $residents,
                'residentsData' => $residentsData,
                'collectors' => $collectors,
                'collectorCompanies' => $collectorCompanies,
            ])->render();
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading users partial', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return response()->json(['error' => 'Failed to load users'], 500);
        }
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $collectorCompanies = CollectorCompany::all();
        $areas = Area::where('council_id', $user->council_id)->get();

        return view('council.user-edit', compact('user', 'collectorCompanies', 'areas'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:resident,collector',
            'collector_company_id' => 'nullable|exists:collector_companies,id',
            'address' => 'nullable|string|max:255',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'collector_company_id' => $validated['collector_company_id'] ?? null,
        ]);

        if ($user->role === 'resident') {
            Residency::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'council_id' => $user->council_id,
                    'billing_address' => $validated['address'] ?? null,
                    'area_id' => $validated['area_id'] ?? null,
                ]
            );
        } else {
            Residency::where('user_id', $user->id)->delete();
        }

        return redirect()->route('council.users')->with('success', 'User updated successfully.');
    }

    public function showCreateUserForm()
    {
        $collectorCompanies = CollectorCompany::all();
        $areas = Area::where('council_id', Auth::user()->council_id)->get();
        return view('council.users-create', compact('collectorCompanies', 'areas'));
    }

    public function pendingPickups()
    {
        try {
            $pendingPickups = $this->wasteManagementService->getPendingPickups(Auth::user()->council_id)->load('wasteType');
            $collectors = $this->wasteManagementService->getCollectors();
            return view('council.pending-pickups', compact('pendingPickups', 'collectors'));
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading pending pickups', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load pending pickups: ' . $e->getMessage());
        }
    }

    public function scheduledPickups()
    {
        try {
            $scheduledPickups = $this->wasteManagementService->getScheduledPickups(Auth::user()->council_id)->load('wasteType');
            return view('council.scheduled-pickups', compact('scheduledPickups'));
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading scheduled pickups', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load scheduled pickups: ' . $e->getMessage());
        }
    }

    public function completedPickups()
    {
        try {
            $completedPickups = $this->wasteManagementService->getCompletedPickups(Auth::user()->council_id)->load('wasteType');
            return view('council.completed-pickups', compact('completedPickups'));
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading completed pickups', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load completed pickups: ' . $e->getMessage());
        }
    }

    public function issues()
    {
        try {
            $issues = $this->wasteManagementService->getIssues(Auth::user()->council_id);
            return view('council.issues', compact('issues'));
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading issues', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load issues: ' . $e->getMessage());
        }
    }

    public function payments()
    {
        try {
            $payments = $this->wasteManagementService->getPayments(Auth::user()->council_id);
            return view('council.payments', compact('payments'));
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading payments', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load payments: ' . $e->getMessage());
        }
    }


public function reports()
{
    $user = auth()->user();
    $stats = [
        'users' => 0,
        'issues' => 0,
        'payments' => 0,
        'pickups' => 0,
    ];

    if ($user->role === 'management') {
        $stats['users'] = User::count();
        $stats['issues'] = Issue::count();
        $stats['payments'] = Payment::sum('amount');
        $stats['pickups'] = Collection::count();
    } elseif ($user->role === 'council_admin') {
        $councilId = $user->council_id;
        $stats['users'] = User::where('council_id', $councilId)->count();
        $stats['issues'] = Issue::where('council_id', $councilId)->count();
        $stats['payments'] = Payment::whereHas('user', function ($q) use ($councilId) {
            $q->where('council_id', $councilId);
        })->sum('amount');
        $stats['pickups'] = Collection::whereHas('resident', function ($q) use ($councilId) {
            $q->where('council_id', $councilId);
        })->count();
    } elseif ($user->role === 'collector_admin') {
        $companyId = $user->collector_company_id;
        $stats['users'] = User::where('collector_company_id', $companyId)->count();
        $stats['issues'] = Issue::where('collector_company_id', $companyId)->count();
        $stats['payments'] = Payment::whereHas('user', function ($q) use ($companyId) {
            $q->where('collector_company_id', $companyId);
        })->sum('amount');
        $stats['pickups'] = Collection::where('collector_company_id', $companyId)->count();
    }

    return view('reports', compact('stats', 'user'));
}

    public function wasteGuide()
    {
        try {
            return view('council.waste-guide');
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading waste guide', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load waste guide: ' . $e->getMessage());
        }
    }

    public function assignCollectorCompanyToCouncil(Request $request)
    {
        $request->validate([
            'collector_company_id' => 'required|exists:collector_companies,id',
        ]);

        $councilId = Auth::user()->council_id;
        $companyId = $request->collector_company_id;

        $exists = DB::table('council_collector_companies')
            ->where('council_id', $councilId)
            ->where('collector_company_id', $companyId)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'This company is already assigned to your council.');
        }

        DB::table('council_collector_companies')->insert([
            'council_id' => $councilId,
            'collector_company_id' => $companyId,
            'created_at' => now(),
        ]);

        return redirect()->route('council.collectors')->with('success', 'Collector company added to your council successfully.');
    }

    public function collectorsIndex()
    {
        try {
            $councilId = Auth::user()->council_id;
            $council = Council::findOrFail($councilId);

            if ($council->employs_collectors) {
                $collectors = User::where('council_id', $councilId)
                    ->where('role', 'collector')
                    ->get();

                return view('council.collectors_direct', compact('collectors'));
            } else {
                $assignedCompanyIds = DB::table('council_collector_companies')
                    ->where('council_id', $councilId)
                    ->pluck('collector_company_id')
                    ->toArray();

                $collectorCompanies = CollectorCompany::whereIn('id', $assignedCompanyIds)
                    ->with(['collectors', 'companyAdmin'])
                    ->get();

                $availableCompanies = CollectorCompany::whereNotIn('id', $assignedCompanyIds)->get();
                $areas = Area::where('council_id', $councilId)->get();
                $companyAreaAssignments = DB::table('collector_company_areas')
                    ->whereIn('collector_company_id', $assignedCompanyIds)
                    ->pluck('area_id', 'collector_company_id')
                    ->toArray();

                return view('council.collectors_companies', compact(
                    'collectorCompanies', 'availableCompanies', 'areas', 'companyAreaAssignments'
                ));
            }
        } catch (\Exception $e) {
            Log::error('Error fetching collectors: ' . $e->getMessage(), ['userId' => Auth::id()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch collectors'
            ], 500);
        }
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:resident,collector',
            'collector_company_id' => 'nullable|exists:collector_companies,id',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:255',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'id' => (string) Str::uuid(),
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'council_id' => Auth::user()->council_id,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            if ($request->role === 'resident') {
                Residency::create([
                    'user_id' => $user->id,
                    'council_id' => Auth::user()->council_id,
                    'billing_address' => $request->address,
                    'area_id' => $request->area_id,
                ]);
            }

            if ($request->role === 'collector' && $request->collector_company_id) {
                $exists = DB::table('council_collector_companies')
                    ->where('council_id', Auth::user()->council_id)
                    ->where('collector_company_id', $request->collector_company_id)
                    ->exists();

                if (!$exists) {
                    throw new \Exception('Invalid collector company for your council.');
                }

                $user->collector_company_id = $request->collector_company_id;
                $user->save();
            }

            DB::commit();
            return redirect()->route('council.users')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function resetUserPassword(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->council_id !== Auth::user()->council_id) {
                throw new \Exception('Unauthorized: User does not belong to your council');
            }

            $newPassword = Str::random(12);
            $user->update(['password' => Hash::make($newPassword)]);

            $this->wasteManagementService->sendNotification(
                $user->id,
                'Password Reset',
                "Your password has been reset by the council admin. New password: {$newPassword}"
            );

            $this->wasteManagementService->logAction(
                'reset_password',
                "Council admin reset password for user {$user->id}",
                Auth::id(),
                'user',
                $user->id
            );

            return redirect()->route('council.users')->with('success', 'Password reset successfully. User has been notified.');
        } catch (\Exception $e) {
            Log::error('CouncilController: Error resetting user password', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'reset_user_id' => $id,
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function assignCompany(Request $request, $id)
    {
        $request->validate([
            'collector_company_id' => 'required|integer|exists:collector_companies,id',
        ]);

        try {
            $user = User::findOrFail($id);
            if ($user->council_id !== Auth::user()->council_id || $user->role !== 'collector') {
                throw new \Exception('Unauthorized: User is not a collector or does not belong to your council');
            }

            $relationshipExists = DB::table('council_collector_companies')
                ->where('council_id', Auth::user()->council_id)
                ->where('collector_company_id', $request->collector_company_id)
                ->exists();
            if (!$relationshipExists) {
                throw new \Exception('Invalid council-collector company combination');
            }

            $user->update(['collector_company_id' => $request->collector_company_id]);

            $this->wasteManagementService->sendNotification(
                $user->id,
                'Collector Company Assigned',
                'You have been assigned to a new collector company.'
            );

            $this->wasteManagementService->logAction(
                'assign_company',
                "Council admin assigned user {$user->id} to collector company {$request->collector_company_id}",
                Auth::id(),
                'user',
                $user->id
            );

            return redirect()->route('council.users')->with('success', 'Collector company assigned successfully.');
        } catch (\Exception $e) {
            Log::error('CouncilController: Error assigning collector company', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'assign_user_id' => $id,
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function deactivateUser($id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->council_id !== Auth::user()->council_id) {
                throw new \Exception('Unauthorized: User does not belong to your council');
            }

            $user->update(['is_active' => !$user->is_active]);

            $this->wasteManagementService->sendNotification(
                $user->id,
                'Account Status Changed',
                'Your account has been ' . ($user->is_active ? 'activated' : 'deactivated') . ' by the council admin.'
            );

            $this->wasteManagementService->logAction(
                'deactivate_user',
                "Council admin " . ($user->is_active ? 'activated' : 'deactivated') . " user {$user->id}",
                Auth::id(),
                'user',
                $user->id
            );

            return redirect()->route('council.users')->with('success', 'User ' . ($user->is_active ? 'activated' : 'deactivated') . ' successfully.');
        } catch (\Exception $e) {
            Log::error('CouncilController: Error deactivating user', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'deactivate_user_id' => $id,
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function approveRequest(Request $request, $requestId)
    {
        $request->validate([
            'collector_company_id' => 'required|integer|exists:collector_companies,id',
        ]);

        try {
            $councilRequest = CouncilRequest::findOrFail($requestId);
            if ($councilRequest->council_id !== Auth::user()->council_id) {
                throw new \Exception('Unauthorized: Request does not belong to your council');
            }

            $relationshipExists = DB::table('council_collector_companies')
                ->where('council_id', Auth::user()->council_id)
                ->where('collector_company_id', $request->collector_company_id)
                ->exists();
            if (!$relationshipExists) {
                throw new \Exception('Invalid council-collector company combination');
            }

            $this->wasteManagementService->approveCouncilRequest($requestId);
            $councilRequest->update(['collector_company_id' => $request->collector_company_id]);

            Residency::create([
                'user_id' => $councilRequest->user_id,
                'council_id' => Auth::user()->council_id,
            ]);

            $user = User::find($councilRequest->user_id);
            if ($user->role === 'collector') {
                $user->update(['collector_company_id' => $request->collector_company_id]);
            }

            $this->wasteManagementService->sendNotification(
                $councilRequest->user_id,
                'Council Request Approved',
                'Your request has been approved and assigned to a collector company.'
            );

            $this->wasteManagementService->logAction(
                'approve_request',
                "Council admin approved request {$requestId}",
                Auth::id(),
                'council_request',
                $requestId
            );

            return redirect()->route('council.users')->with('success', 'Request approved successfully.');
        } catch (\Exception $e) {
            Log::error('CouncilController: Error approving request', [
                'error' => $e->getMessage(),
                'request_id' => $requestId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function rejectRequest($requestId)
    {
        try {
            $councilRequest = CouncilRequest::findOrFail($requestId);
            if ($councilRequest->council_id !== Auth::user()->council_id) {
                throw new \Exception('Unauthorized: Request does not belong to your council');
            }

            $this->wasteManagementService->rejectCouncilRequest($requestId);

            $this->wasteManagementService->sendNotification(
                $councilRequest->user_id,
                'Council Request Rejected',
                'Your council request was rejected. Please contact support for details.'
            );

            $this->wasteManagementService->logAction(
                'reject_request',
                "Council admin rejected request {$requestId}",
                Auth::id(),
                'council_request',
                $requestId
            );

            return redirect()->route('council.users')->with('success', 'Request rejected successfully.');
        } catch (\Exception $e) {
            Log::error('CouncilController: Error rejecting request', [
                'error' => $e->getMessage(),
                'request_id' => $requestId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }



    public function updateIssueStatus(Request $request, $issueId)
    {
        $request->validate([
            'status' => 'required|in:reported,in_progress,resolved',
        ]);

        try {
            $issue = $this->wasteManagementService->updateIssueStatus($issueId, $request->status);
            if ($issue->council_id !== Auth::user()->council_id) {
                throw new \Exception('Unauthorized: Issue does not belong to your council');
            }

            $this->wasteManagementService->sendNotification(
                $issue->user_id,
                'Issue Status Updated',
                "Your reported issue ({$issue->issue_type}) is now {$request->status}."
            );

            $this->wasteManagementService->logAction(
                'update_issue',
                "Council admin updated issue {$issueId} to status {$request->status}",
                Auth::id(),
                'issue',
                $issueId
            );

            return redirect()->route('council.issues')->with('success', 'Issue status updated successfully.');
        } catch (\Exception $e) {
            Log::error('CouncilController: Error updating issue status', [
                'error' => $e->getMessage(),
                'issue_id' => $issueId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function bills()
    {
        try {
            $bills = $this->wasteManagementService->getCouncilBills(Auth::user()->council_id);
            return view('council.payments', compact('bills'));
        } catch (\Exception $e) {
            Log::error('CouncilController: Error loading bills', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load bills: ' . $e->getMessage());
        }
    }

    public function billDetails($billId)
    {
        $bill = UserBill::with('user', 'payments')->findOrFail($billId);

        if ($bill->user->council_id !== Auth::user()->council_id) {
            abort(403, 'Unauthorized access to this bill.');
        }

        return view('council.bill_details', compact('bill'));
    }

    public function updatePaymentStatus(Request $request, $paymentId)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,failed',
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::with('user')->findOrFail($paymentId);

            if ($payment->user->council_id !== Auth::user()->council_id) {
                throw new \Exception('Unauthorized: Payment does not belong to your council');
            }

            $payment->status = $request->status;
            $payment->updated_at = now();
            $payment->save();

            if ($request->status === 'completed') {
                $bill = UserBill::where('user_id', $payment->user_id)
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->first();

                if (!$bill) {
                    $payment->user->increment('credit_balance', $payment->amount);
                } else {
                    BillPayment::create([
                        'bill_id' => $bill->id,
                        'payment_id' => $payment->id,
                        'amount_paid' => $payment->amount,
                    ]);

                    $totalPaid = $bill->billPayments()->sum('amount_paid');

                    if ($totalPaid == $bill->amount_due) {
                        $bill->status = 'paid';
                    } elseif ($totalPaid < $bill->amount_due) {
                        $bill->status = 'partial';
                    } else {
                        $bill->status = 'paid';
                        $excess = $totalPaid - $bill->amount_due;
                        $payment->user->increment('credit_balance', $excess);
                    }
                    $bill->save();
                }
            }

            $this->wasteManagementService->sendNotification(
                $payment->user_id,
                'Payment Status Updated',
                "Your payment of {$payment->amount} is now {$request->status}."
            );

            $this->wasteManagementService->logAction(
                'update_payment',
                "Council admin updated payment {$paymentId} to status {$request->status}",
                Auth::id(),
                'payment',
                $paymentId
            );

            DB::commit();
            return redirect()->route('council.payments')->with('success', 'Payment status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CouncilController: Error updating payment status', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}