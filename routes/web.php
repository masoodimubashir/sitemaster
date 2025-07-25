<?php

use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ConstructionMaterialBilling;
use App\Http\Controllers\Admin\DailyExpensesController;
use App\Http\Controllers\Admin\DailyWagerController;
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
use App\Http\Controllers\Admin\SitePaymentController;
use App\Http\Controllers\Admin\SquareFootageBillsController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupplierPaymentController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UnverifiedSupplierPayments;
use App\Http\Controllers\Admin\UpdateOnGoingController;
use App\Http\Controllers\Admin\WagerAttendanceController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientLedgerController;
use App\Http\Controllers\Client\ClientLogoutController;
use App\Http\Controllers\Client\GenerateReportController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\User\MarkNotificationAsReadController;
use App\Http\Controllers\User\UserConstuctionMaterialBuildingsController;
use App\Http\Controllers\User\UserDailyExpensesController;
use App\Http\Controllers\User\UserDailyWagerController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\UserSitePayments;
use App\Http\Controllers\User\UserSquareFootageBillsController;
use App\Http\Controllers\User\UserWagerAttendanceController;
use App\Http\Controllers\User\ViewSiteController;
use App\Http\Controllers\UserSupplierController;
use App\Http\Controllers\WastaController;
use App\Http\Controllers\AttendanceSheetController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth'])->group(function () { });

Route::get('/client-login', [ClientAuthController::class, 'login'])->name('client.login');
Route::post('/client-login', [ClientAuthController::class, 'store'])->name('client.store');

//  Client Routes
Route::middleware(['auth:clients', 'isClient'])->prefix('client')->group(function () {

    Route::resource('/dashboard', ClientDashboardController::class);

    Route::post('/logout', [ClientLogoutController::class, 'logout'])->name('client.logout');
    Route::get('/ledger', [ClientLedgerController::class, 'index']);

    Route::get('/generate-report/{id}', GenerateReportController::class)->name('generate-report');
    Route::get('/download-site/report/{id}', [PDFController::class, 'showSitePdf']);
    Route::get('/download-phase/report/{id}', [PDFController::class, 'showPhasePdf']);
    Route::get('/site-payment/report/{id}', [PDFController::class, 'showSitePaymentPdf']);
    Route::get('/ledger/report', [PDFController::class, 'showLedgerPdf']);

    Route::get('/attendance/site/show/{id}', [AttendanceSheetController::class, 'showAttendanceBySite']);

    Route::get('attendance/pdf', [PDFController::class, 'generateAttendancePdf'])->name('generateAttendancePdf');


});


//-------------------- Admin Routes --------------------------------
Route::middleware(['auth', 'verified', 'isAdmin'])->prefix('admin')->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'siteDashboard'])->name('dashboard');
    Route::get('dashboard/suppliers', [DashboardController::class, 'supplierDashboard'])->name('suppliers.dashboard');


    Route::get('/markAllAsRead', [MarkNotificationAsReadController::class, 'markAllNotificationAsRead'])->name('admin.markAllAsRead');
    Route::get('/viewall-notifications', [MarkNotificationAsReadController::class, 'viewAllNotifications'])->name('admin.viewAllNotifications');
    Route::get('/markAsRead/{id}', [MarkNotificationAsReadController::class, 'markAsRead'])->name('admin.markAsRead');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Engineer Controllers
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::get('/edit-user/{id}', [AdminUserController::class, 'editUser'])->name('admin.edit-user');
    Route::post('/register', [AdminUserController::class, 'register'])->name('admin.register-user');
    Route::put('/user/update-password/{id}', [AdminUserController::class, 'updateUserPassword'])->name('admin.update-user-password');
    Route::put('/user/update-name/{id}', [AdminUserController::class, 'updateName'])->name('user.update-name');
    Route::delete('/user/delete/{id}', [AdminUserController::class, 'deleteUser'])->name('admin.delete-user');

    // Client Controller
    Route::resource('/clients', ClientController::class);

    // Suppliers Routes
    Route::resource('/suppliers', SupplierController::class);
    Route::get('/supplier/detail/{id}', [SupplierController::class, 'showSupplierDetail']);
    Route::resource('/supplier/payments', PaymentSupplierController::class);


    // Items Controller
    Route::resource('/items', ItemController::class);

    // Sites Controller
    Route::resource('/sites', SiteController::class);
    Route::get('/sites/details/{id}', [SiteController::class, 'showSiteDetails']);
    Route::post('/sites/update-on-going/{id}', UpdateOnGoingController::class)->name('sites.update-on-going');
    Route::get('/sites/payments/{id}', [PaymentSiteController::class, 'showPayment']);
    Route::post('/sites/payments', [PaymentSiteController::class, 'makePayment']);
    Route::put('/sites/payments/{id}', [PaymentSiteController::class, 'updatePayment']);


    // Route::resource('/workforce', WorkforceController::class);

    Route::resource('/construction-material-billings', ConstructionMaterialBilling::class);

    Route::resource('/square-footage-bills', SquareFootageBillsController::class);

    Route::resource('/daily-expenses', DailyExpensesController::class);

    // Route::resource('/dailywager', DailyWagerController::class);

    // Route::resource('/daily-wager-attendance', WagerAttendanceController::class);

    Route::resource('/phase', PhaseController::class);


    // Recheck
    // Route::get('/site/ledger/{id}', SitePaymentController::class)->name('sites.view-ledger');

    // View Supplier Ledger
    // Route::get('/supplier/ledger/{id}', SupplierPaymentController::class)->name('suppliers.view-ledger');

    // Payments Controller
    Route::resource('/payments', PaymentsController::class);

    //  All Controllers For Soft Deletes
    Route::get('/trashed-supplier', [TrashController::class, 'trashedSuppliers'])->name('trash.suppliers');
    Route::get('/trashed-site', [TrashController::class, 'trashedSites'])->name('trash.sites');
    Route::get('/trashed-phases', [TrashController::class, 'trashedPhase'])->name('trash.phases');
    Route::get('/trashed-{model_name}/{id}', [TrashController::class, 'restore'])->name('trash.restore');

    // DownLoad PDF Controller
    Route::get('/download-site/report/{id}', [PDFController::class, 'showSitePdf']);
    Route::get('/download-phase/report/{id}', [PDFController::class, 'showPhasePdf']);
    Route::get('/supplier-payment/report/{id}', [PDFController::class, 'showSupplierPaymentPdf']);
    Route::get('/site-payment/report/{id}', [PDFController::class, 'showSitePaymentPdf']);
    Route::get('/ledger/report', [PDFController::class, 'showLedgerPdf']);

    // Admin Verification Controller
    Route::post('verify/materials/{id}', [AdminVerificationController::class, 'verifyConstructionMaterials'])->name('verifyConstructionMaterials');
    Route::post('verify/square-footage/{id}', [AdminVerificationController::class, 'verifySquareFootage'])->name('verifySquareFootage');
    Route::post('verify/expenses/{id}', [AdminVerificationController::class, 'verifyExpenses'])->name('verifyExpenses');
    // Route::post('verify/wagers/{id}', [AdminVerificationController::class, 'verifyDailyWagers'])->name('verifyDailyWagers');
    // Route::post('verify/attendance/{id}', [AdminVerificationController::class, 'verifyAttendance'])->name('verifyAttendance');

    // Verify Controllers For Pending Payments
    Route::get('/pay-verification', [PendingPaymentsVerifications::class, 'index']);
    Route::put('/verify-payments', [PendingPaymentsVerifications::class, 'verifyPayment']);
    Route::get('/pay-verification/{id}/edit', [PendingPaymentsVerifications::class, 'edit']);
    Route::put('/pay-verification/{id}', [PendingPaymentsVerifications::class, 'update']);
    Route::delete('/pay-verification/{id}', [PendingPaymentsVerifications::class, 'destroy']);

    // Verification Controller For Items
    Route::get('/item-verification', [ItemsVerificationController::class, 'index']);
    Route::get('/verify-items', [ItemsVerificationController::class, 'verifyItems']);

    // Routes For Attendance Sheet
    Route::get('/wager-attendance', [AttendanceSheetController::class, 'index']);
    Route::put('/attendance/wasta', [AttendanceSheetController::class, 'storeWastaAttendance']);
    Route::put('/attendance/labour', [AttendanceSheetController::class, 'storelabourAttendance']);
    Route::post('/labour/store', [AttendanceSheetController::class, 'storeLabour']);
    Route::resource('/wasta', WastaController::class);
    Route::put('/attendance/wasta/update/{id}', [AttendanceSheetController::class, 'updateWasta']);
    Route::put('/attendance/labour/update/{id}', [AttendanceSheetController::class, 'updateLabour']);
    Route::get('/attendance/site/show/{id}', [AttendanceSheetController::class, 'showAttendanceBySite']);
    Route::get('attendance/pdf', [PDFController::class, 'generateAttendancePdf'])->name('generateAttendancePdf');

    // Route For Managing Payments By Admin
    Route::get('/manage-payment', [AdminPaymentController::class, 'index']);
    Route::post('/manage-payment/{id?}', [AdminPaymentController::class, 'storeOrUpdate'])->name('manage-payment.store-update');
    Route::get('/manage-payment/{id}/edit', [AdminPaymentController::class, 'edit'])->name('payments.edit');
});


//  -------------------------- User Routes --------------------------
Route::middleware(['auth', 'isUser'])->prefix('user')->group(function () {

    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

    //  Route For Ypdating The Site Ongoing Status
    Route::post('sites/update-on-going/{id}', UpdateOnGoingController::class)->name('sites.update-on-going');

    // Site Controllers
    Route::get('/sites/create', [ViewSiteController::class, 'create']);
    Route::get('/sites/{id}', [ViewSiteController::class, 'show']);
    Route::get('/sites/details/{id}', [ViewSiteController::class, 'showDetails']);
    Route::post('/sites/store', [ViewSiteController::class, 'store']);
    Route::resource('site/payments', UserSitePayments::class);

    // Site Payments
    Route::resource('supplier/payments', PaymentSupplierController::class);
    Route::get('sites/payments/{id}', [PaymentSiteController::class, 'showPayment']);
    Route::resource('/payments', PaymentsController::class);


    // User Supplier Controller
    Route::resource('/suppliers', UserSupplierController::class);
    Route::get('/supplier/detail/{id}', [UserSupplierController::class, 'showSupplierDetail']);

    // Client Controller
    Route::resource('/clients', ClientController::class);

    // Items Controller
    Route::resource('/items', ItemController::class);

    // Construction Material Routes
    Route::post('/construction-material-billings', [UserConstuctionMaterialBuildingsController::class, 'store']);
    Route::get('/construction-material-billings/{id}', [UserConstuctionMaterialBuildingsController::class, 'edit']);
    Route::put('/construction-material-billings/{id}', [UserConstuctionMaterialBuildingsController::class, 'update']);

    // Square Footage Routes
    Route::post('/square-footage-bills', [UserSquareFootageBillsController::class, 'store']);
    Route::get('/square-footage-bills/{id}', [UserSquareFootageBillsController::class, 'edit']);
    Route::put('/square-footage-bills/{id}', [UserSquareFootageBillsController::class, 'update']);

    // Expenses Routes
    Route::post('/daily-expenses', [UserDailyExpensesController::class, 'store']);
    Route::get('/daily-expenses/{id}', [UserDailyExpensesController::class, 'edit']);
    Route::put('/daily-expenses/{id}', [UserDailyExpensesController::class, 'update']);

    // // Daily Wager Routes
    // Route::post('/dailywager', [UserDailyWagerController::class, 'store']);
    // Route::get('/dailywager/{id}/edit', [UserDailyWagerController::class, 'edit']);
    // Route::put('/dailywager/{id}', [UserDailyWagerController::class, 'update']);

    // // Attendance Routes
    // Route::post('/daily-wager-attendance', [UserWagerAttendanceController::class, 'store']);
    // Route::get('/daily-wager-attendance/{id}/edit', [UserWagerAttendanceController::class, 'edit']);
    // Route::put('/daily-wager-attendance/{id}', [UserWagerAttendanceController::class, 'update']);

    // View Ledger
    // Route::get('/site/ledger/{id}', UserLedgerController::class);
    Route::get('/site/ledger/{id}', SitePaymentController::class)->name('sites.view-ledger');

    // View Supplier Ledger
    Route::get('/supplier/ledger/{id}', SupplierPaymentController::class)->name('suppliers.view-ledger');

    // Notification Routes
    Route::get('/markAllAsRead', [MarkNotificationAsReadController::class, 'markAllNotificationAsRead'])
        ->name('user.markAllAsRead');
    Route::get('/viewall-notifications', [MarkNotificationAsReadController::class, 'viewAllNotifications'])
        ->name('user.viewAllNotifications');
    Route::get('/markAsRead/{id}', [MarkNotificationAsReadController::class, 'markAsRead'])
        ->name('user.markAsRead');

    // Generate PDF Routes
    Route::get('/download-site/report/{id}', [PDFController::class, 'showSitePdf']);
    Route::get('/download-phase/report/{id}', [PDFController::class, 'showPhasePdf']);
    Route::get('/supplier-payment/report/{id}', [PDFController::class, 'showSupplierPaymentPdf']);
    Route::get('/site-payment/report/{id}', [PDFController::class, 'showSitePaymentPdf']);
    Route::get('/ledger/report', [PDFController::class, 'showLedgerPdf']);


    Route::get('wager-attendance', [AttendanceSheetController::class, 'index']);
    Route::put('/attendance/wasta', [AttendanceSheetController::class, 'storeWastaAttendance']);
    Route::put('/attendance/labour', [AttendanceSheetController::class, 'storelabourAttendance']);
    Route::post('/labour/store', [AttendanceSheetController::class, 'storeLabour']);
    Route::resource('/wasta', WastaController::class);
    Route::put('/attendance/wasta/update/{id}', [AttendanceSheetController::class, 'updateWasta']);
    Route::put('/attendance/labour/update/{id}', [AttendanceSheetController::class, 'updateLabour']);
    Route::get('/attendance/site/show/{id}', [AttendanceSheetController::class, 'showAttendanceBySite']);
    Route::get('attendance/pdf', [PDFController::class, 'generateAttendancePdf'])->name('generateAttendancePdf');



    Route::get('/item-verification', [ItemsVerificationController::class, 'index']);


    Route::get('/pay-verification', [PendingPaymentsVerifications::class, 'index']);
    Route::post('/pay-verification/upload-screenshot', [PendingPaymentsVerifications::class, 'uploadScreenshot'])
        ->name('payments.upload-screenshot');


    Route::post('/site/query', [QueryController::class, 'storeSiteQuery']);


});


require __DIR__ . '/auth.php';
