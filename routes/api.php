<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\MusicCategoryController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\DecryptApplicationPayload;
use App\Http\Middleware\EncryptFetchMusicResponse;

# Health check endpoint
Route::get('/health', [HealthController::class, 'ping']);

# Auth route (optional body encryption: { "encryptedPayload": "<base64>" })
Route::middleware([DecryptApplicationPayload::class, 'throttle:auth'])->group(function () {
    Route::post('/register', [AuthController::class, 'register_user']);
    Route::post('/login', [AuthController::class, 'login_user']);
    Route::post('/forgot-password', [AuthController::class, 'forgot_password']);
    Route::patch('/reset-password', [AuthController::class, 'reset_password']);
});

# Music route
Route::middleware('throttle:api')->group(function () {
    Route::post('/save-music', [MusicController::class, 'save_music']);

    Route::middleware(['auth:sanctum'])->group(function () {
        # Music routes
        Route::get('/fetch-music-categories', [MusicCategoryController::class, 'get_music_categories']);
        Route::get('/fetch-music', [MusicController::class, 'get_music'])
            ->middleware([EncryptFetchMusicResponse::class]);
        Route::post('/init-save-music', [MusicController::class, 'init_create_music']);
        Route::post('/create-music-category', [MusicCategoryController::class, 'create_music_category']);
        Route::patch('/update-music-category', [MusicController::class, 'edit_music_category']);
        Route::post('/enable-disable-music', [MusicController::class, 'enable_disable_music']);

        # Logout user
        Route::delete('/logout', [AuthController::class, 'logout_user']);
    });

});

?>
