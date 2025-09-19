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
use App\Http\Controllers\AreaController;
use Illuminate\Support\Facades\Auth;


Auth::routes();


// Shared map route for all roles
Route::middleware(['auth'])->group(function () {
    Route::get('/map', [MapController::class, 'index'])->name('map');
});


// Override authentication routes
Route::get('/signin', [AuthController::class, 'showLoginForm'])->name('custom.signin');
Route::post('/login', [AuthController::class, 'login'])->name('custom.login');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('custom.register');
//Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('custom.logout');

// Home and public routes
Route::get('/home', [HomeController::class, 'index'])->name('custom.home');
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
   // Route::post('/collectors', [CouncilController::class, 'storeCollector'])->name('council.collectors.store');
    Route::put('/collectors/{id}', [CouncilController::class, 'updateCollector'])->name('council.collectors.update');
    Route::post('/ccollectors/{id}/deactivate', [CouncilController::class, 'deactivateCollector'])->name('council.collectors.deactivate');
    Route::post('/companies', [CouncilController::class, 'storeCompany'])->name('council.companies.store');
    Route::put('/companies/{id}', [CouncilController::class, 'updateCompany'])->name('council.companies.update');
    Route::get('/dashboard', [CouncilController::class, 'index'])->name('council.dashboard');
    Route::get('/requests', [CouncilController::class, 'getRequests'])->name('council.requests');
    Route::get('/pickups', [CouncilController::class, 'pickups'])->name('council.pickups');
    Route::get('/scheduled-pickups', [CouncilController::class, 'getScheduledPickups'])->name('council.scheduled-pickups');
    Route::get('/completed-pickups', [CouncilController::class, 'getCompletedPickups'])->name('council.completed-pickups');    
    Route::get('/pickup/edit/{id}', [CouncilController::class, 'editPickup'])->name('council.pickup.edit'); // You need to add this
    Route::post('/pickup/cancel/{id}', [CouncilController::class, 'cancelPickup'])->name('council.pickup.cancel'); 
    Route::get('/users', [CouncilController::class, 'getUsers'])->name('council.users');
    Route::get('/bills/{bill}', [CouncilController::class, 'billDetails'])->name('council.bill.details');
    Route::get('/issues', [CouncilController::class, 'issues'])->name('council.issues');
    Route::resource('areas', AreaController::class)->names('council.areas');
    Route::post('/collector-company/assign', [CouncilController::class, 'assignCollectorCompanyToCouncil'])->name('council.collector-company.assign');
    // Reports
    Route::get('/reports', [CouncilController::class, 'reports'])->name('council.reports');

   Route::post('/collector-company/assign-area', [CouncilController::class, 'assignCollectorCompanyToArea'])->name('council.collector-company.assign-area');
   // Route::get('/collectors', [CouncilController::class, 'collectorsIndex'])->name('council.collectors');
    
    // Show form to create collector (direct or company)
    Route::get('/collectors/create', [CouncilController::class, 'showCreateCollectorForm'])->name('council.collectors.create');
    
    // Store new collector
    Route::post('/collectors', [CouncilController::class, 'storeCompanyAdmin'])->name('council.collectors.store');
    
    // Show form to edit collector
    Route::get('/collectors/{id}/edit', [CouncilController::class, 'editCollector'])->name('council.collectors.edit');

    Route::post('/company-admins/{id}/reset-password', [CouncilController::class, 'resetCompanyAdminPassword'])->name('council.company-admins.reset-password');
    Route::get('/companies/{id}/edit', [CouncilController::class, 'editCompany'])->name('council.companies.edit');
Route::get('/payments', [CouncilController::class, 'bills'])->name('council.payments');
    Route::post('/request/approve/{requestId}', [CouncilController::class, 'approveRequest'])->name('council.request.approve');
    Route::post('/request/reject/{requestId}', [CouncilController::class, 'rejectRequest'])->name('council.request.reject');
    Route::post('/pickup/schedule/{pickupId}', [CouncilController::class, 'schedulePickup'])->name('council.schedule-pickup');
    Route::post('/issue/{issueId}/status', [CouncilController::class, 'updateIssueStatus'])->name('council.issue.status');
  Route::get('/users/create', [CouncilController::class, 'showCreateUserForm'])->name('council.user.create');
Route::post('/users/create', [CouncilController::class, 'createUser']);
    Route::get('/users/{id}/edit', [CouncilController::class, 'editUser'])->name('council.user.edit');
    Route::put('/council/users/{id}', [CouncilController::class, 'updateUser'])->name('council.user.update');
    Route::post('/council/users/{id}/password-reset', [CouncilController::class, 'resetUserPassword'])->name('council.user.password.reset');
    Route::post('/council/users/{id}/deactivate', [CouncilController::class, 'deactivateUser'])->name('council.user.deactivate');
    Route::post('/council/users/{id}/assign-company', [CouncilController::class, 'assignCompany'])->name('council.user.assign-company');
    Route::get('/council/companies/{id}/edit', [CouncilController::class, 'editCompany'])->name('council.company.edit');
    Route::get('/council/area/', [AreaController::class, 'index'])->name('index');
    Route::get('/council/area/create', [AreaController::class, 'create'])->name('create');
    Route::post('/', [AreaController::class, 'store'])->name('store');
    Route::get('/council/area/{area}/edit', [AreaController::class, 'edit'])->name('edit');
    Route::put('/{area}', [AreaController::class, 'update'])->name('update');
    Route::delete('/{area}', [AreaController::class, 'destroy'])->name('destroy');

});

Route::prefix('management')->middleware(['auth', 'role:company_admin'])->group(function () {
    Route::get('/dashboard', [CollectionCompanyAdminController::class, 'index'])->name('management.dashboard');
    Route::post('/collections/schedule', [CollectionCompanyAdminController::class, 'schedule'])->name('management.collections.schedule');
    Route::post('/collectors/assign', [CollectionCompanyAdminController::class, 'assignResidentsToCollector'])->name('management.collectors.assign');
    Route::get('/collections/create', [CollectionCompanyAdminController::class, 'createCollection'])->name('management.collections.create');
    Route::post('/collections', [CollectionCompanyAdminController::class, 'storeCollection'])->name('management.collections.store');
    Route::get('/collections/{id}/edit', [CollectionCompanyAdminController::class, 'editCollection'])->name('management.collections.edit');
    Route::put('/collections/{id}', [CollectionCompanyAdminController::class, 'updateCollection'])->name('management.collections.update');
    Route::put('/collection_schedules/{collection_schedule}', [CollectionCompanyAdminController::class, 'updateSchedule'])->name('management.collection_schedules.update');
    Route::get('/collection_schedules/{id}/edit', [CollectionScheduleController::class, 'edit'])->name('management.collection_schedules.edit');
    Route::post('/assign/{pickupId}', [CollectionCompanyAdminController::class, 'assignCollector'])->name('management.assign');
    Route::post('/message/{residentId}', [CollectionCompanyAdminController::class, 'sendResidentMessage'])->name('management.message');
    Route::get('/residents', [CollectionCompanyAdminController::class, 'residents'])->name('management.residents');
    Route::post('/residents/{residentId}/message', [CollectionCompanyAdminController::class, 'sendResidentMessage'])->name('management.residents.message');
    Route::get('/reports', [CollectionCompanyAdminController::class, 'reports'])->name('management.reports');
    Route::get('/issues', [CollectionCompanyAdminController::class, 'issues'])->name('management.issues');
    Route::post('/issues', [CollectionCompanyAdminController::class, 'storeIssue'])->name('management.issues.store');
    Route::put('/issues/{id}', [CollectionCompanyAdminController::class, 'updateIssue'])->name('management.issues.update');
    Route::delete('/issues/{id}', [CollectionCompanyAdminController::class, 'deleteIssue'])->name('management.issues.delete');
    Route::get('/collectors', [CollectionCompanyAdminController::class, 'collectors'])->name('management.collectors');
    Route::post('/collectors', [CollectionCompanyAdminController::class, 'storeCollector'])->name('management.collectors.store');
    Route::put('/collectors/{id}', [CollectionCompanyAdminController::class, 'updateCollector'])->name('management.collectors.update');
    Route::post('/collectors/{id}/deactivate', [CollectionCompanyAdminController::class, 'deactivateCollector'])->name('management.collectors.deactivate');
    Route::post('/collectors/{id}/activate', [CollectionCompanyAdminController::class, 'activateCollector'])->name('management.collectors.activate');
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
