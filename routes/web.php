<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ConstructionMaterialBilling;
use App\Http\Controllers\Admin\DailyExpensesController;
use App\Http\Controllers\Admin\DailyWagerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentBillsController;
use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Admin\PaymentSupplierController;
use App\Http\Controllers\Admin\PhaseController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SitePaymentController;
use App\Http\Controllers\Admin\SquareFootageBillsController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupplierPaymentController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UpdateOnGoingController;
use App\Http\Controllers\Admin\WagerAttendanceController;
use App\Http\Controllers\Admin\WorkforceController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientLogoutController;
use App\Http\Controllers\Client\GenerateReportController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\MarkNotificationAsReadController;
use App\Http\Controllers\User\UserConstuctionMaterialBuildingsController;
use App\Http\Controllers\User\UserDailyExpensesController;
use App\Http\Controllers\User\UserDailyWagerController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\UserPhaseController;
use App\Http\Controllers\User\UserSquareFootageBillsController;
use App\Http\Controllers\User\UserWagerAttendanceController;
use App\Http\Controllers\User\ViewSiteController;
use Illuminate\Support\Facades\Route;








Route::get('/', function () {
    return view('welcome');
});

Route::get('/client-login', [ClientAuthController::class, 'login'])->name('client.login');
Route::post('/client-login', [ClientAuthController::class, 'store'])->name('client.store');

Route::middleware(['auth'])->group(function () {});

//  Client Routes
Route::middleware(['auth:clients', 'isClient'])->prefix('client')->group(function () {

    Route::resource('dashboard', ClientDashboardController::class);

    Route::post('/logout', [ClientLogoutController::class, 'logout'])->name('client.logout');

    Route::get('/generate-report/{id}', GenerateReportController::class)->name('generate-report');
});

//-------------------- Admin Routes --------------------------------

Route::middleware(['auth', 'verified', 'isAdmin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/register', [AdminUserController::class, 'register'])->name('admin.register-user');
    Route::get('/edit-user/{id}', [AdminUserController::class, 'editUser'])->name('admin.edit-user');
    Route::put('/user/update-password/{id}', [AdminUserController::class, 'updateUserPassword'])->name('admin.update-user-password');

    // Admin Creates Site Enginner Here
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');

    Route::resource('/clients', ClientController::class);
    Route::resource('/suppliers', SupplierController::class);
    Route::resource('/sites', SiteController::class);
    Route::post('sites/update-on-going/{id}', UpdateOnGoingController::class)->name('sites.update-on-going');
    Route::resource('/workforce', WorkforceController::class);
    Route::resource('/construction-material-billings', ConstructionMaterialBilling::class);
    Route::resource('/dailywager', DailyWagerController::class);
    Route::resource('/square-footage-bills', SquareFootageBillsController::class);
    Route::resource('/phase', PhaseController::class);
    Route::resource('/payment-bills', PaymentBillsController::class);
    Route::resource('/daily-wager-attendance', WagerAttendanceController::class);
    Route::resource('/daily-expenses', DailyExpensesController::class);
    Route::resource('sites/supplier-payments', PaymentSupplierController::class);
    Route::get('/site/ledger/{id}', SitePaymentController::class)->name('sites.view-ledger');
    Route::get('/supplier/ledger/{id}', SupplierPaymentController::class)->name('suppliers.view-ledger');
    Route::resource('/payments', PaymentsController::class);

    Route::get('/bin-supplier', [TrashController::class, 'trashedSuppliers'])->name('trash.suppliers');
    Route::get('/bin-site', [TrashController::class, 'trashedSites'])->name('trash.sites');
    Route::get('/bin/{model_name}/{id}', [TrashController::class, 'restore'])->name('trash.restore');
});


//  -------------------------- User Routes --------------------------
Route::middleware(['auth', 'isUser'])->prefix('user')->group(function () {

    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/markread', MarkNotificationAsReadController::class)->name('user.markAsRead');

    Route::get('/sites', [ViewSiteController::class, 'index'])->name('sites.view');
    Route::get('/sites/{id}', [ViewSiteController::class, 'show'])->name('user-sites.show');
    Route::resource('/user-phase', UserPhaseController::class);

    Route::post('/user-wager-attendance', UserWagerAttendanceController::class)->name('user-daily-wager-attendance.store');
    Route::post('/user-daily-expenses', UserDailyExpensesController::class)->name('user-daily-expenses.store');
    Route::post('construction-material-billings', UserConstuctionMaterialBuildingsController::class)
        ->name('user-construction-billings.store');
    Route::post('user-daily-wager', UserDailyWagerController::class)->name('user-daily-wager.store');
    Route::post('/user-square-footage-bills', UserSquareFootageBillsController::class)->name('user-square-footage-bills.store');

});



require __DIR__ . '/auth.php';
