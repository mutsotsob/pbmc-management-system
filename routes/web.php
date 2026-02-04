<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PbmcController;
use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('pbmc', PbmcController::class);
    Route::get('/settings', [DashboardController::class, 'settings']);
    Route::post('/settings/password', [DashboardController::class, 'updatePassword']);
    Route::post('/settings/profile', [DashboardController::class, 'updateProfile']);
    Route::get('/admin/users', [DashboardController::class, 'manageUsers'])
        ->name('admin.users');
         Route::get('/admin/users/create', [DashboardController::class, 'createUser'])
        ->name('admin.users.create');

    Route::post('/admin/users', [DashboardController::class, 'storeUser'])
        ->name('admin.users.store');

    Route::get('/admin/users/{user}', [DashboardController::class, 'showUser'])
        ->name('admin.users.show');

    Route::get('/admin/users/{user}/edit', [DashboardController::class, 'editUser'])
        ->name('admin.users.edit');

    Route::patch('/admin/users/{user}/toggle-status', [DashboardController::class, 'toggleUserStatus'])
        ->name('admin.users.toggle-status');
    Route::put('/admin/users/{user}', [DashboardController::class, 'updateUser'])
    ->name('admin.users.update');

    Route::post('/admin/users/bulk/enable', [DashboardController::class, 'bulkEnableUsers'])->name('admin.users.bulk.enable');
Route::patch('/admin/users/bulk/disable', [DashboardController::class, 'bulkDisableUsers'])->name('admin.users.bulk.disable');

});

require __DIR__.'/auth.php';
