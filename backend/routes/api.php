<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampingController;
use App\Http\Controllers\Api\CampingSpotController;
use App\Http\Controllers\Api\BookingController;

// Public routes
Route::get('/test', function() {
    return response()->json(['message' => 'API is working!', 'timestamp' => now()]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public camping routes (viewing)
Route::get('/campings', [CampingController::class, 'index']);
Route::get('/campings/suggestions', [CampingController::class, 'suggestions']);
Route::get('/campings/{id}', [CampingController::class, 'show']);
Route::get('/camping-spots', [CampingSpotController::class, 'index']);
Route::get('/camping-spots/{id}', [CampingSpotController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Campings (owner only for create/update/delete)
    Route::post('/campings', [CampingController::class, 'store']);
    Route::put('/campings/{id}', [CampingController::class, 'update']);
    Route::delete('/campings/{id}', [CampingController::class, 'destroy']);

    // Camping spots (owner only)
    Route::post('/camping-spots', [CampingSpotController::class, 'store']);
    Route::put('/camping-spots/{id}', [CampingSpotController::class, 'update']);
    Route::delete('/camping-spots/{id}', [CampingSpotController::class, 'destroy']);

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
});
