<?php

namespace App\Http\Controllers;

use App\Services\WasteManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller; // Add this import
use App\Services\ReportService;

class SuperAdminController extends Controller
{
    protected $wasteManagementService;

    public function __construct(WasteManagementService $wasteManagementService)
    {
        $this->middleware('auth');
        $this->middleware('role:management');
        $this->wasteManagementService = $wasteManagementService;
    }

    public function index()
    {
        Log::info('SuperAdminController: Accessing dashboard', [
            'user' => Auth::user() ? Auth::user()->toArray() : null,
        ]);
        try {
            $councils = $this->wasteManagementService->getCouncils();
            $companies = $this->wasteManagementService->getCollectorCompanies();
            $users = $this->wasteManagementService->getAllUsers();
            $residents = $this->wasteManagementService->getResidents();
            $councilAdmins = $this->wasteManagementService->getCouncilAdmins();
            $companyAdmins = $this->wasteManagementService->getCompanyAdmins();
            $residencies = $this->wasteManagementService->getResidencies();
            Log::info('SuperAdminController: Data fetched', [
                'councils_count' => $councils->count(),
                'companies_count' => $companies->count(),
                'users_count' => $users->count(),
                'residents_count' => $residents->count(),
                'council_admins_count' => $councilAdmins->count(),
                'company_admins_count' => $companyAdmins->count(),
                'residencies_count' => $residencies->count(),
            ]);
            return view('superadmin.dashboard', compact('councils', 'companies', 'users', 'residents', 'councilAdmins', 'companyAdmins', 'residencies'));
        } catch (\Exception $e) {
            Log::error('SuperAdminController: Error fetching dashboard data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load dashboard data.');
        }
    }

    public function createCouncil(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'contact_email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        try {
            $council = $this->wasteManagementService->createCouncil($request->only([
                'name', 'region', 'contact_email', 'phone_number', 'address', 'status',
            ]));
            $this->wasteManagementService->logAction('create_council', "Council created: {$request->name}, {$request->region}", auth()->id(), 'council', $council->id);
            return redirect()->route('superadmin.dashboard')->with('success', 'Council created successfully.');
        } catch (\Exception $e) {
            Log::error('SuperAdminController: Error creating council', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function editCouncil(Request $request, $id)
    {
        $council = $this->wasteManagementService->getCouncil($id);
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'region' => 'required|string|max:255',
                'contact_email' => 'nullable|email',
                'phone_number' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'status' => 'in:active,inactive',
            ]);
            try {
                $this->wasteManagementService->updateCouncil($id, $request->only([
                    'name', 'region', 'contact_email', 'phone_number', 'address', 'status',
                ]));
                $this->wasteManagementService->logAction('update_council', "Council updated: {$request->name}, {$request->region}", auth()->id(), 'council', $id);
                return redirect()->route('superadmin.dashboard')->with('success', 'Council updated successfully.');
            } catch (\Exception $e) {
                Log::error('SuperAdminController: Error updating council', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        return view('superadmin.council.edit', compact('council'));
    }

    public function createCompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'license_number' => 'nullable|string|unique:collector_companies,license_number',
            'status' => 'in:active,inactive',
        ]);

        try {
            $company = $this->wasteManagementService->createCollectionCompany($request->only([
                'name', 'contact_email', 'phone_number', 'address', 'license_number', 'status',
            ]));
            $this->wasteManagementService->logAction('create_company', "Company created: {$request->name}", auth()->id(), 'collector_company', $company->id);
            return redirect()->route('superadmin.dashboard')->with('success', 'Collection company created successfully.');
        } catch (\Exception $e) {
            Log::error('SuperAdminController: Error creating company', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function editCompany(Request $request, $id)
    {
        $company = $this->wasteManagementService->getCollectorCompany($id);
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'contact_email' => 'nullable|email',
                'phone_number' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'license_number' => 'nullable|string|unique:collector_companies,license_number,' . $id,
                'status' => 'in:active,inactive',
            ]);
            try {
                $this->wasteManagementService->updateCollectionCompany($id, $request->only([
                    'name', 'contact_email', 'phone_number', 'address', 'license_number', 'status',
                ]));
                $this->wasteManagementService->logAction('update_company', "Company updated: {$request->name}", auth()->id(), 'collector_company', $id);
                return redirect()->route('superadmin.dashboard')->with('success', 'Company updated successfully.');
            } catch (\Exception $e) {
                Log::error('SuperAdminController: Error updating company', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        return view('superadmin.company.edit', compact('company'));
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|confirmed|min:8',
            'role' => 'required|in:superadmin,council_admin,company_admin,collector,resident',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'council_id' => 'nullable|integer|exists:councils,id',
            'collector_company_id' => 'nullable|integer|exists:collector_companies,id',
            'notifications_enabled' => 'boolean',
        ]);

        try {
            $user = $this->wasteManagementService->createUser($request->only([
                'email', 'name', 'password', 'role', 'address', 'phone_number',
                'council_id', 'collector_company_id', 'notifications_enabled',
            ]));
            $this->wasteManagementService->logAction('create_user', "User created: {$request->email}, Role: {$request->role}", auth()->id(), 'user', $user->id);
            return redirect()->route('superadmin.dashboard')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            Log::error('SuperAdminController: Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function editUser(Request $request, $id)
    {
        $user = $this->wasteManagementService->getUser($id);
        $councils = $this->wasteManagementService->getCouncils();
        $companies = $this->wasteManagementService->getCollectorCompanies();
        if ($request->isMethod('post')) {
            $request->validate([
                'email' => 'required|email|unique:users,email,' . $id,
                'name' => 'required|string|max:255',
                'password' => 'nullable|confirmed|min:8',
                'role' => 'required|in:superadmin,council_admin,company_admin,collector,resident',
                'address' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'council_id' => 'nullable|integer|exists:councils,id',
                'collector_company_id' => 'nullable|integer|exists:collector_companies,id',
                'notifications_enabled' => 'boolean',
            ]);
            try {
                $this->wasteManagementService->updateUser($id, $request->only([
                    'email', 'name', 'password', 'role', 'address', 'phone_number',
                    'council_id', 'collector_company_id', 'notifications_enabled',
                ]));
                $this->wasteManagementService->logAction('update_user', "User updated: {$request->email}, Role: {$request->role}", auth()->id(), 'user', $id);
                return redirect()->route('superadmin.dashboard')->with('success', 'User updated successfully.');
            } catch (\Exception $e) {
                Log::error('SuperAdminController: Error updating user', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        return view('superadmin.user.edit', compact('user', 'councils', 'companies'));
    }

    public function createResidency(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'council_id' => 'required|integer|exists:councils,id',
            'collector_company_id' => 'required|integer|exists:collector_companies,id',
            'household_size' => 'nullable|integer|min:1',
            'waste_collection_frequency' => 'nullable|in:weekly,biweekly,monthly',
            'billing_address' => 'nullable|string',
        ]);

        try {
            $residency = $this->wasteManagementService->createResidency($request->only([
                'user_id', 'council_id', 'collector_company_id', 'household_size',
                'waste_collection_frequency', 'billing_address',
            ]));
            $this->wasteManagementService->logAction('create_residency', "Residency created for user: {$request->user_id}", auth()->id(), 'residency', $residency->id);
            return redirect()->route('superadmin.dashboard')->with('success', 'Residency created successfully.');
        } catch (\Exception $e) {
            Log::error('SuperAdminController: Error creating residency', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function editResidency(Request $request, $id)
    {
        $residency = $this->wasteManagementService->getResidency($id);
        $users = $this->wasteManagementService->getResidents();
        $councils = $this->wasteManagementService->getCouncils();
        $companies = $this->wasteManagementService->getCollectorCompanies();
        if ($request->isMethod('post')) {
            $request->validate([
                'user_id' => 'required|uuid|exists:users,id',
                'council_id' => 'required|integer|exists:councils,id',
                'collector_company_id' => 'required|integer|exists:collector_companies,id',
                'household_size' => 'nullable|integer|min:1',
                'waste_collection_frequency' => 'nullable|in:weekly,biweekly,monthly',
                'billing_address' => 'nullable|string',
            ]);
            try {
                $this->wasteManagementService->updateResidency($id, $request->only([
                    'user_id', 'council_id', 'collector_company_id', 'household_size',
                    'waste_collection_frequency', 'billing_address',
                ]));
                $this->wasteManagementService->logAction('update_residency', "Residency updated for user: {$request->user_id}", auth()->id(), 'residency', $id);
                return redirect()->route('superadmin.dashboard')->with('success', 'Residency updated successfully.');
            } catch (\Exception $e) {
                Log::error('SuperAdminController: Error updating residency', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        return view('superadmin.residency.edit', compact('residency', 'users', 'councils', 'companies'));
    }

    public function assignCollectorCompany(Request $request)
    {
        $request->validate([
            'council_id' => 'required|integer|exists:councils,id',
            'collector_company_id' => 'required|integer|exists:collector_companies,id',
        ]);

        try {
            $this->wasteManagementService->assignCollectorCompany($request->council_id, $request->collector_company_id);
            $this->wasteManagementService->logAction('assign_collector_company', "Collector company assigned to council: {$request->council_id}", auth()->id(), 'council_collector_companies', $request->council_id);
            return redirect()->route('superadmin.dashboard')->with('success', 'Collector company assigned to council successfully.');
        } catch (\Exception $e) {
            Log::error('SuperAdminController: Error assigning collector company', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function resetPassword(Request $request, $email)
    {
        try {
            $this->wasteManagementService->resetUserPassword($email);
            $this->wasteManagementService->logAction('reset_password', "Password reset requested for: {$email}", auth()->id(), 'user', null);
            return redirect()->route('superadmin.dashboard')->with('success', 'Password reset email sent.');
        } catch (\Exception $e) {
            Log::error('SuperAdminController: Error resetting password', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

public function reports()
{
    $user = auth()->user();

    // Base stats
    $stats = [
        'users'    => 0,
        'issues'   => 0,
        'payments' => 0,
        'pickups'  => 0,
    ];

    if ($user->role === 'management') {
        // Superadmin -> See all
        $stats['users']    = \App\Models\User::count();
        $stats['issues']   = \App\Models\Issue::count();
        $stats['payments'] = \App\Models\Payment::sum('amount');
        $stats['pickups']  = \App\Models\Collection::count();
    } elseif ($user->role === 'council_admin') {
        $councilId = $user->council_id;

        $stats['users'] = \App\Models\User::where('council_id', $councilId)->count();

        $stats['issues'] = \App\Models\Issue::where('council_id', $councilId)->count();

        // Payments through users in this council
        $stats['payments'] = \App\Models\Payment::whereHas('user', function ($q) use ($councilId) {
            $q->where('council_id', $councilId);
        })->sum('amount');

        // Pickups through users in this council
        $stats['pickups'] = \App\Models\Collection::whereHas('user', function ($q) use ($councilId) {
            $q->where('council_id', $councilId);
        })->count();
    } elseif ($user->role === 'collector_admin') {
        $companyId = $user->collector_company_id;

        $stats['users'] = \App\Models\User::where('collector_company_id', $companyId)->count();

        $stats['issues'] = \App\Models\Issue::where('collector_company_id', $companyId)->count();

        // Payments through users served by this company
        $stats['payments'] = \App\Models\Payment::whereHas('user', function ($q) use ($companyId) {
            $q->where('collector_company_id', $companyId);
        })->sum('amount');

        $stats['pickups'] = \App\Models\Collection::where('collector_company_id', $companyId)->count();
    }

    return view('reports', compact('stats', 'user'));
}



}