<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TelegramErrorNotification;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \App\Http\Middleware\LogApiRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e) {
            // Only send to Telegram in production/staging environments
            if (app()->environment('production', 'staging')) {
                try {
                    Notification::route('telegram', env('TELEGRAM_ERRORS_BLOG_CHAT_ID'))
                        ->notify(new TelegramErrorNotification(
                            title: "Application Error",
                            message: $e->getMessage(),
                            context: [
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                                'url' => request()?->fullUrl(),
                                'user_id' => Auth::id(),
                                'trace' => $e->getTraceAsString(),
                            ],
                        ));
                } catch (\Exception $notificationException) {
                    // Silently fail if notification fails to prevent error loops
                    logger()->error('Failed to send Telegram notification', [
                        'error' => $notificationException->getMessage()
                    ]);
                }
            }
        });
    })->create();
