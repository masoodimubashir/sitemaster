
<?php

use App\Http\Controllers\Admin\PDFController;
use App\Http\Controllers\AttendanceSheetController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientLedgerController;
use App\Http\Controllers\Client\ClientLogoutController;
use App\Http\Controllers\Client\GenerateReportController;
use App\Http\Controllers\ClientAuthController;
use Illuminate\Support\Facades\Route;

// -------------------- Client Auth ----------------------
Route::get('/client-login', [ClientAuthController::class, 'login'])->name('client.login');
Route::post('/client-login', [ClientAuthController::class, 'store'])->name('client.store');

// -------------------- Client Routes --------------------
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
