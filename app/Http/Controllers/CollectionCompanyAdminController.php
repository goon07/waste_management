<?php

namespace App\Http\Controllers;


use App\Models\Collection;
use App\Models\CollectionSchedule;

use App\Models\Issue;
use App\Models\User;
use App\Models\Payment;
use App\Models\Residency;
use App\Models\CollectorCompany;
use App\Services\WasteManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller; // Add this import
use App\Models\CollectorResidentAssignment;

use Illuminate\Support\Facades\DB;




class CollectionCompanyAdminController extends Controller
{
    protected $wasteManagementService;

    public function __construct(WasteManagementService $wasteManagementService)
    {
        $this->middleware(['auth', 'role:company_admin']);
        $this->wasteManagementService = $wasteManagementService;
    }

    /**
     * Dashboard view.
     */




public function index()
{
    try {
        $user = Auth::user();
        $companyId = $user->collector_company_id;

        $company = \App\Models\CollectorCompany::find($companyId);

        $collectors = User::where('role', 'collector')
            ->where('collector_company_id', $companyId)
            ->get();

        // Past collections: completed schedules with completed collections
        $pastCollections = CollectionSchedule::with(['collection.user.residency.area', 'collector'])
            ->whereHas('collection', function ($q) use ($companyId) {
                $q->where('collector_company_id', $companyId);
            })
            ->where('status', 'completed')
            ->orderBy('scheduled_date', 'desc')
            ->limit(10)
            ->get();

        // Next scheduled collections (scheduled_date today or later)
        $nextScheduledCollections = CollectionSchedule::with(['collection.user.residency.area', 'collector'])
            ->whereHas('collection', function ($q) use ($companyId) {
                $q->where('collector_company_id', $companyId);
            })
            ->where('status', 'scheduled')
            ->whereDate('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->get();

        // Residents without recent collections (no completed or scheduled collection in last 30 days)
        $recentCollectionUserIds = CollectionSchedule::whereHas('collection', function ($q) use ($companyId) {
                $q->where('collector_company_id', $companyId);
            })
            ->whereIn('status', ['completed', 'scheduled'])
            ->whereDate('scheduled_date', '>=', now()->subDays(30))
            ->pluck('collection_id')
            ->unique();

        // Get user ids from collections
        $recentUserIds = Collection::whereIn('id', $recentCollectionUserIds)->pluck('user_id')->unique();

        $residentsWithoutRecentCollections = User::where('role', 'resident')
            ->where('collector_company_id', $companyId)
            ->whereHas('residency')
            ->whereNotIn('id', $recentUserIds)
            ->with('residency.area')
            ->get();

        $residents = User::where('role', 'resident')->where('collector_company_id', $companyId)->get();

        $areas = \App\Models\Area::whereIn('id', function ($query) use ($companyId) {
            $query->select('area_id')->from('collector_company_areas')->where('collector_company_id', $companyId);
        })->get();

        $wasteTypes = \App\Models\WasteType::all();

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
            'pastCollections',
            'nextScheduledCollections',
            'residentsWithoutRecentCollections',
            'residents',
            'areas',
            'wasteTypes'
        ));
    } catch (\Exception $e) {
        Log::error('Error loading dashboard', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id(),
        ]);
        return redirect()->back()->with('error', 'Failed to load dashboard: ' . $e->getMessage());
    }
}








public function scheduleCollection(Request $request)
{
    $request->validate([
        'schedule_for' => 'required|in:individual,area,all',
        'resident_id' => 'required_if:schedule_for,individual|exists:users,id',
        'area_id' => 'required_if:schedule_for,area|exists:areas,id',
        'schedule_type' => 'required|in:specific_date,weekly,biweekly,monthly',
        'specific_date' => 'required_if:schedule_type,specific_date|date|after_or_equal:today',
        'weekly_day' => 'required_if:schedule_type,weekly|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        'biweekly_day' => 'required_if:schedule_type,biweekly|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        'monthly_day' => 'required_if:schedule_type,monthly|integer|between:1,28',
        'waste_type' => 'required|exists:waste_types,id',
        'collector_id' => 'required|exists:users,id',
    ]);

    $companyId = Auth::user()->collector_company_id;
    $adminId = Auth::id();

    // Determine residents to schedule for
    if ($request->schedule_for === 'individual') {
        $residents = User::where('id', $request->resident_id)->get();
    } elseif ($request->schedule_for === 'area') {
        $residents = User::where('role', 'resident')
            ->whereHas('residency', function ($q) use ($request, $companyId) {
                $q->where('area_id', $request->area_id)
                  ->where('collector_company_id', $companyId);
            })->get();
    } else { // all residents assigned to company
        $residents = User::where('role', 'resident')
            ->where('collector_company_id', $companyId)
            ->get();
    }

    foreach ($residents as $resident) {
        // Find or create a collection record for this resident and waste type
        $collection = Collection::firstOrCreate([
            'user_id' => $resident->id,
            'waste_type' => $request->waste_type,
            'collector_company_id' => $companyId,
        ], [
            'status' => 'scheduled',
            'collector_id' => $request->collector_id,
            'scheduled_date' => $request->specific_date ?? null, // optional, will be managed in schedules
        ]);

        // Schedule based on type
        switch ($request->schedule_type) {
            case 'specific_date':
                $this->createSchedule($collection->id, $request->specific_date, $request->collector_id, $adminId);
                break;

            case 'weekly':
                $this->createRecurringSchedules($collection->id, $request->weekly_day, 1, $request->collector_id, $adminId);
                break;

            case 'biweekly':
                $this->createRecurringSchedules($collection->id, $request->biweekly_day, 2, $request->collector_id, $adminId);
                break;

            case 'monthly':
                $this->createMonthlySchedules($collection->id, $request->monthly_day, 3, $request->collector_id, $adminId);
                break;
        }
    }

    return redirect()->back()->with('success', 'Collections scheduled successfully.');
}

protected function createSchedule($collectionId, $date, $collectorId, $adminId)
{
    CollectionSchedule::create([
        'collection_id' => $collectionId,
        'scheduled_date' => $date,
        'assigned_collector_id' => $collectorId,
        'assigned_by_admin_id' => $adminId,
        'status' => 'scheduled',
    ]);
}

protected function createRecurringSchedules($collectionId, $weekday, $weekInterval, $collectorId, $adminId)
{
    $startDate = now()->next($weekday);
    for ($i = 0; $i < 4 * $weekInterval; $i += $weekInterval) {
        $this->createSchedule($collectionId, $startDate->copy()->addWeeks($i), $collectorId, $adminId);
    }
}

protected function createMonthlySchedules($collectionId, $dayOfMonth, $monthsCount, $collectorId, $adminId)
{
    $startDate = now()->day($dayOfMonth);
    if ($startDate->isPast()) {
        $startDate->addMonth();
    }
    for ($i = 0; $i < $monthsCount; $i++) {
        $this->createSchedule($collectionId, $startDate->copy()->addMonths($i), $collectorId, $adminId);
    }
}








public function createCollection(Request $request)
{
    $residentId = $request->query('resident_id');

    // Fetch resident and related data to pre-fill form
    $resident = User::with('residency.area')->findOrFail($residentId);

    $collectors = User::where('role', 'collector')
        ->where('collector_company_id', Auth::user()->collector_company_id)
        ->get();

    $wasteTypes = WasteType::all();

    return view('management.collections.create', compact('resident', 'collectors', 'wasteTypes'));
}



/**
 * Helper to schedule recurring weekly/biweekly collections
 */
protected function scheduleRecurring($residentId, $collectorId, $wasteType, $weekday, $weekInterval, $companyId)
{
    $startDate = now()->next($weekday);
    for ($i = 0; $i < 4 * $weekInterval; $i += $weekInterval) {
        Collection::create([
            'user_id' => $residentId,
            'collector_id' => $collectorId,
            'waste_type' => $wasteType,
            'scheduled_date' => $startDate->copy()->addWeeks($i),
            'collector_company_id' => $companyId,
            'status' => 'scheduled',
        ]);
    }
}

/**
 * Helper to schedule monthly collections
 */
protected function scheduleMonthly($residentId, $collectorId, $wasteType, $dayOfMonth, $monthsCount, $companyId)
{
    $startDate = now()->day($dayOfMonth);
    if ($startDate->isPast()) {
        $startDate->addMonth();
    }
    for ($i = 0; $i < $monthsCount; $i++) {
        Collection::create([
            'user_id' => $residentId,
            'collector_id' => $collectorId,
            'waste_type' => $wasteType,
            'scheduled_date' => $startDate->copy()->addMonths($i),
            'collector_company_id' => $companyId,
            'status' => 'scheduled',
        ]);
    }
}






public function storeCollection(Request $request)
{
    $request->validate([
        'resident_id' => 'required|exists:users,id',
        'collector_id' => 'required|exists:users,id',
        'waste_type' => 'required|exists:waste_types,id',
        'scheduled_date' => 'required|date|after_or_equal:today',
    ]);

    $collection = new Collection();
    $collection->user_id = $request->resident_id;
    $collection->collector_id = $request->collector_id;
    $collection->waste_type = $request->waste_type;
    $collection->scheduled_date = $request->scheduled_date;
    $collection->collector_company_id = Auth::user()->collector_company_id;
    $collection->status = 'scheduled';
    $collection->save();

    return redirect()->route('management.dashboard')->with('success', 'Collection scheduled successfully.');
}

    public function indexold()
    {
        try {
            $companyId = Auth::user()->collector_company_id;

            $collectors = User::where('role', 'collector')
                ->where('collector_company_id', $companyId)
                ->get();

            $pendingPickups = Collection::with('wasteType')
                ->where('collector_company_id', $companyId)
                ->whereIn('status', ['pending', 'scheduled'])
                ->get();

            $residents = User::where('role', 'resident')
                ->whereHas('residency', fn($q) => $q->where('collector_company_id', $companyId))
                ->get();

            $reports = $this->wasteManagementService->getReports($companyId);

            $this->wasteManagementService->logAction(
                'view_dashboard',
                "Company admin viewed dashboard",
                Auth::id(),
                'dashboard',
                null
            );

            return view('management.dashboard', compact(
                'collectors',
                'pendingPickups',
                'residents',
                'reports'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading dashboard', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load dashboard: ' . $e->getMessage());
        }
    }




public function assignResidentsToCollector(Request $request)
{
    $request->validate([
        'collector_id' => 'required|uuid|exists:users,id',
        'assignment_type' => 'required|in:area,individual,bulk',
        'area' => 'required_if:assignment_type,area|string|nullable',
        'resident_ids' => 'required_if:assignment_type,individual|array',
        'resident_ids.*' => 'uuid|exists:users,id',
    ]);

    $companyId = Auth::user()->collector_company_id;
    $collectorId = $request->collector_id;

    if ($request->assignment_type === 'area') {
        $areaName = $request->area;

        // Fetch area ID by name
        $area = \App\Models\Area::where('name', $areaName)->first();

        if (!$area) {
            return redirect()->back()->with('error', 'Area not found.');
        }

        // Get resident user IDs who have residency in this area and belong to the company
        $residentUserIds = \App\Models\Residency::where('area_id', $area->id)
            ->whereHas('user', function($query) use ($companyId) {
                $query->where('collector_company_id', $companyId)
                      ->where('role', 'resident');
            })
            ->pluck('user_id');

        $residents = \App\Models\User::whereIn('id', $residentUserIds)->get();

    } elseif ($request->assignment_type === 'individual') {
        $residentIds = $request->resident_ids;

        $residents = \App\Models\User::where('role', 'resident')
            ->where('collector_company_id', $companyId)
            ->whereIn('id', $residentIds)
            ->get();

    } else { // bulk
        $residents = \App\Models\User::where('role', 'resident')
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

        return redirect()->back()->with('success', 'Residents assigned to collector successfully.');

    } catch (\Exception $e) {
        DB::rollBack();

        \Log::error('Error assigning residents to collector', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('error', 'Failed to assign residents: ' . $e->getMessage());
    }
}



public function assignResidentsToCollectorold(Request $request)
{
    $request->validate([
        'collector_id' => 'required|uuid|exists:users,id',
        'assignment_type' => 'required|in:area,individual,bulk',
        'area' => 'required_if:assignment_type,area|string|nullable',
        'resident_ids' => 'required_if:assignment_type,individual|array',
        'resident_ids.*' => 'uuid|exists:users,id',
    ]);

    $companyId = Auth::user()->collector_company_id;
    $collectorId = $request->collector_id;

    // Determine residents to assign
    if ($request->assignment_type === 'area') {
        $area = $request->area;

        $residents = User::where('role', 'resident')
            ->where('collector_company_id', $companyId)
            ->where('area', $area) // Adjust if area stored differently
            ->get();

    } elseif ($request->assignment_type === 'individual') {
        $residentIds = $request->resident_ids;

        $residents = User::where('role', 'resident')
            ->where('collector_company_id', $companyId)
            ->whereIn('id', $residentIds)
            ->get();

    } else { // bulk
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
            // Upsert assignment to avoid duplicates
            CollectorResidentAssignment::updateOrCreate(
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

        return redirect()->back()->with('success', 'Residents assigned to collector successfully.');

    } catch (\Exception $e) {
        DB::rollBack();

        \Log::error('Error assigning residents to collector', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('error', 'Failed to assign residents: ' . $e->getMessage());
    }
}





    /**
 * Show all residents assigned to the company admin's collectors.
 */
public function residentsold()
{
    try {
        $companyId = Auth::user()->collector_company_id;

        // Get residents assigned to this company via residency
        $residents = User::where('role', 'resident')
            ->whereHas('residency', function($q) use ($companyId) {
                $q->where('collector_company_id', $companyId);
            })
            ->get();

        return view('management.residents', compact('residents'));
    } catch (\Exception $e) {
        Log::error('CollectionCompanyAdminController: Error loading residents', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('error', 'Failed to load residents: ' . $e->getMessage());
    }
}
public function residents()
{
    try {
        $companyId = Auth::user()->collector_company_id;

        // Get residents assigned to this company via residency, eager load payments
     $residents = User::where('role', 'resident')
    ->whereHas('residency', function($q) use ($companyId) {
        $q->where('collector_company_id', $companyId);
    })
    ->with(['bills.payments' => function($query) {
        $query->orderBy('payment_date', 'desc')->limit(5);
    }])
    ->get();


$areas = \App\Models\Area::whereHas('collectorCompanies', function($q) use ($companyId) {
    $q->where('collector_company_id', $companyId);
})->get();
      

        return view('management.residents', compact('residents'));
    } catch (\Exception $e) {
        Log::error('CollectionCompanyAdminController: Error loading residents', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('error', 'Failed to load residents: ' . $e->getMessage());
    }
}


    /**
     * Reports view (read-only).
     */
    public function reports()
    {
        $companyId = Auth::user()->collector_company_id;
        $reports = $this->wasteManagementService->getReports($companyId);

        return view('management.reports', compact('reports'));
    }

    /**
     * Assign a collector to a pickup.
     */
    public function assignCollector(Request $request, $pickupId)
    {
        $request->validate([
            'collector_id'   => 'required|uuid|exists:users,id',
            'scheduled_date' => 'required|date|after:today',
        ]);

        try {
            $pickup = Collection::findOrFail($pickupId);

            if ($pickup->collector_company_id !== Auth::user()->collector_company_id) {
                throw new \Exception('Unauthorized: Pickup does not belong to your company');
            }

            $pickup->update([
                'collector_id'   => $request->collector_id,
                'scheduled_date' => $request->scheduled_date,
                'status'         => 'scheduled',
            ]);

            $this->wasteManagementService->sendNotification(
                $request->collector_id,
                'New Pickup Assigned',
                "You have a new {$pickup->wasteType->name} pickup scheduled for " . \Carbon\Carbon::parse($request->scheduled_date)->toDateString()
            );

            $this->wasteManagementService->logAction(
                'assign_collector',
                "Assigned pickup $pickupId to collector {$request->collector_id}",
                Auth::id(),
                'collection',
                $pickupId
            );

            return redirect()->back()->with('success', 'Pickup assigned successfully.');
        } catch (\Exception $e) {
            Log::error('Error assigning collector', [
                'error' => $e->getMessage(),
                'pickup_id' => $pickupId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Send a message to a resident.
     */
    public function sendResidentMessage(Request $request, $residentId)
    {
        $request->validate(['message' => 'required|string']);

        try {
            $residency = \App\Models\Residency::where('user_id', $residentId)
                ->where('collector_company_id', Auth::user()->collector_company_id)
                ->firstOrFail();

            $this->wasteManagementService->sendNotification(
                $residentId,
                'Message from Collection Company',
                $request->message
            );

            $this->wasteManagementService->logAction(
                'send_message',
                "Sent message to resident $residentId",
                Auth::id(),
                'notification',
                null
            );

            return redirect()->back()->with('success', 'Message sent.');
        } catch (\Exception $e) {
            Log::error('Error sending message', [
                'error' => $e->getMessage(),
                'resident_id' => $residentId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ================== Collectors Management ==================

    public function collectorsold()
    {
 
  $companyId = Auth::user()->collector_company_id;

  $collectors = User::where('role', 'collector')
      ->where('collector_company_id', $companyId)
      ->get();

//  $residents = User::where('role', 'resident')->where('collector_company_id', $companyId)->get();

      
$areas = \App\Models\Area::whereHas('collectorCompanies', function($q) use ($companyId) {
    $q->where('collector_company_id', $companyId);
})->get();

$areaIds = $areas->pluck('id')->toArray();
$residents = User::where('role', 'resident')
    ->where('collector_company_id', $companyId)
    ->whereHas('residency', function($query) use ($areaIds) {
        $query->whereIn('area_id', $areaIds);
    })
    ->get();

  return view('management.collectors', compact('collectors', 'residents','areas'));
 
    }

public function collectors1()
{
    $companyId = Auth::user()->collector_company_id;

    // Collectors of the company
    $collectors = User::where('role', 'collector')
        ->where('collector_company_id', $companyId)
        ->get();

    // Areas assigned to the company
    $areas = \App\Models\Area::whereHas('collectorCompanies', function($q) use ($companyId) {
        $q->where('collector_company_id', $companyId);
    })->get();

    $areaIds = $areas->pluck('id')->toArray();

    // Residents assigned to the company whose residency area is in the company's areas
    $residents = User::where('role', 'resident')
        ->where('collector_company_id', $companyId)
        ->whereHas('residency', function($query) use ($areaIds) {
            $query->whereIn('area_id', $areaIds);
        })
        ->get();

    return view('management.collectors', compact('collectors', 'residents', 'areas'));
}


public function collectors()
{
    $companyId = Auth::user()->collector_company_id;

    // Collectors of the company
    $collectors = User::where('role', 'collector')
        ->where('collector_company_id', $companyId)
        ->get();

    // Fetch areas assigned to the company using query builder
    $areas = \DB::table('collector_company_areas as cca')
        ->join('areas as a', 'cca.area_id', '=', 'a.id')
        ->where('cca.collector_company_id', $companyId)
        ->select('a.id', 'a.name')
        ->get();

    $areaIds = $areas->pluck('id')->toArray();

    // Residents assigned to the company whose residency area is in the company's areas
    $residents = User::where('role', 'resident')
    //    ->where('collector_company_id', $companyId)
        ->whereHas('residency', function($query) use ($areaIds) {
            $query->whereIn('area_id', $areaIds);
        })
        ->get();

    // Debug output - remove or comment out after debugging
    // dd([
    //     'collectors_count' => $collectors->count(),
    //     'areas_count' => $areas->count(),
    //     'residents_count' => $residents->count(),
    //     'collectors_sample' => $collectors->take(3),
    //     'areas_sample' => $areas->take(3),
    //     'residents_sample' => $residents->take(3),
    // ]);

    return view('management.collectors', compact('collectors', 'residents', 'areas'));
}









    public function createCollector()
    {
        return view('management.collectors');
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
            'password' => bcrypt('password'), // default password
            'active' => true,
        ]);

        return redirect()->route('management.collectors')->with('success', 'Collector created successfully.');
    }

    public function editCollector($id)
    {
        $collector = User::findOrFail($id);
        return view('management.collectors', compact('collector'));
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

        return redirect()->route('management.collectors')->with('success', 'Collector updated successfully.');
    }

    public function activateCollector($id)
    {
        $collector = User::findOrFail($id);
        $collector->update(['active' => true]);
        return redirect()->back()->with('success', 'Collector activated.');
    }

    public function deactivateCollector($id)
    {
        $collector = User::findOrFail($id);
        $collector->update(['active' => false]);
        return redirect()->back()->with('success', 'Collector deactivated.');
    }

    // ================== Issues Management ==================

  public function issues()
{
    $companyId = Auth::user()->collector_company_id;

    // Get issues for this company
    $issues = Issue::where('collector_company_id', $companyId)->get();

    // Get collectors assigned to this company (adjust relation if needed)
    $collectors = User::where('collector_company_id', $companyId)
        ->where('role', 'collector')
        ->get();

    return view('management.issues', compact('issues', 'collectors'));
}


    public function createIssue()
    {
        return view('management.issues');
    }

    public function storeIssue(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Issue::create([
            'title' => $request->title,
            'description' => $request->description,
            'collector_company_id' => Auth::user()->collector_company_id,
            'active' => true,
        ]);

        return redirect()->route('management.issues')->with('success', 'Issue created successfully.');
    }

    public function editIssue($id)
    {
        $issue = Issue::findOrFail($id);
        return view('management.issues', compact('issue'));
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

        return redirect()->route('management.issues')->with('success', 'Issue updated successfully.');
    }

    public function activateIssue($id)
    {
        $issue = Issue::findOrFail($id);
        $issue->update(['active' => true]);
        return redirect()->back()->with('success', 'Issue activated.');
    }

    public function deactivateIssue($id)
    {
        $issue = Issue::findOrFail($id);
        $issue->update(['active' => false]);
        return redirect()->back()->with('success', 'Issue deactivated.');
    }
}
