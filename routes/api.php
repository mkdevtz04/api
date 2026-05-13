<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OnboardingController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\DiscoverController;
use App\Http\Controllers\API\SwipeController;
use App\Http\Controllers\API\MatchController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\FilterController;
use App\Http\Controllers\API\StoryController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register',          [AuthController::class, 'register']);
    Route::post('/login',             [AuthController::class, 'login']);
    Route::post('/email/send-otp',    [AuthController::class, 'sendEmailOtp']);
    Route::post('/email/verify-otp',  [AuthController::class, 'verifyEmailOtp']);
    Route::post('/email/complete-signup', [AuthController::class, 'completeEmailSignup']);
    Route::post('/email/test',        [AuthController::class, 'testEmail']);
    Route::post('/onboarding/gender', [OnboardingController::class, 'updateGender']);
    Route::post('/onboarding/interests', [OnboardingController::class, 'updateInterests']);
    Route::post('/onboarding/friends', [OnboardingController::class, 'updateFriendsPermission']);
    Route::post('/onboarding/notifications', [OnboardingController::class, 'updateNotifications']);
    Route::post('/phone/send-otp',    [AuthController::class, 'sendOtp']);
    Route::post('/phone/verify-otp',  [AuthController::class, 'verifyOtp']);
});

Route::get('/discover', [DiscoverController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Sanctum token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
   
    Route::delete('/auth/logout', [AuthController::class, 'logout']);

    // Profile
    // Route::get('/profile', [ProfileController::class, 'show']);
    // Route::put('/profile', [ProfileController::class, 'update']);
    // Route::post('/profile/photos',[ProfileController::class, 'uploadPhoto']);
    // Route::delete('/profile/photos/{id}', [ProfileController::class, 'deletePhoto']);

    // Discovery
    // Route::get('/discover',   [DiscoverController::class, 'index']);

    // Swipes
    Route::post('/swipes',    [SwipeController::class, 'store']);

    // Matches
    Route::get('/matches',    [MatchController::class, 'index']);

    // Messages
    Route::get('/matches/{matchId}/messages',  [MessageController::class, 'index']);
    Route::post('/matches/{matchId}/messages', [MessageController::class, 'store']);
    Route::patch('/messages/{id}/read',        [MessageController::class, 'markRead']);

    // Filters

    
});
