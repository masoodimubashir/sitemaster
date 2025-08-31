<?php

use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ConstructionMaterialBilling;
use App\Http\Controllers\Admin\DailyExpensesController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\ItemsVerificationController;
use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Admin\PaymentSiteController;
use App\Http\Controllers\Admin\PaymentSupplierController;
use App\Http\Controllers\Admin\PDFController;
use App\Http\Controllers\Admin\PendingPaymentsVerifications;
use App\Http\Controllers\Admin\PhaseController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SquareFootageBillsController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UpdateOnGoingController;
use App\Http\Controllers\AttendnaceSetupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\MarkNotificationAsReadController;
use App\Http\Controllers\WagerController;
use App\Http\Controllers\WastaController;
use App\Http\Controllers\AttendanceSheetController;
use Illuminate\Support\Facades\Route;

// -------------------- Admin Routes ---------------------
Route::middleware(['auth', 'verified', 'isAdmin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'siteDashboard'])->name('dashboard');
    Route::get('dashboard/suppliers', [DashboardController::class, 'supplierDashboard'])->name('suppliers.dashboard');

    // Notifications
    Route::get('/markAllAsRead', [MarkNotificationAsReadController::class, 'markAllNotificationAsRead'])->name('admin.markAllAsRead');
    Route::get('/viewall-notifications', [MarkNotificationAsReadController::class, 'viewAllNotifications'])->name('admin.viewAllNotifications');
    Route::get('/markAsRead/{id}', [MarkNotificationAsReadController::class, 'markAsRead'])->name('admin.markAsRead');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::get('/edit-user/{id}', [AdminUserController::class, 'editUser'])->name('admin.edit-user');
    Route::post('/register', [AdminUserController::class, 'register'])->name('admin.register-user');
    Route::put('/user/update-password/{id}', [AdminUserController::class, 'updateUserPassword'])->name('admin.update-user-password');
    Route::put('/user/update-name/{id}', [AdminUserController::class, 'updateName'])->name('user.update-name');
    Route::delete('/user/delete/{id}', [AdminUserController::class, 'deleteUser'])->name('admin.delete-user');

    // Clients
    Route::resource('/clients', ClientController::class);

    // Suppliers
    Route::resource('/suppliers', SupplierController::class);

    Route::get('/supplier/ledger-pdf', [SupplierController::class, 'generatePdf']);


    Route::get('/supplier/detail/{id}', [SupplierController::class, 'showSupplierDetail']);
    Route::resource('/supplier/payments', PaymentSupplierController::class);

    // Items
    Route::resource('/items', ItemController::class);

    // Sites
    Route::resource('/sites', SiteController::class);
    Route::get('/sites/details/{id}', [SiteController::class, 'showSiteDetails']);
    Route::post('/sites/update-on-going/{id}', UpdateOnGoingController::class)->name('sites.update-on-going');
    Route::get('/sites/payments/{id}', [PaymentSiteController::class, 'showPayment']);
    Route::post('/sites/payments', [PaymentSiteController::class, 'makePayment']);
    Route::put('/sites/payments/{id}', [PaymentSiteController::class, 'updatePayment']);

    // Construction Material Billings
    Route::resource('/construction-material-billings', ConstructionMaterialBilling::class);
    Route::resource('/square-footage-bills', SquareFootageBillsController::class);
    Route::resource('/daily-expenses', DailyExpensesController::class);
    Route::resource('/phase', PhaseController::class);
    Route::resource('/payments', PaymentsController::class);

    // Trash
    Route::get('/trashed-supplier', [TrashController::class, 'trashedSuppliers'])->name('trash.suppliers');
    Route::get('/trashed-site', [TrashController::class, 'trashedSites'])->name('trash.sites');
    Route::get('/trashed-phases', [TrashController::class, 'trashedPhase'])->name('trash.phases');
    Route::get('/trashed-{model_name}/{id}', [TrashController::class, 'restore'])->name('trash.restore');

    // PDF Downloads
    Route::get('/download-site/report/{id}', [PDFController::class, 'showSitePdf']);
    Route::get('/download-phase/report/{id}', [PDFController::class, 'showPhasePdf']);
    Route::get('/supplier-payment/report/{id}', [PDFController::class, 'showSupplierPaymentPdf']);
    Route::get('/site-payment/report/{id}', [PDFController::class, 'showSitePaymentPdf']);
    Route::get('/ledger/report', [PDFController::class, 'showLedgerPdf']);

    // Verification
    Route::post('verify/materials/{id}', [AdminVerificationController::class, 'verifyConstructionMaterials'])->name('verifyConstructionMaterials');
    Route::post('verify/square-footage/{id}', [AdminVerificationController::class, 'verifySquareFootage'])->name('verifySquareFootage');
    Route::post('verify/expenses/{id}', [AdminVerificationController::class, 'verifyExpenses'])->name('verifyExpenses');
    Route::post('verify/attendance/{id}', [AdminVerificationController::class, 'verifyAttendance'])->name('verifyAttendance');

    // Verifications
    Route::get('/pay-verification', [PendingPaymentsVerifications::class, 'index']);
    Route::put('/verify-payments', [PendingPaymentsVerifications::class, 'verifyPayment']);
    Route::get('/pay-verification/{id}/edit', [PendingPaymentsVerifications::class, 'edit']);
    Route::put('/pay-verification/{id}', [PendingPaymentsVerifications::class, 'update']);
    Route::delete('/pay-verification/{id}', [PendingPaymentsVerifications::class, 'destroy']);
    Route::get('/item-verification', [ItemsVerificationController::class, 'index']);
    Route::get('/verify-items', [ItemsVerificationController::class, 'verifyItems']);

    // Attendance Sheet
    // Route::get('/wager-attendance', [AttendanceSheetController::class, 'index']);
    Route::put('/attendance/wasta', [AttendanceSheetController::class, 'storeWastaAttendance']);
    Route::put('/attendance/labour', [AttendanceSheetController::class, 'storelabourAttendance']);
    Route::put('/attendance/wasta/update/{id}', [AttendanceSheetController::class, 'updateWasta']);
    Route::put('/attendance/labour/update/{id}', [AttendanceSheetController::class, 'updateLabour']);
    Route::resource('/attendance-setup', AttendnaceSetupController::class);


    Route::post('/labour/store', [AttendanceSheetController::class, 'storeLabour']);

    Route::resource('/wasta', WastaController::class);
    Route::resource('/wager', WagerController::class);


    Route::get('/attendance/site/show/{id}', [AttendanceSheetController::class, 'showAttendanceBySite']);
    Route::get('attendance/pdf', [PDFController::class, 'generateAttendancePdf'])->name('generateAttendancePdf');

    // Manage Payments
    Route::get('/manage-payment', [AdminPaymentController::class, 'index']);
    Route::post('/manage-payment/{id?}', [AdminPaymentController::class, 'storeOrUpdate'])->name('manage-payment.store-update');
    Route::get('/manage-payment/{id}/edit', [AdminPaymentController::class, 'edit'])->name('payments.edit');


});
