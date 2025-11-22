<?php

namespace Modules\Comments\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Comments\Models\Comment;
use Modules\Comments\Policies\CommentPolicy;

class CommentsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register policy
        Gate::policy(Comment::class, CommentPolicy::class);

        // Load module routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');

        // Load module migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
