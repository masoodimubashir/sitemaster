<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\ItemsVerificationController;
use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Admin\PaymentSiteController;
use App\Http\Controllers\Admin\PaymentSupplierController;
use App\Http\Controllers\Admin\PDFController;
use App\Http\Controllers\Admin\PendingPaymentsVerifications;
use App\Http\Controllers\Admin\SitePaymentController;
use App\Http\Controllers\Admin\SupplierPaymentController;
use App\Http\Controllers\Admin\UpdateOnGoingController;
use App\Http\Controllers\AttendanceSheetController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\User\MarkNotificationAsReadController;
use App\Http\Controllers\User\UserConstuctionMaterialBuildingsController;
use App\Http\Controllers\User\UserDailyExpensesController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\UserSitePayments;
use App\Http\Controllers\User\UserSquareFootageBillsController;
use App\Http\Controllers\User\ViewSiteController;
use App\Http\Controllers\UserSupplierController;
use App\Http\Controllers\WastaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendnaceSetupController;


// -------------------- User Routes ----------------------
Route::middleware(['auth', 'isUser'])->prefix('user')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::post('sites/update-on-going/{id}', UpdateOnGoingController::class)->name('sites.update-on-going');
    Route::get('/sites/create', [ViewSiteController::class, 'create']);
    Route::get('/sites/{id}', [ViewSiteController::class, 'show']);
    Route::get('/sites/details/{id}', [ViewSiteController::class, 'showDetails']);
    Route::post('/sites/store', [ViewSiteController::class, 'store']);
    Route::resource('site/payments', UserSitePayments::class);
    Route::resource('supplier/payments', PaymentSupplierController::class);
    Route::get('sites/payments/{id}', [PaymentSiteController::class, 'showPayment']);
    Route::resource('/payments', PaymentsController::class);
    Route::resource('/suppliers', UserSupplierController::class);
    Route::get('/supplier/detail/{id}', [UserSupplierController::class, 'showSupplierDetail']);
    Route::resource('/clients', ClientController::class);
    Route::resource('/items', ItemController::class);
    Route::post('/construction-material-billings', [UserConstuctionMaterialBuildingsController::class, 'store']);
    Route::get('/construction-material-billings/{id}', [UserConstuctionMaterialBuildingsController::class, 'edit']);
    Route::put('/construction-material-billings/{id}', [UserConstuctionMaterialBuildingsController::class, 'update']);
    Route::post('/square-footage-bills', [UserSquareFootageBillsController::class, 'store']);
    Route::get('/square-footage-bills/{id}', [UserSquareFootageBillsController::class, 'edit']);
    Route::put('/square-footage-bills/{id}', [UserSquareFootageBillsController::class, 'update']);
    Route::post('/daily-expenses', [UserDailyExpensesController::class, 'store']);
    Route::get('/daily-expenses/{id}', [UserDailyExpensesController::class, 'edit']);
    Route::put('/daily-expenses/{id}', [UserDailyExpensesController::class, 'update']);
    Route::get('/site/ledger/{id}', SitePaymentController::class)->name('sites.view-ledger');
    Route::get('/supplier/ledger/{id}', SupplierPaymentController::class)->name('suppliers.view-ledger');
    Route::get('/markAllAsRead', [MarkNotificationAsReadController::class, 'markAllNotificationAsRead'])->name('user.markAllAsRead');
    Route::get('/viewall-notifications', [MarkNotificationAsReadController::class, 'viewAllNotifications'])->name('user.viewAllNotifications');
    Route::get('/markAsRead/{id}', [MarkNotificationAsReadController::class, 'markAsRead'])->name('user.markAsRead');
    Route::get('/download-site/report/{id}', [PDFController::class, 'showSitePdf']);
    Route::get('/download-phase/report/{id}', [PDFController::class, 'showPhasePdf']);
    Route::get('/supplier-payment/report/{id}', [PDFController::class, 'showSupplierPaymentPdf']);
    Route::get('/site-payment/report/{id}', [PDFController::class, 'showSitePaymentPdf']);
    Route::get('/ledger/report', [PDFController::class, 'showLedgerPdf']);

    //    Route::get('wager-attendance', [AttendanceSheetController::class, 'index']);
    Route::put('/attendance/wasta', [AttendanceSheetController::class, 'storeWastaAttendance']);
    Route::put('/attendance/labour', [AttendanceSheetController::class, 'storelabourAttendance']);
    Route::post('/labour/store', [AttendanceSheetController::class, 'storeLabour']);
    Route::resource('/wasta', WastaController::class);
    Route::put('/attendance/wasta/update/{id}', [AttendanceSheetController::class, 'updateWasta']);
    Route::put('/attendance/labour/update/{id}', [AttendanceSheetController::class, 'updateLabour']);
    Route::get('/attendance/site/show/{id}', [AttendanceSheetController::class, 'showAttendanceBySite']);
    Route::resource('/attendance-setup', AttendnaceSetupController::class);


    Route::get('attendance/pdf', [PDFController::class, 'generateAttendancePdf'])->name('generateAttendancePdf');
    Route::get('/item-verification', [ItemsVerificationController::class, 'index']);
    Route::get('/pay-verification', [PendingPaymentsVerifications::class, 'index']);
    Route::post('/pay-verification/upload-screenshot', [PendingPaymentsVerifications::class, 'uploadScreenshot'])->name('payments.upload-screenshot');
    Route::post('/site/query', [QueryController::class, 'storeSiteQuery']);


});
