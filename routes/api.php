<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public post routes
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/search', [PostController::class, 'search']);
Route::get('/posts/{post}', [PostController::class, 'show']);

// Public comment routes
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Protected post routes
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    // Soft delete management for posts
    Route::get('/posts/trashed/list', [PostController::class, 'trashed']);
    Route::post('/posts/{id}/restore', [PostController::class, 'restore']);
    Route::delete('/posts/{id}/force', [PostController::class, 'forceDelete']);

    // Protected comment routes
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Soft delete management for comments
    Route::post('/comments/{id}/restore', [CommentController::class, 'restore']);
    Route::delete('/comments/{id}/force', [CommentController::class, 'forceDelete']);
});
