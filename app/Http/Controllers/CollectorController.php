<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Issue;
use App\Services\WasteManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller; // Add this import

class CollectorController extends Controller
{
    protected $wasteManagementService;

    public function __construct(WasteManagementService $wasteManagementService)
    {
        $this->middleware(['auth', 'role:collector']);
        $this->wasteManagementService = $wasteManagementService;
    }

    public function index()
    {
        try {
            $assignedPickups = $this->wasteManagementService->getAssignedPickups(Auth::user()->id)->load('wasteType');
            $completedPickups = \App\Models\Collection::where('collector_id', Auth::user()->id)
                ->where('status', 'completed')
                ->with(['user', 'wasteType'])
                ->get();
            $allPickups = \App\Models\Collection::where('collector_id', Auth::user()->id)
                ->with(['user', 'wasteType'])
                ->orderBy('scheduled_date', 'desc')
                ->get();
            $wasteGuide = $this->wasteManagementService->getWasteGuide();

            $this->wasteManagementService->logAction(
                'view_dashboard',
                "Collector viewed dashboard",
                Auth::id(),
                'dashboard',
                null
            );

            return view('collector.routes', compact('assignedPickups', 'completedPickups', 'allPickups', 'wasteGuide'));
        } catch (\Exception $e) {
            Log::error('CollectorController: Error loading dashboard', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load dashboard: ' . $e->getMessage());
        }
    }

    public function confirmPickup($pickupId)
    {
        try {
            $pickup = \App\Models\Collection::findOrFail($pickupId);
            $this->wasteManagementService->confirmCollectorPickup($pickupId, Auth::user()->id);
            $pickup->update([
                'status' => 'completed',
                'last_updated' => now(),
            ]);

            $this->wasteManagementService->sendNotification(
                $pickup->user_id,
                'Pickup Completed',
                "Your {$pickup->wasteType->name} pickup has been completed. Please confirm."
            );

            $this->wasteManagementService->logAction(
                'confirm_pickup',
                "Collector confirmed pickup $pickupId",
                Auth::id(),
                'collection',
                $pickupId
            );

            return redirect()->back()->with('success', 'Pickup confirmed successfully.');
        } catch (\Exception $e) {
            Log::error('CollectorController: Error confirming pickup', [
                'error' => $e->getMessage(),
                'pickup_id' => $pickupId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reportIssue(Request $request, $pickupId)
    {
        $request->validate([
            'issue_type' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        try {
            $description = $pickupId == 0 ? $request->description : "{$request->description} (Pickup ID: $pickupId)";
            $this->wasteManagementService->reportIssue(
                Auth::user()->id,
                Auth::user()->council_id,
                $request->issue_type,
                $description
            );

            $this->wasteManagementService->logAction(
                'report_issue',
                "Collector reported issue" . ($pickupId == 0 ? '' : " for pickup $pickupId"),
                Auth::id(),
                'issue',
                null
            );

            return redirect()->back()->with('success', 'Issue reported successfully.');
        } catch (\Exception $e) {
            Log::error('CollectorController: Error reporting issue', [
                'error' => $e->getMessage(),
                'pickup_id' => $pickupId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}