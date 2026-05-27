<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\SampleDispatchWorkflowController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/token', [AuthTokenController::class, 'store'])->name('api.v1.auth.token.store');

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/auth/token', [AuthTokenController::class, 'destroy'])->name('api.v1.auth.token.destroy');
    Route::get('/sample-dispatches', [SampleDispatchWorkflowController::class, 'index'])->name('api.v1.sample-dispatches.index');
    Route::get('/sample-dispatches/{sampleDispatch}', [SampleDispatchWorkflowController::class, 'show'])->name('api.v1.sample-dispatches.show');
    Route::post('/sample-dispatches/{sampleDispatch}/receive', [SampleDispatchWorkflowController::class, 'receive'])->name('api.v1.sample-dispatches.receive');
    Route::post('/sample-dispatches/{sampleDispatch}/reject', [SampleDispatchWorkflowController::class, 'reject'])->name('api.v1.sample-dispatches.reject');
    Route::post('/sample-dispatches/{sampleDispatch}/process', [SampleDispatchWorkflowController::class, 'process'])->name('api.v1.sample-dispatches.process');
    });
});
