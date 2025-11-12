# Blog API - Copilot Instructions

## Project Overview

This is a **pure API-only Laravel 12** blog application using **PHP 8.2+** with Laravel Sanctum for token-based authentication. The project implements a RESTful API for blog posts with user authentication, comments, and image uploads. **No frontend/views** - strictly JSON API responses.

## Project Requirements

### Core Features
1. **Authentication** (Laravel Sanctum): Register, login, logout with token-based auth
2. **Posts CRUD**: Full CRUD with authorization (only owners can update/delete)
3. **Comments**: Users can comment on posts, delete own comments
4. **Image Upload**: Optional image upload for posts
5. **Bonus**: Search posts, API Resources, unit tests

## Architecture & Structure

### Authentication Strategy

-   **Laravel Sanctum** (`HasApiTokens` trait) is configured on the `User` model for token-based API authentication
-   Personal access tokens table migration exists (`2025_11_12_063858_create_personal_access_tokens_table.php`)
-   **Important**: API routes file does NOT exist yet - must be explicitly registered in `bootstrap/app.php` when creating API endpoints
-   Token authentication is the expected pattern (not session-based SPA auth)

### Database Configuration

-   **Default database**: SQLite (`database/database.sqlite`)
-   Testing uses in-memory SQLite (`:memory:`) per `phpunit.xml`
-   Migrations use anonymous class syntax: `return new class extends Migration`
-   Database connection can be changed via `DB_CONNECTION` env variable

### Routing & Middleware

Current routing is **web-only** (`bootstrap/app.php`):

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

When adding API routes, register them explicitly:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

## Development Workflow

### Essential Commands

```bash
# Complete setup (includes dependencies, migrations)
composer setup

# Development mode (runs server, queue worker, and log viewer concurrently)
composer dev

# Run tests
composer test
# Or directly: php artisan test
```

### Custom Composer Scripts

-   **`composer setup`**: Full project initialization (install → .env → key:generate → migrate)
-   **`composer dev`**: Runs 3 concurrent processes: PHP server, queue worker, and log viewer (pail)
-   **`composer test`**: Clears config cache and runs PHPUnit/Pest tests

## Code Conventions

### Model Patterns

-   Use typed properties with Laravel 11+ syntax
-   Mass assignment via `$fillable` array (not `$guarded`)
-   Casts as method returning array: `protected function casts(): array`
-   Example from `User` model:

```php
protected $fillable = ['name', 'email', 'password'];
protected function casts(): array {
    return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
}
```

### Testing Configuration

-   Feature tests in `tests/Feature`, unit tests in `tests/Unit`
-   Use `RefreshDatabase` trait when testing database interactions (currently commented out in example test)
-   Testing environment automatically uses in-memory SQLite and array cache/session drivers

### Queue & Background Jobs

-   Default queue connection: `database` (via `QUEUE_CONNECTION=database`)
-   Queue worker runs via `php artisan queue:listen --tries=1` in dev mode
-   Session and cache also use database driver by default

## Database Design & Relationships

### Models & Migrations
- **User**: HasMany Posts, HasMany Comments (uses `HasApiTokens` trait)
- **Post**: BelongsTo User, HasMany Comments, has optional image upload
- **Comment**: BelongsTo User, BelongsTo Post

### Migration Patterns
- Use anonymous class syntax: `return new class extends Migration`
- Foreign keys with cascading deletes: `$table->foreignId('user_id')->constrained()->onDelete('cascade')`
- Image field nullable: `$table->string('image')->nullable()`
- Use `timestamps()` for all tables

### Model Conventions
Example Post model structure:
```php
protected $fillable = ['title', 'content', 'image', 'user_id'];

public function user() {
    return $this->belongsTo(User::class);
}

public function comments() {
    return $this->hasMany(Comment::class);
}
```

## API Development Patterns

### Routing Structure
1. Create `routes/api.php` and register in `bootstrap/app.php`:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    ...
)
```

2. **Authentication routes** (public):
   - `POST /api/register`
   - `POST /api/login`

3. **Protected routes** (require `auth:sanctum`):
   - `POST /api/logout`
   - `POST /api/posts`
   - `PUT /api/posts/{post}`
   - `DELETE /api/posts/{post}`
   - `POST /api/posts/{post}/comments`
   - `DELETE /api/comments/{comment}`

4. **Public read routes**:
   - `GET /api/posts`
   - `GET /api/posts/{post}`
   - `GET /api/posts/{post}/comments`

### Authorization Patterns
- Use Policies for post/comment ownership checks
- Example: `$this->authorize('update', $post)` in controller
- Policy methods: `update()`, `delete()` check `$user->id === $post->user_id`

### API Resources
- Use API Resources for consistent JSON responses
- Example: `return new PostResource($post)`
- Include relationships conditionally: `'user' => new UserResource($this->whenLoaded('user'))`
- Collections: `return PostResource::collection($posts)`

### Image Upload Handling
- Store in `storage/app/public/images`
- Create symbolic link: `php artisan storage:link`
- Validate: `'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'`
- Save path in database, not full file

## Environment Notes

-   Local development uses `php artisan serve` (port 8000)
-   Health check endpoint available at `/up` (configured in routing)
-   Log channel: `single` (not stack) in development per `.env.example`
-   Mail mailer: `log` driver (emails written to logs during development)
