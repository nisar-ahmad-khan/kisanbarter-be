<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingImageController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CategoryController;

    // Public Auth Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
    });


// Public listing routes \\
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{listing}', [ListingController::class, 'show']);

// Protected listing routes:
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/listings', [ListingController::class, 'store']);
    Route::put('/listings/{listing}', [ListingController::class, 'update']);
    Route::patch('/listings/{listing}/toggle-status', [ListingController::class, 'toggleStatus']);
    Route::delete('/listings/{listing}', [ListingController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/listings/{listing}/images', [ListingImageController::class, 'store']);
    Route::delete('/listing-images/{image}', [ListingImageController::class, 'destroy']);
});

// Offer routes \\

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/offers', [OfferController::class, 'index']);
    Route::post('/offers', [OfferController::class, 'store']);
    Route::get('/offers/{offer}', [OfferController::class, 'show']);
    Route::post('/offers/{offer}/accept', [OfferController::class, 'accept']);
    Route::post('/offers/{offer}/reject', [OfferController::class, 'reject']);
    Route::delete('/offers/{offer}', [OfferController::class, 'cancel']);
});


// user reporting routes \\

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/reports', [ReportController::class, 'store']);

    // Admin reporting management endpoints
    // Route::get('/admin/reports', [ReportController::class, 'index']);
    // Route::delete('/admin/reports/{report}', [ReportController::class, 'destroy']);
});

// message routes \\

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
});

//categories:

Route::get('/categories', [CategoryController::class, 'index']);
