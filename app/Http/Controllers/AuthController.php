<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WasteManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Routing\Controller; // Add this import

class AuthController extends Controller
{
    protected $client;
    protected $supabaseServiceKey;
    protected $wasteManagementService;

    public function __construct(WasteManagementService $wasteManagementService)
    {
        $supabaseUrl = env('SUPABASE_URL', 'https://spysutplknjrudxbojsw.supabase.co');
        $supabaseKey = env('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNweXN1dHBsa25qcnVkeGJvanN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTM4Njc1OTAsImV4cCI6MjA2OTQ0MzU5MH0.q10hMcFxb7o1ow0sX0oEfE3eGrAxwoQiqyQOgs725t8');
        $supabaseServiceKey = env('SUPABASE_SERVICE_ROLE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNweXN1dHBsa25qcnVkeGJvanN3Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1Mzg2NzU5MCwiZXhwIjoyMDY5NDQzNTkwfQ.N9dAeL6tSKurRUtGD8cOSbyrMF9sUgohz5hUQfamVPw');

        if (!$supabaseUrl || !$supabaseKey || !$supabaseServiceKey) {
            Log::channel('supabase')->error('Supabase configuration missing', [
                'SUPABASE_URL' => $supabaseUrl,
                'SUPABASE_KEY' => $supabaseKey,
                'SUPABASE_SERVICE_ROLE_KEY' => $supabaseServiceKey,
            ]);
            throw new \Exception('Supabase configuration missing');
        }

        $this->client = new Client([
            'base_uri' => rtrim($supabaseUrl, '/'),
            'headers' => [
                'Authorization' => 'Bearer ' . $supabaseKey,
                'apikey' => $supabaseKey,
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->supabaseServiceKey = $supabaseServiceKey;
        $this->wasteManagementService = $wasteManagementService;
    }

    public function showLoginForm()
    {
        Log::channel('supabase')->info('Showing login form');
        return view('auth.signin');
    }

    public function login(Request $request)
    {
        Log::channel('supabase')->info('Login attempt started', ['email' => $request->email]);

        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            Log::channel('supabase')->info('Credentials validated', ['email' => $credentials['email']]);

            DB::enableQueryLog();

            // Supabase Auth API call
            try {
                $response = $this->client->post('/auth/v1/token?grant_type=password', [
                    'json' => [
                        'email' => $credentials['email'],
                        'password' => $credentials['password'],
                    ],
                ]);

                $responseData = json_decode($response->getBody(), true);
                Log::channel('supabase')->info('Supabase Auth response', ['response' => $responseData]);
            } catch (RequestException $e) {
                $errorMessage = $e->hasResponse() ? (json_decode($e->getResponse()->getBody(), true)['error_description'] ?? $e->getMessage()) : $e->getMessage();
                Log::channel('supabase')->warning('Supabase Auth failed', [
                    'email' => $credentials['email'],
                    'error' => $errorMessage,
                    'queries' => DB::getQueryLog(),
                ]);
                DB::disableQueryLog();
                return back()->with('error', 'Login failed: ' . $errorMessage);
            }

            if (isset($responseData['error'])) {
                Log::channel('supabase')->warning('Supabase Auth failed', [
                    'email' => $credentials['email'],
                    'error' => $responseData['error_description'],
                    'queries' => DB::getQueryLog(),
                ]);
                DB::disableQueryLog();
                return back()->with('error', 'Login failed: ' . $responseData['error_description']);
            }

            $supabaseUser = $responseData['user'] ?? null;
            if (!$supabaseUser) {
                Log::channel('supabase')->warning('Supabase Auth failed: No user data returned', [
                    'email' => $credentials['email'],
                    'response' => $responseData,
                    'queries' => DB::getQueryLog(),
                ]);
                DB::disableQueryLog();
                return back()->with('error', 'Login failed: No user data returned');
            }

            // Sync or update user in Laravel users table
            $user = User::updateOrCreate(
                ['email' => $supabaseUser['email']],
                [
                    'id' => $supabaseUser['id'],
                    'name' => $supabaseUser['user_metadata']['name'] ?? 'Unknown',
                  //  'role' => $supabaseUser['user_metadata']['role'] ?? 'resident',
                    'password' => Hash::make($credentials['password']),
                    'user_status' => 'active',
                    'notifications_enabled' => true,
                    'phone_number' => $supabaseUser['phone'] ?? null,
                ]
            );

            Log::channel('supabase')->info('Supabase Auth user synced', [
                'email' => $user->email,
                'user_id' => $user->id,
                'queries' => DB::getQueryLog(),
            ]);

            // Attempt Laravel authentication
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                $user = Auth::user();
                $this->wasteManagementService->logAction(
                    'login',
                    "User {$user->email} logged in",
                    $user->id,
                    'user',
                    $user->id
                );
                Log::channel('supabase')->info('Login successful', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'queries' => DB::getQueryLog(),
                ]);
                DB::disableQueryLog();

                // Log redirect attempt
                Log::channel('supabase')->info('Redirecting user', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'redirect_route' => $this->getRedirectRoute($user->role),
                ]);

                return redirect()->route($this->getRedirectRoute($user->role));
            }

            Log::channel('supabase')->warning('Login failed: Laravel Auth attempt failed', [
                'email' => $credentials['email'],
                'queries' => DB::getQueryLog(),
            ]);
            DB::disableQueryLog();
            return back()->with('error', 'Login failed: Invalid credentials');
        } catch (\Exception $e) {
            Log::channel('supabase')->error('Login exception', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'queries' => DB::getQueryLog(),
            ]);
            DB::disableQueryLog();
            return back()->with('error', 'Login failed: ' . $e->getMessage());
        }
    }

    protected function getRedirectRoute($role)
    {
        $routes = [
            'management' => 'superadmin.dashboard',
            'council_admin' => 'council.dashboard',
            'company_admin' => 'management.dashboard',
            'collector' => 'collector.routes',
            'resident' => 'resident.dashboard',
        ];

        return $routes[$role] ?? 'home';
    }

    public function showRegistrationForm()
    {
        Log::channel('supabase')->info('Showing registration form');
        try {
            $councils = $this->wasteManagementService->getCouncils();
            $companies = $this->wasteManagementService->getCollectorCompanies();
        } catch (\Exception $e) {
            Log::channel('supabase')->error('Failed to fetch councils or companies', ['error' => $e->getMessage()]);
            $councils = [];
            $companies = [];
        }
        return view('auth.register', compact('councils', 'companies'));
    }

    public function register(Request $request)
    {
        Log::channel('supabase')->info('Registration attempt started', ['email' => $request->email]);

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:8',
                'role' => 'required|in:management,resident,collector,company_admin,council_admin',
                'address' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'council_id' => 'nullable|integer|exists:councils,id',
                'collector_company_id' => 'nullable|integer|exists:collector_companies,id',
                'household_size' => 'nullable|integer|min:1',
                'waste_collection_frequency' => 'nullable|in:weekly,biweekly,monthly',
                'billing_address' => 'nullable|string|max:255',
            ]);

            Log::channel('supabase')->info('Registration data validated', ['data' => $data]);

            DB::enableQueryLog();

            // Register with Supabase Auth
            try {
                $response = $this->client->post('/auth/v1/signup', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                    ],
                    'json' => [
                        'email' => $data['email'],
                        'password' => $data['password'],
                        'user_metadata' => [
                            'name' => $data['name'],
                            'role' => $data['role'],
                        ],
                    ],
                ]);

                $responseData = json_decode($response->getBody(), true);
                Log::channel('supabase')->info('Supabase Auth registration response', ['response' => $responseData]);
            } catch (RequestException $e) {
                $errorMessage = $e->hasResponse() ? (json_decode($e->getResponse()->getBody(), true)['error_description'] ?? $e->getMessage()) : $e->getMessage();
                Log::channel('supabase')->warning('Supabase Auth registration failed', [
                    'email' => $data['email'],
                    'error' => $errorMessage,
                    'queries' => DB::getQueryLog(),
                ]);
                DB::disableQueryLog();
                return back()->with('error', 'Registration failed: ' . $errorMessage);
            }

            if (isset($responseData['error'])) {
                Log::channel('supabase')->warning('Supabase Auth registration failed', [
                    'email' => $data['email'],
                    'error' => $responseData['error_description'],
                    'queries' => DB::getQueryLog(),
                ]);
                DB::disableQueryLog();
                return back()->with('error', 'Registration failed: ' . $responseData['error_description']);
            }

            $supabaseUser = $responseData['user'] ?? null;
            if (!$supabaseUser) {
                Log::channel('supabase')->warning('Supabase Auth registration failed: No user data returned', [
                    'email' => $data['email'],
                    'response' => $responseData,
                    'queries' => DB::getQueryLog(),
                ]);
                DB::disableQueryLog();
                return back()->with('error', 'Registration failed: No user data returned');
            }

            $user = User::create([
                'id' => $supabaseUser['id'],
                'email' => $data['email'],
                'name' => $data['name'],
                'role' => $data['role'],
                'address' => $data['address'],
                'phone_number' => $data['phone_number'],
                'council_id' => $data['council_id'],
                'collector_company_id' => $data['collector_company_id'],
                'password' => Hash::make($data['password']),
                'user_status' => 'active',
                'notifications_enabled' => true,
            ]);

            // Create residency for resident users if council_id and collector_company_id are provided
            if ($data['role'] === 'resident' && $data['council_id'] && $data['collector_company_id']) {
                $this->wasteManagementService->createResidency([
                    'user_id' => $user->id,
                    'council_id' => $data['council_id'],
                    'collector_company_id' => $data['collector_company_id'],
                    'household_size' => $data['household_size'],
                    'waste_collection_frequency' => $data['waste_collection_frequency'],
                    'billing_address' => $data['billing_address'],
                ]);
            }

            $this->wasteManagementService->logAction(
                'register',
                "User {$user->email} registered with role {$user->role}",
                $user->id,
                'user',
                $user->id
            );

            Log::channel('supabase')->info('User registered', [
                'user_id' => $user->id,
                'email' => $user->email,
                'queries' => DB::getQueryLog(),
            ]);

            DB::disableQueryLog();

            Auth::login($user);

            // Log redirect attempt
            Log::channel('supabase')->info('Redirecting user after registration', [
                'user_id' => $user->id,
                'role' => $user->role,
                'redirect_route' => $this->getRedirectRoute($user->role),
            ]);

            return redirect()->route($this->getRedirectRoute($user->role));
        } catch (ValidationException $e) {
            Log::channel('supabase')->warning('Registration validation failed', [
                'email' => $request->email,
                'errors' => $e->errors(),
                'queries' => DB::getQueryLog(),
            ]);
            DB::disableQueryLog();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::channel('supabase')->error('Registration exception', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'queries' => DB::getQueryLog(),
            ]);
            DB::disableQueryLog();
            return back()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        Log::channel('supabase')->info('Logout attempt', ['user_id' => $userId]);

        try {
            $this->client->post('/auth/v1/logout', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->supabaseServiceKey,
                ],
            ]);
            $this->wasteManagementService->logAction(
                'logout',
                "User logged out",
                $userId,
                'user',
                $userId
            );
        } catch (RequestException $e) {
            Log::channel('supabase')->warning('Supabase logout failed', [
                'error' => $e->getMessage(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}