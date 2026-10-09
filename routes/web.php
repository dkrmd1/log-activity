<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BackupLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ControlItemController;
use App\Http\Controllers\ServerCheckController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportApprovalController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ResetRequestController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ----- Profil (semua user login) -----
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature');
    Route::delete('/profile/signature', [ProfileController::class, 'destroySignature'])->name('profile.signature.destroy');

    // ----- Log Isu -----
    Route::get('/issues/export/excel', [IssueController::class, 'exportExcel'])->name('issues.export.excel');
    Route::get('/issues/export/pdf', [IssueController::class, 'exportPdf'])->name('issues.export.pdf');
    Route::resource('issues', IssueController::class);

    // ----- Log Permintaan -----
    Route::get('/requests/export/excel', [RequestController::class, 'exportExcel'])->name('requests.export.excel');
    Route::get('/requests/export/pdf', [RequestController::class, 'exportPdf'])->name('requests.export.pdf');
    Route::resource('requests', RequestController::class)
        ->parameters(['requests' => 'log_permintaan']);

    // ----- Permohonan Reset (CS) -----
    Route::get('/reset-requests/export/excel', [ResetRequestController::class, 'exportExcel'])->name('reset-requests.export.excel');
    Route::get('/reset-requests/export/pdf', [ResetRequestController::class, 'exportPdf'])->name('reset-requests.export.pdf');
    Route::resource('reset-requests', ResetRequestController::class);

    // ----- Log Backup Data -----
    Route::get('/backup-logs/export/excel', [BackupLogController::class, 'exportExcel'])->name('backup-logs.export.excel');
    Route::get('/backup-logs/export/pdf', [BackupLogController::class, 'exportPdf'])->name('backup-logs.export.pdf');
    Route::resource('backup-logs', BackupLogController::class)->except(['show']);

    // ----- Kontrol Server & Jaringan (form harian, PDF landscape) -----
    Route::get('/server-checks/{server_check}/pdf', [ServerCheckController::class, 'pdf'])->name('server-checks.pdf');
    Route::get('/server-checks/{server_check}/preview', [ServerCheckController::class, 'preview'])->name('server-checks.preview');
    Route::resource('server-checks', ServerCheckController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);

    // ----- Laporan Bulanan (semua user login) -----
    Route::get('/reports', [MonthlyReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/pdf', [MonthlyReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/reports/preview', [MonthlyReportController::class, 'preview'])->name('reports.preview');

    // Persetujuan laporan: Staf mengajukan, Group Head menyetujui / menolak (cek role di controller)
    Route::post('/reports/submit', [ReportApprovalController::class, 'submit'])->name('reports.submit');
    Route::post('/reports/approve', [ReportApprovalController::class, 'approve'])->name('reports.approve');
    Route::post('/reports/reject', [ReportApprovalController::class, 'reject'])->name('reports.reject');

    // ----- Khusus Admin: kelola user & pengaturan aplikasi -----
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class);

        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        // Master bagian & item form Kontrol Server & Jaringan
        Route::get('/control-items', [ControlItemController::class, 'index'])->name('control-items.index');
        Route::post('/control-items/sections', [ControlItemController::class, 'storeSection'])->name('control-items.sections.store');
        Route::put('/control-items/sections/{section}', [ControlItemController::class, 'updateSection'])->name('control-items.sections.update');
        Route::delete('/control-items/sections/{section}', [ControlItemController::class, 'destroySection'])->name('control-items.sections.destroy');
        Route::post('/control-items/sections/{section}/items', [ControlItemController::class, 'storeItem'])->name('control-items.items.store');
        Route::put('/control-items/items/{item}', [ControlItemController::class, 'updateItem'])->name('control-items.items.update');
        Route::delete('/control-items/items/{item}', [ControlItemController::class, 'destroyItem'])->name('control-items.items.destroy');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
