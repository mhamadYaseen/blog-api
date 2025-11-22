<?php

use Illuminate\Support\Facades\Route;
use Modules\Posts\Controllers\PostController;

Route::prefix('api')->middleware('api')->group(function () {
    // Public post routes
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/search', [PostController::class, 'search']);
    Route::get('/posts/{post}', [PostController::class, 'show']);

    // Protected post routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/posts', [PostController::class, 'store']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
        Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    });
});
