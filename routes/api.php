<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TestTelegramNotification;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Module routes are now loaded via their respective service providers:
| - Modules\Users\Providers\UsersServiceProvider
| - Modules\Posts\Providers\PostsServiceProvider
| - Modules\Comments\Providers\CommentsServiceProvider
|
| Add global/shared API routes here if needed.
|
*/

// Test Telegram connectivity
Route::get('/test-telegram', function () {
    try {
        $chatId = env('TELEGRAM_ERRORS_BLOG_CHAT_ID');

        Notification::route('telegram', $chatId)
            ->notify(new TestTelegramNotification('Testing from API route at ' . now()->toDateTimeString()));

        return response()->json([
            'status' => 'success',
            'message' => 'Test notification sent to Telegram!',
            'chat_id' => $chatId,
            'timestamp' => now()->toDateTimeString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// Test error notification (forces environment to production for testing)
Route::get('/test-error', function () {
    // Temporarily override environment for testing
    app()->detectEnvironment(fn() => 'production');

    throw new \Exception('Test error from /test-error endpoint - This should appear in Telegram!');
});
