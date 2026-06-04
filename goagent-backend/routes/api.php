<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BidController;
use App\Http\Controllers\Api\JobController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'user']);

    // Jobs
    Route::get('/jobs',          [JobController::class, 'index']);
    Route::post('/jobs',         [JobController::class, 'store']);
    Route::get('/jobs/{id}',     [JobController::class, 'show']);
    Route::patch('/jobs/{id}',   [JobController::class, 'update']);
    Route::delete('/jobs/{id}',  [JobController::class, 'destroy']);

    // Bids
    Route::post('/jobs/{id}/bids',   [BidController::class, 'store']);
    Route::get('/jobs/{id}/bids',    [BidController::class, 'index']);
    Route::patch('/bids/{id}/accept', [BidController::class, 'accept']);
});
