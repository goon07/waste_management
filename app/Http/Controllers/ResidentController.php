<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Issue;
use App\Services\WasteManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller; // Add this import

class ResidentController extends Controller
{
    protected $wasteManagementService;

    public function __construct(WasteManagementService $wasteManagementService)
    {
        $this->middleware(['auth', 'role:resident']);
        $this->wasteManagementService = $wasteManagementService;
    }

/**  public function index()
    {
        return view('resident.dashboard');
    }*/

    public function index()
{
 $user = Auth::user();
 $residency = $user->residency;
 $collections = Collection::where('user_id', $user->id)
 ->orderBy('scheduled_date', 'desc')
 ->get();
 $lastPickup = $collections->where('status', 'completed')->first();
 $nextPickup = $this->calculateNextPickup($residency);
 return view('resident.dashboard', compact('user', 'residency', 'lastPickup', 'nextPickup'));
}

    public function services()
    {
        $user = Auth::user();
        $residency = $user->residency;
        $collections = Collection::where('user_id', $user->id)
            ->orderBy('scheduled_date', 'desc')
            ->get();
        $lastPickup = $collections->where('status', 'completed')->first();
        $nextPickup = $this->calculateNextPickup($residency);
        $companies = \App\Models\CollectorCompany::all();
        $wasteTypes = \App\Models\WasteType::all();
        $councils = \App\Models\Council::all();

        return view('resident.services', compact('user', 'residency', 'collections', 'lastPickup', 'nextPickup', 'companies', 'wasteTypes', 'councils'));
    }

   public function requestPickup(Request $request)
{
    $request->validate([
        'scheduled_date' => 'required|date|after:now',
        'collector_company_id' => 'required|exists:collector_companies,id',
        'waste_type' => 'required|exists:waste_types,id',
    ]);

    try {
        DB::beginTransaction();
        $residency = Auth::user()->residency;
        $collection = Collection::create([
            'user_id' => Auth::user()->id,
            'collector_company_id' => $request->collector_company_id,
            'council_id' => $residency->council_id ?? null,
            'waste_type' => $request->waste_type,
            'status' => 'pending',
            'scheduled_date' => $request->scheduled_date,
        ]);
        $this->wasteManagementService->logAction('request_pickup', 'Pickup requested', Auth::user()->id, 'collection', $collection->id);
        DB::commit();
        return redirect()->back()->with('success', 'Pickup requested successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('ResidentController: Error requesting pickup', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', $e->getMessage());
    }
}

    public function reportIssue(Request $request)
    {
        $request->validate([
            'issue_type' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'council_id' => 'required|exists:councils,id',
            'collector_company_id' => 'nullable|exists:collector_companies,id',
        ]);

        try {
            DB::beginTransaction();
            $issue = Issue::create([
                'user_id' => Auth::user()->id,
                'council_id' => $request->council_id,
                'collector_company_id' => $request->collector_company_id,
                'issue_type' => $request->issue_type,
                'description' => $request->description,
                'status' => 'open',
            ]);
            $this->wasteManagementService->logAction('report_issue', 'Issue reported', Auth::user()->id, 'issue', $issue->id);
            DB::commit();
            return redirect()->back()->with('success', 'Issue reported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ResidentController: Error reporting issue', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function calculateNextPickup($residency)
    {
        if (!$residency || !$residency->waste_collection_frequency) {
            return null;
        }

        $lastPickup = Collection::where('user_id', Auth::user()->id)
            ->where('status', 'completed')
            ->orderBy('completed_date', 'desc')
            ->first();

        $lastDate = $lastPickup && $lastPickup->completed_date ? $lastPickup->completed_date : now();
        $frequency = $residency->waste_collection_frequency;

        return match ($frequency) {
            'weekly' => $lastDate->addWeek(),
            'biweekly' => $lastDate->addWeeks(2),
            'monthly' => $lastDate->addMonth(),
            default => null,
        };
    }

    public function payments()
    {
        return view('resident.payments');
    }

    public function wasteGuide()
    {
        return view('resident.waste_guide');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}