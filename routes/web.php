<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\SampleDispatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Iavic114ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PbmcController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ─── Public ───────────────────────────────────────────────────────────────────
Route::get('/', fn () => view('auth.login'));

// ─── Authenticated ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])
        ->middleware('throttle:10,1')
        ->name('settings.password');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-read');

    // PBMC records
    Route::resource('pbmc', PbmcController::class);
    Route::get('/pbmcs/export', [PbmcController::class, 'exportAll'])->name('pbmcs.export');
    Route::post('/pbmcs/export-selected', [PbmcController::class, 'exportSelected'])->name('pbmcs.export.selected');

    // ACRN sync — throttle to 5 per minute, admin only
    Route::post('/pbmcs/sync-from-acrn', [PbmcController::class, 'syncFromAcrn'])
        ->middleware(['admin', 'throttle:5,1'])
        ->name('pbmcs.sync');

    // Analytics
    Route::get('/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/filter/{filter}', [App\Http\Controllers\AnalyticsController::class, 'getFilteredData'])->name('analytics.filter');

    // Sample dispatches
    Route::get('/sample-dispatches', [SampleDispatchController::class, 'index'])->name('sample-dispatches.index');
    Route::get('/sample-dispatches/create', [SampleDispatchController::class, 'create'])->name('sample-dispatches.create');
    Route::post('/sample-dispatches', [SampleDispatchController::class, 'store'])->name('sample-dispatches.store');
    Route::get('/sample-dispatches/{sampleDispatch}', [SampleDispatchController::class, 'show'])->name('sample-dispatches.show');
    Route::post('/sample-dispatches/{sampleDispatch}/receive', [SampleDispatchController::class, 'receive'])->name('sample-dispatches.receive');

    // Driver management (admin + Clinical Operations)
    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
    Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store');
    Route::put('/drivers/{driver}', [DriverController::class, 'update'])->name('drivers.update');
    Route::patch('/drivers/{driver}/toggle-active', [DriverController::class, 'toggleActive'])->name('drivers.toggle-active');

    // IAVIC114 reports
    Route::get('/iavic114-reports/create', [Iavic114ReportController::class, 'create'])->name('iavic114-reports.create');
    Route::post('/iavic114-reports', [Iavic114ReportController::class, 'store'])->name('iavic114-reports.store');
    Route::get('/iavic114-reports/{iavic114PbmcReport}/print', [Iavic114ReportController::class, 'printReport'])->name('iavic114-reports.print');
    Route::get('/iavic114-reports/{iavic114PbmcReport}', [Iavic114ReportController::class, 'show'])->name('iavic114-reports.show');

    // IAVIC114 exports
    Route::get('/iavic114-reports-export/excel', [Iavic114ReportController::class, 'exportExcel'])->name('iavic114-reports.export.excel');
    Route::post('/iavic114-reports-export/excel', [Iavic114ReportController::class, 'exportSelectedExcel'])->name('iavic114-reports.export.selected.excel');
    Route::get('/iavic114-reports-export/csv', [Iavic114ReportController::class, 'exportCsv'])->name('iavic114-reports.export.csv');
    Route::post('/iavic114-reports-export/csv', [Iavic114ReportController::class, 'exportSelectedCsv'])->name('iavic114-reports.export.selected.csv');
    Route::get('/iavic114-reports-export/pdf', [Iavic114ReportController::class, 'exportPdf'])->name('iavic114-reports.export.pdf');
    Route::post('/iavic114-reports-export/pdf', [Iavic114ReportController::class, 'exportSelectedPdf'])->name('iavic114-reports.export.selected.pdf');

    // ─── Admin-only ───────────────────────────────────────────────────────────
    Route::middleware('admin')->group(function () {

        // User management
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
        Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
        Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::patch('/admin/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::post('/admin/users/bulk/enable', [UserController::class, 'bulkEnable'])->name('admin.users.bulk.enable');
        Route::patch('/admin/users/bulk/disable', [UserController::class, 'bulkDisable'])->name('admin.users.bulk.disable');

        // Audit logs
        Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs');
    });
});

require __DIR__.'/auth.php';
