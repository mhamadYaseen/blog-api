<?php

use Illuminate\Support\Facades\Route;
use Modules\Comments\App\Http\Controllers\CommentController;

Route::prefix('api')->middleware('api')->group(function () {
    // Public comment routes
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

    // Protected comment routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    });
});
