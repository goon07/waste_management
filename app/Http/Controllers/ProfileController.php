<?php

namespace App\Http\Controllers;

use App\Services\WasteManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller; // Add this import

class ProfileController extends Controller
{
    protected $wasteManagementService;

    public function __construct(WasteManagementService $wasteManagementService)
    {
        $this->middleware(['auth', 'role:resident']);
        $this->wasteManagementService = $wasteManagementService;
    }

    public function index()
    {
        $user = Auth::user();
        $residency = $user->residency;
        $councils = \App\Models\Council::all();
        $companies = \App\Models\CollectorCompany::all();
        $userRequests = \App\Models\CouncilRequest::where('user_id', $user->id)->get();
        return view('resident.profile', compact('user', 'residency', 'councils', 'companies', 'userRequests'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'notifications_enabled' => 'boolean',
            'household_size' => 'nullable|integer|min:1',
            'waste_collection_frequency' => 'nullable|in:weekly,biweekly,monthly',
            'billing_address' => 'nullable|string|max:255',
            'council_id' => 'nullable|exists:councils,id',
            'collector_company_id' => 'nullable|exists:collector_companies,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        try {
            DB::beginTransaction();

            // Update user details
            $this->wasteManagementService->updateProfile(Auth::user()->id, [
                'name' => $request->name,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'notifications_enabled' => $request->boolean('notifications_enabled'),
            ]);

            // Update or create residency
            $residencyData = [
                'user_id' => Auth::user()->id,
                'council_id' => $request->council_id,
                'collector_company_id' => $request->collector_company_id,
                'household_size' => $request->household_size,
                'waste_collection_frequency' => $request->waste_collection_frequency,
                'billing_address' => $request->billing_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ];

            \App\Models\Residency::updateOrCreate(
                ['user_id' => Auth::user()->id],
                $residencyData
            );

            $this->wasteManagementService->logAction('update_profile', 'Profile updated', Auth::user()->id, 'user', Auth::user()->id);
            DB::commit();
            return redirect()->back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ProfileController: Error updating profile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function requestCouncil(Request $request)
    {
        $request->validate([
            'council_id' => 'required|exists:councils,id',
        ]);

        try {
            DB::beginTransaction();
            $councilRequest = \App\Models\CouncilRequest::create([
                'user_id' => Auth::user()->id,
                'council_id' => $request->council_id,
                'status' => 'pending',
                'requested_at' => now(),
            ]);
            $this->wasteManagementService->logAction('request_council', 'Council membership requested', Auth::user()->id, 'council_request', $councilRequest->id);
            DB::commit();
            return redirect()->back()->with('success', 'Council membership request submitted.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ProfileController: Error requesting council membership', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getProfileData()
    {
        $user = Auth::user();
        $residency = $user->residency;
        $councils = \App\Models\Council::all();
        $companies = \App\Models\CollectorCompany::all();
        return view('resident.partials.profile', compact('user', 'residency', 'councils', 'companies'));
    }
}