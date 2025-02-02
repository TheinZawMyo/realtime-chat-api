<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MessageController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // ============= Message =============
    Route::post('/send-message', [MessageController::class, 'sendMessage']);
    Route::get('/get-messages', [MessageController::class, 'getMessages']);
    Route::get('/search-users', [AuthController::class, 'searchUsers']);
    Route::get('/user-detail', [AuthController::class, 'getUserDetail']);
});

