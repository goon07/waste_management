<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Issue;
use App\Models\User;
use App\Models\Payment;
use App\Services\WasteManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller; // Add this import

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

    /**
 * Show all residents assigned to the company admin's collectors.
 */
public function residents()
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

    public function collectors()
    {
        $companyId = Auth::user()->collector_company_id;
        $collectors = User::where('role', 'collector')
            ->where('collector_company_id', $companyId)
            ->get();

        return view('management.collectors', compact('collectors'));
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
