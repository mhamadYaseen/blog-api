# Custom Module Monolith Guide

This guide shows how to change the project to a full custom-module monolith (no composer packages). Each domain (Users, Posts, Comments) becomes a self-contained module inside the app with its own Models, Controllers, Requests, Resources, Policies, Services, Providers, and optionally routes.

Goals

-   Isolate domain code into modules for easier reasoning and ownership.
-   Keep a single repository and single `composer.json` (no external packages per module).
-   Make it straightforward to navigate, test, and evolve each module.

Overview

-   Root modules folder: `Modules/` (at project root)
-   Each module: `Modules/<ModuleName>/`
-   Namespace root: `Modules\<ModuleName>` (PSR-4 mapping in `composer.json`)

Recommended module layout (example for `Posts`):

```
Modules/Posts/
├─ Actions/
│  ├─ CreatePostAction.php
│  ├─ UpdatePostAction.php
│  └─ DeletePostAction.php
├─ Controllers/
│  ├─ PostController.php
│  └─ AdminPostController.php (optional)
├─ Models/
│  └─ Post.php
├─ Policies/
│  └─ PostPolicy.php
├─ Requests/
│  ├─ StorePostRequest.php
│  └─ UpdatePostRequest.php
├─ Resources/
│  ├─ PostResource.php
│  └─ PostCollection.php
├─ Services/
│  └─ PostService.php
├─ Repositories/ (optional)
│  └─ EloquentPostRepository.php
├─ Providers/
│  └─ PostsServiceProvider.php
├─ Database/
│  ├─ Factories/
│  │  └─ PostFactory.php
│  ├─ Migrations/
│  │  └─ 2025_11_12_071229_create_posts_table.php
│  └─ Seeders/
│     └─ PostSeeder.php
├─ Tests/
│  ├─ Feature/
│  │  └─ PostTest.php
│  └─ Unit/
│     └─ PostServiceTest.php
├─ routes.php
└─ Routes/
   └─ api.php
```

Apply the same pattern for `Users` and `Comments` modules.

**Note on Routes**: You can choose between:

-   **Single `routes.php`**: Simple, all module routes in one file
-   **Routes folder with `api.php`**: Separates API routes, allows future `web.php`, `console.php` if needed

**Why move database files and actions into modules?**

-   **Full domain isolation**: Each module owns its migrations, factories, seeders, and action classes alongside its models and business logic.
-   **Easier navigation**: Everything related to Posts (or Users/Comments) lives in one place.
-   **Better scalability**: If you extract a module to a package later, all database artifacts and domain logic move together.
-   **Module-specific testing**: Tests live next to the code they verify.
-   **Action pattern**: Single-responsibility action classes encapsulate specific business operations (following the action service pattern).

Step-by-step migration plan

**1. Prepare composer autoloading**

-   Add a PSR-4 entry to `composer.json` for `Modules\` pointing to `Modules/` so PHP autoloading recognizes module classes.

Example `composer.json` snippet:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Modules\\": "Modules/"
    }
}
```

After editing `composer.json`, run:

```zsh
composer dump-autoload
```

**2. Create module directories**

-   Create the three module directories and subfolders, including database and test folders.

```zsh
# from project root
mkdir -p Modules/Users/{Actions,Controllers,Models,Requests,Resources,Policies,Services,Providers,Database/{Factories,Migrations,Seeders},Tests/{Feature,Unit},Routes}
mkdir -p Modules/Posts/{Actions,Controllers,Models,Requests,Resources,Policies,Services,Providers,Database/{Factories,Migrations,Seeders},Tests/{Feature,Unit},Routes}
mkdir -p Modules/Comments/{Actions,Controllers,Models,Requests,Resources,Policies,Services,Providers,Database/{Factories,Migrations,Seeders},Tests/{Feature,Unit},Routes}
```

**3. Move & update files**

-   Move domain files into their module folders and update namespaces.

Example: move `app/Models/Post.php` → `Modules/Posts/Models/Post.php` and change namespace at top from `namespace App;` or `namespace App\Models;` to:

```php
namespace Modules\Posts\Models;
```

-   Update any `use` references across the codebase to the new namespaces. Use a search-and-replace (or an IDE refactor) to update occurrences.

Helpful shell commands (use carefully):

```zsh
# move file
git mv app/Models/Post.php Modules/Posts/Models/Post.php
# then update namespaces using a small sed command (example):
# adjust these sed commands to match your current file header style
sed -i '' 's/namespace App\\Models;/namespace Modules\\Posts\\Models;/g' Modules/Posts/Models/Post.php
```

**4. Create & register module Service Providers**

-   Each module should provide a `Providers/<ModuleName>ServiceProvider.php`.
-   Register bindings, policies, routes, migrations, and factories in its `boot()` method.

Example `PostsServiceProvider` (full):

```php
namespace Modules\Posts\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Posts\Models\Post;
use Modules\Posts\Policies\PostPolicy;

class PostsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // bindings, repositories, services
    }

    public function boot()
    {
        // register policy
        Gate::policy(Post::class, PostPolicy::class);

        // load module routes
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');

        // load module migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // optionally load translations, views, etc.
    }
}
```

**Note on Factories**: Laravel auto-discovers factories based on model naming conventions. If you move `PostFactory.php` to `Modules/Posts/Database/Factories/`, ensure the namespace is:

```php
namespace Modules\Posts\Database\Factories;

use Modules\Posts\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'user_id' => \Modules\Users\Models\User::factory(),
        ];
    }
}
```

Update your Model's `HasFactory` trait usage:

```php
namespace Modules\Posts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Modules\Posts\Database\Factories\PostFactory::new();
    }
}
```

Then register module providers in `config/app.php` (or your bootstrap if needed):

```php
// config/app.php -> 'providers' => [ ... ]
Modules\Posts\Providers\PostsServiceProvider::class,
Modules\Users\Providers\UsersServiceProvider::class,
Modules\Comments\Providers\CommentsServiceProvider::class,
```

Because this project uses `bootstrap/app.php` to configure routing, ensure `bootstrap/app.php` references `config/app.php` providers or add registration there if you prefer central manual registration.

**5. Move and organize module routes**

You have two options for organizing module routes:

**Option A: Single `routes.php` per module (simpler)**

Each module has one `routes.php` file loaded by the service provider.

Example `Modules/Posts/routes.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Posts\Controllers\PostController;

Route::prefix('api')->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
});
```

Load in provider:

```php
$this->loadRoutesFrom(__DIR__ . '/../routes.php');
```

**Option B: Separate `Routes/api.php` per module (more organized)**

Create a `Routes/` folder in each module with `api.php` for API routes.

Example `Modules/Posts/Routes/api.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Posts\Controllers\PostController;

Route::prefix('api')->middleware('api')->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post}', [PostController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/posts', [PostController::class, 'store']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
        Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    });
});
```

Load in provider:

```php
$this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
```

**Moving existing `routes/api.php` to modules:**

```zsh
# Create Routes directories
mkdir -p Modules/Users/Routes
mkdir -p Modules/Posts/Routes
mkdir -p Modules/Comments/Routes

# Split routes/api.php content into module-specific files
# Example: Extract post routes to Posts module
# (Manually copy relevant route definitions to each module's Routes/api.php)
```

After moving routes to modules, you can either:

1. **Delete** the central `routes/api.php` (if all routes are in modules)
2. **Keep it minimal** for shared/global routes (health checks, etc.)

Example minimal `routes/api.php` (if keeping):

```php
<?php

use Illuminate\Support\Facades\Route;

// Global API routes (if any)
Route::get('/health', fn() => response()->json(['status' => 'ok']));
```

**Important**: Ensure `bootstrap/app.php` has API routing registered:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

**6. Move Action files to modules**

If you're using the Action pattern (single-responsibility classes for business operations), move them into each module's `Actions/` folder.

Example:

```zsh
# Move post actions
git mv app/Actions/Post/CreatePostAction.php Modules/Posts/Actions/CreatePostAction.php
git mv app/Actions/Post/UpdatePostAction.php Modules/Posts/Actions/UpdatePostAction.php
git mv app/Actions/Post/DeletePostAction.php Modules/Posts/Actions/DeletePostAction.php

# Move comment actions
git mv app/Actions/Comment/CreateCommentAction.php Modules/Comments/Actions/CreateCommentAction.php
git mv app/Actions/Comment/DeleteCommentAction.php Modules/Comments/Actions/DeleteCommentAction.php

# Move auth actions
git mv app/Actions/Auth/RegisterAction.php Modules/Users/Actions/RegisterAction.php
git mv app/Actions/Auth/LoginAction.php Modules/Users/Actions/LoginAction.php
git mv app/Actions/Auth/LogoutAction.php Modules/Users/Actions/LogoutAction.php
```

Update namespaces in action files:

```php
namespace Modules\Posts\Actions;

use Modules\Posts\Models\Post;
use Modules\Posts\Requests\StorePostRequest;

class CreatePostAction
{
    public function handle(StorePostRequest $request): Post
    {
        // action logic
    }
}
```

Update controller imports:

```php
use Modules\Posts\Actions\CreatePostAction;
```

**7. Update policies & authorization**

-   Keep policies inside modules (e.g. `Modules/Posts/Policies/PostPolicy.php`) and register them in the provider as shown.

**8. Move database files to modules**

**Migrations:**

-   Move migration files from `database/migrations` to each module's `Database/Migrations/` folder.
-   Service provider will load them via `$this->loadMigrationsFrom()`.

Example:

```zsh
# Move posts migration
git mv database/migrations/2025_11_12_071229_create_posts_table.php \
  Modules/Posts/Database/Migrations/2025_11_12_071229_create_posts_table.php

# Move comments migration
git mv database/migrations/2025_11_12_071233_create_comments_table.php \
  Modules/Comments/Database/Migrations/2025_11_12_071233_create_comments_table.php

# Move users table migration
git mv database/migrations/0001_01_01_000000_create_users_table.php \
  Modules/Users/Database/Migrations/0001_01_01_000000_create_users_table.php
```

Keep framework migrations (cache, jobs, personal_access_tokens) in `database/migrations` or create a separate `Core` or `Shared` module for cross-cutting concerns.

**Factories:**

-   Move factory files and update namespaces.

Example:

```zsh
git mv database/factories/PostFactory.php Modules/Posts/Database/Factories/PostFactory.php
```

Update namespace in `PostFactory.php`:

```php
namespace Modules\Posts\Database\Factories;
```

Update the model's `newFactory()` method as shown in step 4.

**Seeders:**

-   Move seeders into module `Database/Seeders/` folders.

Example:

```zsh
git mv database/seeders/PostSeeder.php Modules/Posts/Database/Seeders/PostSeeder.php
```

Update namespace:

```php
namespace Modules\Posts\Database\Seeders;
```

In `database/seeders/DatabaseSeeder.php`, call module seeders:

```php
$this->call([
    \Modules\Users\Database\Seeders\UserSeeder::class,
    \Modules\Posts\Database\Seeders\PostSeeder::class,
    \Modules\Comments\Database\Seeders\CommentSeeder::class,
]);
```

**9. Move tests to modules**

Move tests from `tests/Feature` and `tests/Unit` into each module's `Tests/` folder.

Example:

```zsh
# Move feature tests
git mv tests/Feature/PostTest.php Modules/Posts/Tests/Feature/PostTest.php
git mv tests/Feature/CommentTest.php Modules/Comments/Tests/Feature/CommentTest.php
git mv tests/Feature/AuthTest.php Modules/Users/Tests/Feature/AuthTest.php
```

Update test namespaces:

```php
namespace Modules\Posts\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;
    // ...
}
```

**Configure PHPUnit to discover module tests:**

Update `phpunit.xml` to include module test directories:

```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
        <directory>Modules/*/Tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
        <directory>Modules/*/Tests/Feature</directory>
    </testsuite>
</testsuites>
```

This allows PHPUnit to auto-discover tests in all module folders.

**10. Update composer.json autoload for factories**

Add module factory paths to `composer.json` so Laravel can discover them:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Modules\\": "Modules/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    }
},
"autoload-dev": {
    "psr-4": {
        "Tests\\": "tests/",
        "Modules\\Users\\Tests\\": "Modules/Users/Tests/",
        "Modules\\Posts\\Tests\\": "Modules/Posts/Tests/",
        "Modules\\Comments\\Tests\\": "Modules/Comments/Tests/"
    }
}
```

**Note**: The `Database\\Factories\\` mapping can stay pointing to the old location or be removed once all factories are moved to modules.

**11. Updating existing code that references moved classes**

-   Run a project-wide search for the old namespaces and update them.
-   Use an IDE's rename/refactor tools if available. Otherwise, use careful sed/perl replacements.

Example: replace `App\\Models\\Post` with `Modules\\Posts\\Models\\Post`:

```zsh
# Dry run search
grep -R "App\\Models\\Post" -n
# Replace (careful, run on branch and commit before massive changes)
# GNU sed: sed -i 's/old/new/g' $(grep -Ril "old" )
```

**12. Composer & autoload**

-   After moving files and updating namespaces, run:

```zsh
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

**13. Run tests & manual checks**

```zsh
# run migrations from modules
php artisan migrate:fresh

# run the test suite
composer test
# or
php artisan test

# seed database using module seeders
php artisan db:seed
```

Tips & Best Practices

-   Make small, incremental moves and run tests after each domain migration.
-   Keep commit granularity small (one module per commit) and use feature branches.
-   Use `git mv` to preserve history when moving files.
-   If your IDE supports it, use structural refactoring to rename namespaces and update imports automatically.
-   Keep controllers thin; move business logic to `Services` or `Repositories` inside modules.

Example: Minimal `Users` module snippet

`Modules/Users/Models/User.php`:

```php
namespace Modules\Users\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['name','email','password'];
}
```

`Modules/Users/Controllers/AuthController.php` (example route: `POST /api/register`)

`Modules/Users/Providers/UsersServiceProvider.php` -> register routes and any bindings.

Checklist before merging module refactor

-   [ ] `composer dump-autoload` succeeds
-   [ ] Module service providers registered in `config/app.php`
-   [ ] `php artisan migrate:fresh` runs successfully from module migrations
-   [ ] Factories work with `Model::factory()->create()` in tests/seeders
-   [ ] `php artisan db:seed` uses module seeders correctly
-   [ ] `php artisan test` passes with module tests discovered
-   [ ] Policies are registered and authorization behaves correctly
-   [ ] All routes (web & api) still load (check `bootstrap/app.php` route registration)
-   [ ] No leftover files in old `database/migrations`, `database/factories`, `database/seeders`, or `tests/` directories (or intentionally kept framework migrations)

Optional: scaffolding scripts

-   You can create a simple scaffolding script to make modules quickly. Example bash snippet:

```zsh
# new-module.sh
MODULE=$1
mkdir -p Modules/$MODULE/{Actions,Controllers,Models,Requests,Resources,Policies,Services,Providers,Database/{Factories,Migrations,Seeders},Tests/{Feature,Unit},Routes}
# create a basic provider file, routes.php, and README inside module
```

Closing notes

-   This approach keeps a monorepo but organizes code by domain rather than type across the entire app. It improves discoverability for domain owners and prepares the codebase for potential future extraction of modules into packages if desired.

If you'd like, I can:

-   Scaffold the three module directories and move existing files automatically (I can perform those repository edits).
-   Update `composer.json` PSR-4 autoload and run `composer dump-autoload` for you.

Tell me which of the above you'd like me to do next (scaffold, move files, update composer, or only provide further examples).
