<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\CollectionCompanyAdminController;
use App\Http\Controllers\CollectorController;
use App\Http\Controllers\CouncilController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Auth;

Auth::routes();
Route::get('/test', fn() => view('test'))->name('test');

// Shared map route for all roles
Route::middleware(['auth'])->group(function () {
    Route::get('/map', [MapController::class, 'index'])->name('map');
});


// Override authentication routes
Route::get('/signin', [AuthController::class, 'showLoginForm'])->name('signin');
//Route::post('/login', [AuthController::class, 'login'])->name('login');
//Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
//Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Home and public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/pickup', [HomeController::class, 'pickup'])->name('pickup');
Route::get('/learn-more', [HomeController::class, 'learnMore'])->name('learn-more');
Route::post('/newsletter', [HomeController::class, 'newsletterSubscribe'])->name('newsletter.subscribe');

// Superadmin routes
Route::prefix('superadmin')->middleware(['auth', 'role:management'])->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('superadmin.dashboard');
    Route::post('/council/create', [SuperAdminController::class, 'createCouncil'])->name('superadmin.council.create');
    Route::match(['get', 'post'], '/council/edit/{id}', [SuperAdminController::class, 'editCouncil'])->name('superadmin.council.edit');
    Route::post('/company/create', [SuperAdminController::class, 'createCompany'])->name('superadmin.company.create');
    Route::match(['get', 'post'], '/company/edit/{id}', [SuperAdminController::class, 'editCompany'])->name('superadmin.company.edit');
    Route::post('/user/create', [SuperAdminController::class, 'createUser'])->name('superadmin.user.create');
    Route::match(['get', 'post'], '/user/edit/{id}', [SuperAdminController::class, 'editUser'])->name('superadmin.user.edit');
    Route::post('/residency/create', [SuperAdminController::class, 'createResidency'])->name('superadmin.residency.create');
    Route::match(['get', 'post'], '/residency/edit/{id}', [SuperAdminController::class, 'editResidency'])->name('superadmin.residency.edit');
    Route::post('/assign-collector-company', [SuperAdminController::class, 'assignCollectorCompany'])->name('superadmin.assign-collector-company');
    Route::post('/reset-password/{email}', [SuperAdminController::class, 'resetPassword'])->name('superadmin.reset-password');

    // Reports
    Route::get('/reports', [SuperAdminController::class, 'reports'])->name('superadmin.reports');
});

// Council routes
Route::prefix('council')->middleware(['auth', 'role:council_admin'])->group(function () {
    Route::get('/collectors', [CouncilController::class, 'collectorsIndex'])->name('council.collectors');
    Route::post('/collectors', [CouncilController::class, 'storeCollector'])->name('council.collectors.store');
    Route::put('/collectors/{id}', [CouncilController::class, 'updateCollector'])->name('council.collectors.update');
    Route::post('/ccollectors/{id}/deactivate', [CouncilController::class, 'deactivateCollector'])->name('council.collectors.deactivate');
    Route::post('/companies', [CouncilController::class, 'storeCompany'])->name('council.companies.store');
    Route::put('/companies/{id}', [CouncilController::class, 'updateCompany'])->name('council.companies.update');
    Route::get('/dashboard', [CouncilController::class, 'index'])->name('council.dashboard');
    Route::get('/requests', [CouncilController::class, 'getRequests'])->name('council.requests');
    Route::get('/pickups', [CouncilController::class, 'pickups'])->name('council.pickups');
    Route::get('/scheduled-pickups', [CouncilController::class, 'getScheduledPickups'])->name('council.scheduled-pickups');
    Route::get('/completed-pickups', [CouncilController::class, 'getCompletedPickups'])->name('council.completed-pickups');
    Route::get('/users', [CouncilController::class, 'getUsers'])->name('council.users');
    Route::get('/bills/{bill}', [CouncilController::class, 'billDetails'])->name('council.bill.details');
    Route::get('/issues', [CouncilController::class, 'issues'])->name('council.issues');

    // Reports
    Route::get('/reports', [CouncilController::class, 'reports'])->name('council.reports');

    Route::get('/payments', [CouncilController::class, 'bills'])->name('council.payments');
    Route::post('/request/approve/{requestId}', [CouncilController::class, 'approveRequest'])->name('council.request.approve');
    Route::post('/request/reject/{requestId}', [CouncilController::class, 'rejectRequest'])->name('council.request.reject');
    Route::post('/pickup/schedule/{pickupId}', [CouncilController::class, 'schedulePickup'])->name('council.pickup.schedule');
    Route::post('/issue/{issueId}/status', [CouncilController::class, 'updateIssueStatus'])->name('council.issue.status');
    Route::post('/council/users/create', [CouncilController::class, 'createUser'])->name('council.user.create');
    Route::get('/council/users/{id}/edit', [CouncilController::class, 'editUser'])->name('council.user.edit');
    Route::post('/council/users/{id}/password-reset', [CouncilController::class, 'resetUserPassword'])->name('council.user.password.reset');
    Route::post('/council/users/{id}/deactivate', [CouncilController::class, 'deactivateUser'])->name('council.user.deactivate');
    Route::post('/council/users/{id}/assign-company', [CouncilController::class, 'assignCompany'])->name('council.user.assign-company');
    Route::get('/council/companies/{id}/edit', [CouncilController::class, 'editCompany'])->name('council.company.edit');
});

// Management routes
Route::prefix('management')->middleware(['auth', 'role:company_admin'])->group(function () {
    Route::get('/dashboard', [CollectionCompanyAdminController::class, 'index'])->name('management.dashboard');
    Route::post('/assign/{pickupId}', [CollectionCompanyAdminController::class, 'assignCollector'])->name('management.assign');
    Route::post('/message/{residentId}', [CollectionCompanyAdminController::class, 'sendResidentMessage'])->name('management.message');
    Route::get('/residents', [CollectionCompanyAdminController::class, 'residents'])->name('management.residents');

    // Reports
    Route::get('/reports', [CollectionCompanyAdminController::class, 'reports'])->name('management.reports');
   
   // Route::get('/collectors', [CollectionCompanyAdminController::class, 'collectors'])->name('management.collectors');
 


    Route::post('/residents/{residentId}/message', [CollectionCompanyAdminController::class, 'sendResidentMessage'])
        ->name('management.residents.message');



    Route::get('/issues', [CollectionCompanyAdminController::class, 'issues'])->name('management.issues');
 


     // Collectors
    Route::get('/collectors', [CollectionCompanyAdminController::class, 'collectors'])
        ->name('management.collectors');
    Route::post('/collectors', [CollectionCompanyAdminController::class, 'storeCollector'])
        ->name('management.collectors.store');
    Route::put('/collectors/{id}', [CollectionCompanyAdminController::class, 'updateCollector'])
        ->name('management.collectors.update');
    Route::post('/collectors/{id}/deactivate', [CollectionCompanyAdminController::class, 'deactivateCollector'])
        ->name('management.collectors.deactivate');
    Route::post('/collectors/{id}/activate', [CollectionCompanyAdminController::class, 'activateCollector'])
        ->name('management.collectors.activate');

    // Issues
    Route::get('/issues', [CollectionCompanyAdminController::class, 'issues'])
        ->name('management.issues');
    Route::post('/issues', [CollectionCompanyAdminController::class, 'storeIssue'])
        ->name('management.issues.store');
    Route::put('/issues/{id}', [CollectionCompanyAdminController::class, 'updateIssue'])
        ->name('management.issues.update');
    Route::delete('/issues/{id}', [CollectionCompanyAdminController::class, 'deleteIssue'])
        ->name('management.issues.delete');



});

// Collector routes
Route::prefix('collector')->middleware(['auth', 'role:collector'])->group(function () {
    Route::get('/routes', [CollectorController::class, 'index'])->name('collector.routes');
    Route::post('/confirm/{pickupId}', [CollectorController::class, 'confirmPickup'])->name('collector.confirm');
    Route::post('/report-issue/{pickupId}', [CollectorController::class, 'reportIssue'])->name('collector.report-issue');
});

// Resident routes
Route::prefix('resident')->middleware(['auth', 'role:resident'])->group(function () {
    Route::get('/', [ResidentController::class, 'index'])->name('resident.dashboard');
    Route::get('/services', [ResidentController::class, 'services'])->name('resident.services');
    Route::post('/request-pickup', [ResidentController::class, 'requestPickup'])->name('resident.request_pickup');
    Route::post('/report-issue', [ResidentController::class, 'reportIssue'])->name('resident.report_issue');
    Route::get('/payments', [ResidentController::class, 'payments'])->name('resident.payments');
    Route::get('/waste-guide', [ResidentController::class, 'wasteGuide'])->name('resident.waste_guide');
    Route::post('/logout', [ResidentController::class, 'logout'])->name('resident.logout');
    Route::get('/profile', [ProfileController::class, 'index'])->name('resident.profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('resident.profile.update');
    Route::post('/request-council', [ProfileController::class, 'requestCouncil'])->name('resident.request_council');
    Route::get('/profile-data', [ProfileController::class, 'getProfileData'])->name('resident.profile-data');
});

// Map route
Route::get('/map', [MapController::class, 'index'])->name('map')->middleware(['auth']);
