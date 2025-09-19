<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\CollectionSchedule;
use App\Models\Area;
use App\Models\WasteType;
use App\Models\Issue;
use App\Models\User;
use App\Models\Payment;
use App\Models\Residency;
use App\Models\CollectorCompany;
use App\Services\WasteManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class CollectionCompanyAdminController extends Controller
{
    protected $wasteManagementService;

    public function __construct(WasteManagementService $wasteManagementService)
    {
        $this->middleware(['auth', 'role:company_admin']);
        $this->wasteManagementService = $wasteManagementService;
    }

   public function index(Request $request)
{
    try {
        $user = Auth::user();
        if (!$user || !$user->collector_company_id) {
            return redirect()->route('home')->with('error', 'Invalid user or company configuration.');
        }
        $companyId = (int) $user->collector_company_id;

        $company = CollectorCompany::findOrFail($companyId);

        $collectors = User::where('role', 'collector')
            ->where('collector_company_id', $companyId)
            ->select('id', 'name')
            ->get();

        $dateFrom = $request->input('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->input('date_to', now()->addDays(30)->toDateString());
        $areaId = $request->input('area_id') ? (int) $request->input('area_id') : null;
        $wasteTypeId = $request->input('waste_type_id') ? (int) $request->input('waste_type_id') : null;
        $collectorId = $request->input('collector_id');

        if ($collectorId && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $collectorId)) {
            return redirect()->back()->with('error', 'Invalid collector ID format.');
        }

        $servicedPeriod = (int) $request->input('serviced_period', 30);

        // Unified query with projected recurring schedules
        $completedCollections = CollectionSchedule::select(
            DB::raw("'completed' as type"),
            'users.name as resident_name',
            'areas.name as area_name',
            'collectors.name as collector_name',
            'waste_types.name as waste_type_name',
            'collection_schedules.scheduled_date',
            'collection_schedules.id as schedule_id',
            'collections.resident_id',
            'collection_schedules.schedule_type',
            'collection_schedules.recurring_day',
            'collection_schedules.monthly_day',
            DB::raw('2 as sort_order'),
            'collection_schedules.assigned_collector_id', // Updated to match model
            'collections.waste_type_id'
        )
            ->join('collections', 'collection_schedules.collection_id', '=', 'collections.id')
            ->join('users', 'collections.resident_id', '=', 'users.id')
            ->join('residency', 'users.id', '=', 'residency.user_id')
            ->join('areas', 'residency.area_id', '=', 'areas.id')
            ->join('waste_types', 'collections.waste_type_id', '=', 'waste_types.id')
            ->leftJoin('users as collectors', 'collection_schedules.assigned_collector_id', '=', 'collectors.id')
            ->where('collections.collector_company_id', $companyId)
            ->where('collection_schedules.status', 'completed')
            ->when($dateFrom, fn($q) => $q->whereDate('collection_schedules.scheduled_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('collection_schedules.scheduled_date', '<=', $dateTo))
            ->when($areaId, fn($q) => $q->where('residency.area_id', $areaId))
            ->when($wasteTypeId, fn($q) => $q->where('collections.waste_type_id', $wasteTypeId))
            ->when($collectorId, fn($q) => $q->where('collection_schedules.assigned_collector_id', $collectorId));

        $scheduledCollections = CollectionSchedule::select(
            DB::raw("'scheduled' as type"),
            'users.name as resident_name',
            'areas.name as area_name',
            'collectors.name as collector_name',
            'waste_types.name as waste_type_name',
            'collection_schedules.scheduled_date',
            'collection_schedules.id as schedule_id',
            'collections.resident_id',
            'collection_schedules.schedule_type',
            'collection_schedules.recurring_day',
            'collection_schedules.monthly_day',
            DB::raw('1 as sort_order'),
            'collection_schedules.assigned_collector_id', // Updated to match model
            'collections.waste_type_id'
        )
            ->join('collections', 'collection_schedules.collection_id', '=', 'collections.id')
            ->join('users', 'collections.resident_id', '=', 'users.id')
            ->join('residency', 'users.id', '=', 'residency.user_id')
            ->join('areas', 'residency.area_id', '=', 'areas.id')
            ->join('waste_types', 'collections.waste_type_id', '=', 'waste_types.id')
            ->leftJoin('users as collectors', 'collection_schedules.assigned_collector_id', '=', 'collectors.id')
            ->where('collections.collector_company_id', $companyId)
            ->where('collection_schedules.status', 'scheduled')
            ->whereDate('collection_schedules.scheduled_date', '>=', now()->toDateString())
            ->when($dateTo, fn($q) => $q->whereDate('collection_schedules.scheduled_date', '<=', $dateTo))
            ->when($areaId, fn($q) => $q->where('residency.area_id', $areaId))
            ->when($wasteTypeId, fn($q) => $q->where('collections.waste_type_id', $wasteTypeId))
            ->when($collectorId, fn($q) => $q->where('collection_schedules.assigned_collector_id', $collectorId));

        // Projected recurring schedules
        $recurringCollections = CollectionSchedule::select(
            DB::raw("'scheduled' as type"),
            'users.name as resident_name',
            'areas.name as area_name',
            'collectors.name as collector_name',
            'waste_types.name as waste_type_name',
            DB::raw('generate_series(
                collection_schedules.start_date,
                LEAST(collection_schedules.end_date, CURRENT_DATE + INTERVAL \'30 days\'),
                CASE
                    WHEN collection_schedules.schedule_type = \'weekly\' THEN \'1 week\'::interval
                    WHEN collection_schedules.schedule_type = \'biweekly\' THEN \'2 weeks\'::interval
                    WHEN collection_schedules.schedule_type = \'monthly\' THEN \'1 month\'::interval
                END
            ) as scheduled_date'),
            'collection_schedules.id as schedule_id',
            'collections.resident_id',
            'collection_schedules.schedule_type',
            'collection_schedules.recurring_day',
            'collection_schedules.monthly_day',
            DB::raw('1 as sort_order'),
            'collection_schedules.assigned_collector_id', // Updated to match model
            'collections.waste_type_id'
        )
            ->join('collections', 'collection_schedules.collection_id', '=', 'collections.id')
            ->join('users', 'collections.resident_id', '=', 'users.id')
            ->join('residency', 'users.id', '=', 'residency.user_id')
            ->join('areas', 'residency.area_id', '=', 'areas.id')
            ->join('waste_types', 'collections.waste_type_id', '=', 'waste_types.id')
            ->leftJoin('users as collectors', 'collection_schedules.assigned_collector_id', '=', 'collectors.id')
            ->where('collections.collector_company_id', $companyId)
            ->where('collection_schedules.schedule_type', '!=', 'one_time')
            ->where('collection_schedules.status', 'scheduled')
            ->where('collection_schedules.start_date', '<=', now()->addDays(30))
            ->where(function ($query) {
                $query->whereNull('collection_schedules.end_date')
                      ->orWhere('collection_schedules.end_date', '>=', now());
            })
            ->when($areaId, fn($q) => $q->where('residency.area_id', $areaId))
            ->when($wasteTypeId, fn($q) => $q->where('collections.waste_type_id', $wasteTypeId))
            ->when($collectorId, fn($q) => $q->where('collection_schedules.assigned_collector_id', $collectorId));

        $residentsWithoutCollections = User::select(
            DB::raw("'needs_scheduling' as type"),
            'users.name as resident_name',
            'areas.name as area_name',
            'collectors.name as collector_name',
            DB::raw('NULL as waste_type_name'),
            DB::raw('NULL as scheduled_date'),
            DB::raw('NULL as schedule_id'),
            'users.id as resident_id',
            DB::raw('NULL as schedule_type'),
            DB::raw('NULL as recurring_day'),
            DB::raw('NULL as monthly_day'),
            DB::raw('3 as sort_order'),
            DB::raw('NULL as assigned_collector_id'),
            DB::raw('NULL as waste_type_id')
        )
            ->join('residency', 'users.id', '=', 'residency.user_id')
            ->join('areas', 'residency.area_id', '=', 'areas.id')
            ->leftJoin('collector_resident_assignments', function ($join) use ($companyId) {
                $join->on('users.id', '=', 'collector_resident_assignments.resident_id')
                     ->where('collector_resident_assignments.collector_company_id', $companyId);
            })
            ->leftJoin('users as collectors', 'collector_resident_assignments.collector_id', '=', 'collectors.id')
            ->where('users.role', 'resident')
            ->where('users.collector_company_id', $companyId)
            ->whereNotExists(function ($query) use ($companyId, $servicedPeriod) {
                $query->select(DB::raw(1))
                      ->from('collections')
                      ->whereColumn('users.id', 'collections.resident_id')
                      ->where('collections.collector_company_id', $companyId)
                      ->whereExists(function ($subQuery) use ($servicedPeriod) {
                          $subQuery->select(DB::raw(1))
                                   ->from('collection_schedules')
                                   ->whereColumn('collections.id', 'collection_schedules.collection_id')
                                   ->whereIn('status', ['completed', 'scheduled'])
                                   ->whereDate('scheduled_date', '>=', now()->subDays($servicedPeriod));
                      });
            })
            ->when($areaId, fn($q) => $q->where('residency.area_id', $areaId));

        \Log::info('Executing dashboard queries', [
            'completed' => $completedCollections->toSql(),
            'scheduled' => $scheduledCollections->toSql(),
            'recurring' => $recurringCollections->toSql(),
            'residentsWithout' => $residentsWithoutCollections->toSql(),
            'bindings' => $residentsWithoutCollections->getBindings()
        ]);

        $combinedData = $completedCollections
            ->union($scheduledCollections)
            ->union($recurringCollections)
            ->union($residentsWithoutCollections)
            ->orderByRaw('sort_order, scheduled_date DESC NULLS LAST, resident_name')
            ->paginate(15);

        $residents = Cache::remember("residents_company_{$companyId}", now()->addHours(1), function () use ($companyId) {
            return User::where('role', 'resident')
                ->where('collector_company_id', $companyId)
                ->select('id', 'name')
                ->with([
                    'residency' => function ($q) {
                        $q->select('id', 'user_id', 'area_id');
                    },
                    'residency.area' => function ($q) {
                        $q->select('id', 'name');
                    }
                ])
                ->get();
        });

        $areas = Cache::remember("areas_company_{$companyId}", now()->addHours(24), function () use ($companyId) {
            return Area::whereIn('id', function ($query) use ($companyId) {
                $query->select('area_id')
                      ->from('collector_company_areas')
                      ->where('collector_company_id', $companyId);
            })->select('id', 'name')->get();
        });

        $wasteTypes = Cache::remember('waste_types', now()->addHours(24), function () {
            return WasteType::select('id', 'name')->get();
        });

        $this->wasteManagementService->logAction(
            'view_dashboard',
            "Company admin viewed dashboard",
            $user->id,
            'dashboard',
            null
        );

        return view('management.dashboard', compact(
            'company',
            'user',
            'collectors',
            'combinedData',
            'residents',
            'areas',
            'wasteTypes',
            'dateFrom',
            'dateTo',
            'areaId',
            'wasteTypeId',
            'collectorId',
            'servicedPeriod'
        ));
    } catch (\Exception $e) {
        Log::error('Error loading dashboard', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'An error occurred while loading the dashboard. Please try again.');
    }
}

    public function schedule(Request $request)
    {
        Log::info('Schedule request data', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
        ]);

        try {
            $validated = $request->validate([
                'schedule_for' => 'required|in:individual,area,all',
                'resident_id' => 'nullable|required_if:schedule_for,individual|uuid|exists:users,id',
                'area_id' => 'nullable|required_if:schedule_for,area|integer|exists:areas,id',
                'collector_id' => 'required|uuid|exists:users,id',
                'waste_type_id' => 'required|integer|exists:waste_types,id',
                'schedule_type' => 'required|in:one_time,weekly,biweekly,monthly',
                'specific_date' => 'nullable|required_if:schedule_type,one_time|date|after_or_equal:today',
                'recurring_day' => 'nullable|required_if:schedule_type,weekly,biweekly|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'monthly_day' => 'nullable|required_if:schedule_type,monthly|integer|between:1,31',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'nullable|date|after:start_date',
            ], [
                'waste_type_id.required' => 'Please select a waste type.',
                'waste_type_id.integer' => 'The selected waste type is invalid.',
                'waste_type_id.exists' => 'The selected waste type does not exist.',
                'schedule_type.in' => 'Invalid schedule type selected.',
                'specific_date.required_if' => 'Please select a date for a one-time schedule.',
                'recurring_day.required_if' => 'Please select a day for a weekly or biweekly schedule.',
                'monthly_day.required_if' => 'Please select a day for a monthly schedule.',
                'monthly_day.between' => 'Monthly day must be between 1 and 31.',
                'start_date.required' => 'Please select a start date.',
                'end_date.after' => 'End date must be after start date.',
            ]);

            $companyId = (int) Auth::user()->collector_company_id;

            DB::beginTransaction();

            if ($validated['schedule_for'] === 'individual') {
                $this->createCollectionAndSchedule(
                    $validated['resident_id'],
                    $validated['waste_type_id'],
                    $companyId,
                    $validated['collector_id'],
                    $validated['schedule_type'],
                    $validated['specific_date'] ?? null,
                    $validated['recurring_day'] ?? null,
                    $validated['monthly_day'] ?? null,
                    $validated['start_date'],
                    $validated['end_date'] ?? null
                );
            } elseif ($validated['schedule_for'] === 'area') {
                $residents = User::where('role', 'resident')
                    ->where('collector_company_id', $companyId)
                    ->whereHas('residency', fn($q) => $q->where('area_id', $validated['area_id']))
                    ->get();

                if ($residents->isEmpty()) {
                    throw ValidationException::withMessages(['area_id' => 'No residents found in the selected area.']);
                }

                foreach ($residents as $resident) {
                    $this->createCollectionAndSchedule(
                        $resident->id,
                        $validated['waste_type_id'],
                        $companyId,
                        $validated['collector_id'],
                        $validated['schedule_type'],
                        $validated['specific_date'] ?? null,
                        $validated['recurring_day'] ?? null,
                        $validated['monthly_day'] ?? null,
                        $validated['start_date'],
                        $validated['end_date'] ?? null
                    );
                }
            } elseif ($validated['schedule_for'] === 'all') {
                $residents = User::where('role', 'resident')
                    ->where('collector_company_id', $companyId)
                    ->get();

                if ($residents->isEmpty()) {
                    throw ValidationException::withMessages(['schedule_for' => 'No residents found in the company.']);
                }

                foreach ($residents as $resident) {
                    $this->createCollectionAndSchedule(
                        $resident->id,
                        $validated['waste_type_id'],
                        $companyId,
                        $validated['collector_id'],
                        $validated['schedule_type'],
                        $validated['specific_date'] ?? null,
                        $validated['recurring_day'] ?? null,
                        $validated['monthly_day'] ?? null,
                        $validated['start_date'],
                        $validated['end_date'] ?? null
                    );
                }
            }

            DB::commit();

            $this->wasteManagementService->logAction(
                'schedule_collection',
                "Company admin scheduled collection for {$validated['schedule_for']}",
                Auth::id(),
                'collection_schedule',
                null
            );

            return redirect()->route('management.dashboard')->with('success', 'Collection scheduled successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error scheduling collection', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error scheduling collection', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return redirect()->back()->with('error', 'Failed to schedule collection: ' . $e->getMessage())->withInput();
        }
    }



  protected function createCollectionAndSchedule($residentId, $wasteTypeId, $companyId, $collectorId, $scheduleType, $specificDate, $recurringDay, $monthlyDay, $startDate, $endDate)
{
    $collection = Collection::firstOrCreate([
        'resident_id' => $residentId,
        'waste_type_id' => $wasteTypeId,
        'collector_company_id' => $companyId,
    ], [
        'quantity' => 1.0,
        'priority' => 'medium',
    ]);

    CollectionSchedule::create([
        'collection_id' => $collection->id,
        'scheduled_date' => $scheduleType === 'one_time' ? $specificDate : null,
        'assigned_collector_id' => $collectorId, // Updated to match model
        'assigned_by_admin_id' => Auth::id(),
        'status' => 'scheduled',
        'schedule_type' => $scheduleType,
        'recurring_day' => in_array($scheduleType, ['weekly', 'biweekly']) ? $recurringDay : null,
        'monthly_day' => $scheduleType === 'monthly' ? $monthlyDay : null,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);
}
public function updateSchedule(Request $request, CollectionSchedule $collectionSchedule)
{
    try {
        $validated = $request->validate([
            'collector_id' => 'required|uuid|exists:users,id',
            'waste_type_id' => 'required|integer|exists:waste_types,id',
            'schedule_type' => 'required|in:one_time,weekly,biweekly,monthly',
            'specific_date' => 'required_if:schedule_type,one_time|date|after_or_equal:today',
            'recurring_day' => 'required_if:schedule_type,weekly,biweekly|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'monthly_day' => 'required_if:schedule_type,monthly|integer|between:1,31',
        ]);

        $collectionSchedule->update([
            'assigned_collector_id' => $validated['collector_id'], // Updated to match model
            'scheduled_date' => $validated['schedule_type'] === 'one_time' ? $validated['specific_date'] : null,
            'schedule_type' => $validated['schedule_type'],
            'recurring_day' => in_array($validated['schedule_type'], ['weekly', 'biweekly']) ? $validated['recurring_day'] : null,
            'monthly_day' => $validated['schedule_type'] === 'monthly' ? $validated['monthly_day'] : null,
        ]);

        $collectionSchedule->collection()->update([
            'waste_type_id' => $validated['waste_type_id'],
        ]);

        $this->wasteManagementService->logAction(
            'update_schedule',
            "Updated schedule {$collectionSchedule->id}",
            Auth::id(),
            'collection_schedule',
            $collectionSchedule->id
        );

        return redirect()->route('management.dashboard')->with('success', 'Schedule updated successfully.');
    } catch (\Exception $e) {
        Log::error('Error updating schedule', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'Failed to update schedule: ' . $e->getMessage());
    }
}
    public function complete(Request $request, CollectionSchedule $collectionSchedule)
    {
        try {
            $validated = $request->validate([
                'feedback_rating' => 'nullable|integer|between:1,5',
                'feedback_text' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();

            $collectionSchedule->update(['status' => 'completed']);
            $collectionSchedule->collection()->update([
                'confirmed_by_collector' => true,
                'confirmed_by_resident' => Auth::user()->role === 'resident',
                'feedback_rating' => $validated['feedback_rating'],
                'feedback_text' => $validated['feedback_text'],
            ]);

            // Trigger feedback notification to resident
            $this->wasteManagementService->sendNotification(
                $collectionSchedule->collection->resident_id,
                'Feedback Requested',
                'Please provide feedback for your recent collection.'
            );

            $this->wasteManagementService->logAction(
                'complete_collection',
                "Completed schedule {$collectionSchedule->id}",
                Auth::id(),
                'collection_schedule',
                $collectionSchedule->id
            );

            DB::commit();

            return redirect()->route('management.dashboard')->with('success', 'Collection marked as completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing collection', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to complete collection: ' . $e->getMessage());
        }
    }

    public function assignResidentsToCollector(Request $request)
    {
        $request->validate([
            'collector_id' => 'required|uuid|exists:users,id',
            'assignment_type' => 'required|in:area,individual,bulk',
            'area_id' => 'required_if:assignment_type,area|integer|exists:areas,id',
            'resident_ids' => 'required_if:assignment_type,individual|array',
            'resident_ids.*' => 'uuid|exists:users,id',
        ]);

        $companyId = (int) Auth::user()->collector_company_id;
        $collectorId = $request->collector_id;

        if ($request->assignment_type === 'area') {
            $residents = User::where('role', 'resident')
                ->where('collector_company_id', $companyId)
                ->whereHas('residency', fn($q) => $q->where('area_id', $request->area_id))
                ->get();
        } elseif ($request->assignment_type === 'individual') {
            $residents = User::where('role', 'resident')
                ->where('collector_company_id', $companyId)
                ->whereIn('id', $request->resident_ids)
                ->get();
        } else {
            $residents = User::where('role', 'resident')
                ->where('collector_company_id', $companyId)
                ->get();
        }

        if ($residents->isEmpty()) {
            return redirect()->back()->with('error', 'No residents found for the selected criteria.');
        }

        DB::beginTransaction();

        try {
            foreach ($residents as $resident) {
                \App\Models\CollectorResidentAssignment::updateOrCreate(
                    [
                        'collector_id' => $collectorId,
                        'resident_id' => $resident->id,
                    ],
                    [
                        'collector_company_id' => $companyId,
                    ]
                );
            }

            DB::commit();

            $this->wasteManagementService->logAction(
                'assign_residents',
                "Assigned residents to collector {$collectorId}",
                Auth::id(),
                'collector_resident_assignment',
                null
            );

            return redirect()->back()->with('success', 'Residents assigned to collector successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning residents to collector', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to assign residents: ' . $e->getMessage());
        }
    }

    public function residents()
    {
        try {
            $companyId = (int) Auth::user()->collector_company_id;

            $residents = User::where('role', 'resident')
                ->where('collector_company_id', $companyId)
                ->with(['residency.area', 'bills.payments' => function ($query) {
                    $query->orderBy('payment_date', 'desc')->limit(5);
                }])
                ->get();

            $areas = Area::whereHas('collectorCompanies', fn($q) => $q->where('collector_company_id', $companyId))
                ->get();

            return view('management.residents', compact('residents', 'areas'));
        } catch (\Exception $e) {
            Log::error('Error loading residents', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load residents: ' . $e->getMessage());
        }
    }

    public function collectors()
    {
        try {
            $companyId = (int) Auth::user()->collector_company_id;

            $collectors = User::where('role', 'collector')
                ->where('collector_company_id', $companyId)
                ->get();

            $areas = Area::whereHas('collectorCompanies', fn($q) => $q->where('collector_company_id', $companyId))
                ->get();

            $areaIds = $areas->pluck('id')->toArray();

            $residents = User::where('role', 'resident')
                ->where('collector_company_id', $companyId)
                ->whereHas('residency', fn($q) => $q->whereIn('area_id', $areaIds))
                ->get();

            return view('management.collectors', compact('collectors', 'residents', 'areas'));
        } catch (\Exception $e) {
            Log::error('Error loading collectors', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load collectors: ' . $e->getMessage());
        }
    }

    public function createCollector()
    {
        return view('management.collectors.create');
    }

    public function storeCollector(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $collector = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role' => 'collector',
            'collector_company_id' => Auth::user()->collector_company_id,
            'password' => bcrypt('password'),
            'active' => true,
        ]);

        $this->wasteManagementService->logAction(
            'create_collector',
            "Created collector {$collector->id}",
            Auth::id(),
            'user',
            $collector->id
        );

        return redirect()->route('management.collectors')->with('success', 'Collector created successfully.');
    }

    public function editCollector($id)
    {
        $collector = User::findOrFail($id);
        return view('management.collectors.edit', compact('collector'));
    }

    public function updateCollector(Request $request, $id)
    {
        $collector = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'active' => 'required|boolean',
        ]);

        $collector->update($request->only('name', 'phone_number', 'active'));

        $this->wasteManagementService->logAction(
            'update_collector',
            "Updated collector {$collector->id}",
            Auth::id(),
            'user',
            $collector->id
        );

        return redirect()->route('management.collectors')->with('success', 'Collector updated successfully.');
    }

    public function activateCollector($id)
    {
        $collector = User::findOrFail($id);
        $collector->update(['active' => true]);

        $this->wasteManagementService->logAction(
            'activate_collector',
            "Activated collector {$collector->id}",
            Auth::id(),
            'user',
            $collector->id
        );

        return redirect()->back()->with('success', 'Collector activated.');
    }

    public function deactivateCollector($id)
    {
        $collector = User::findOrFail($id);
        $collector->update(['active' => false]);

        $this->wasteManagementService->logAction(
            'deactivate_collector',
            "Deactivated collector {$collector->id}",
            Auth::id(),
            'user',
            $collector->id
        );

        return redirect()->back()->with('success', 'Collector deactivated.');
    }

    public function issues()
    {
        $companyId = (int) Auth::user()->collector_company_id;

        $issues = Issue::where('collector_company_id', $companyId)->get();
        $collectors = User::where('role', 'collector')
            ->where('collector_company_id', $companyId)
            ->get();

        return view('management.issues', compact('issues', 'collectors'));
    }

    public function createIssue()
    {
        return view('management.issues.create');
    }

    public function storeIssue(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $issue = Issue::create([
            'title' => $request->title,
            'description' => $request->description,
            'collector_company_id' => Auth::user()->collector_company_id,
            'active' => true,
        ]);

        $this->wasteManagementService->logAction(
            'create_issue',
            "Created issue {$issue->id}",
            Auth::id(),
            'issue',
            $issue->id
        );

        return redirect()->route('management.issues')->with('success', 'Issue created successfully.');
    }

    public function editIssue($id)
    {
        $issue = Issue::findOrFail($id);
        return view('management.issues.edit', compact('issue'));
    }

    public function updateIssue(Request $request, $id)
    {
        $issue = Issue::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        $issue->update($request->only('title', 'description', 'active'));

        $this->wasteManagementService->logAction(
            'update_issue',
            "Updated issue {$issue->id}",
            Auth::id(),
            'issue',
            $issue->id
        );

        return redirect()->route('management.issues')->with('success', 'Issue updated successfully.');
    }

    public function activateIssue($id)
    {
        $issue = Issue::findOrFail($id);
        $issue->update(['active' => true]);

        $this->wasteManagementService->logAction(
            'activate_issue',
            "Activated issue {$issue->id}",
            Auth::id(),
            'issue',
            $issue->id
        );

        return redirect()->back()->with('success', 'Issue activated.');
    }

    public function deactivateIssue($id)
    {
        $issue = Issue::findOrFail($id);
        $issue->update(['active' => false]);

        $this->wasteManagementService->logAction(
            'deactivate_issue',
            "Deactivated issue {$issue->id}",
            Auth::id(),
            'issue',
            $issue->id
        );

        return redirect()->back()->with('success', 'Issue deactivated.');
    }
}