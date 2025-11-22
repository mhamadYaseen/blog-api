<?php

namespace Modules\Posts\App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Posts\App\Models\Post;
use Modules\Posts\App\Policies\PostPolicy;

class PostsServiceProvider extends ServiceProvider
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
        Gate::policy(Post::class, PostPolicy::class);

        // Load module routes
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/api.php');

        // Load module migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');
    }
}
