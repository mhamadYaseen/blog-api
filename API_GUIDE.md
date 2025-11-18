# 📖 Blog API - Complete Guide

**Laravel 12 RESTful Blog API** - Token-based authentication, CRUD operations, soft deletes, and comprehensive testing.

---

## 🚀 Quick Start

```bash
# Complete setup
composer setup

# Start development (server + queue + logs)
composer dev

# Seed test data (5 users, 20 posts, 60-100 comments)
php artisan db:seed

# Run tests (45 tests, 184 assertions)
composer test
```

**API Base URL:** `http://localhost:8000/api`

---

## 📡 API Endpoints

### Authentication (Public)

```bash
POST   /api/register              # Register new user
POST   /api/login                 # Login user
POST   /api/logout                # Logout (authenticated)
GET    /api/user                  # Get current user (authenticated)
```

### Posts

```bash
# Public
GET    /api/posts                 # List all (paginated, 15/page)
GET    /api/posts/search?q=word   # Search posts
GET    /api/posts/{id}            # Get single post

# Authenticated
POST   /api/posts                 # Create post
PUT    /api/posts/{id}            # Update post (owner only)
DELETE /api/posts/{id}            # Soft delete (owner only)

# Soft Delete Management (Authenticated)
GET    /api/posts/trashed/list    # List deleted posts
POST   /api/posts/{id}/restore    # Restore post
DELETE /api/posts/{id}/force      # Permanently delete
```

### Comments

```bash
# Public
GET    /api/posts/{post}/comments # List comments for post

# Authenticated
POST   /api/posts/{post}/comments # Add comment
DELETE /api/comments/{id}         # Soft delete (owner only)
POST   /api/comments/{id}/restore # Restore comment
DELETE /api/comments/{id}/force   # Permanently delete
```

**Total:** 20+ endpoints

---

## 📋 Request/Response Examples

### 1. Register User

**Request:**

```bash
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-11-12T10:30:00.000000Z"
    },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
}
```

### 2. Create Post

**Request:**

```bash
POST /api/posts
Authorization: Bearer {token}
Content-Type: multipart/form-data

title: "My Blog Post"
content: "Post content here..."
image: (file upload - optional)
```

**Response (201):**

```json
{
    "data": {
        "id": 1,
        "title": "My Blog Post",
        "content": "Post content here...",
        "image": "https://storage.example.com/media/1/cover.jpg",
        "image_thumb": "https://storage.example.com/media/1/conversions/cover-thumb.jpg",
        "user": {
            "id": 1,
            "name": "John Doe"
        },
        "created_at": "2025-11-12T10:30:00.000000Z",
        "updated_at": "2025-11-12T10:30:00.000000Z"
    }
}
```

### 3. List Posts (Paginated)

**Request:**

```bash
GET /api/posts?page=1
```

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "title": "Post Title",
            "content": "Post content...",
            "image": "https://storage.example.com/media/5/cover.jpg",
            "image_thumb": "https://storage.example.com/media/5/conversions/cover-thumb.jpg",
            "user": {
                "id": 1,
                "name": "John Doe"
            },
            "created_at": "2025-11-12T10:30:00.000000Z"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/posts?page=1",
        "last": "http://localhost:8000/api/posts?page=2",
        "prev": null,
        "next": "http://localhost:8000/api/posts?page=2"
    },
    "meta": {
        "current_page": 1,
        "total": 20,
        "per_page": 15
    }
}
```

### 4. Add Comment

**Request:**

```bash
POST /api/posts/1/comments
Authorization: Bearer {token}
Content-Type: application/json

{
  "comment": "Great post!"
}
```

**Response (201):**

```json
{
    "data": {
        "id": 1,
        "comment": "Great post!",
        "user": {
            "id": 1,
            "name": "John Doe"
        },
        "post_id": 1,
        "created_at": "2025-11-12T11:30:00.000000Z"
    }
}
```

---

## 🔐 Privacy & Security

### User Data Privacy

-   **Auth endpoints** (`/register`, `/login`, `/user`): Return full user data including email
-   **Posts/Comments**: Return only user `id` and `name` (email hidden for privacy)

### Authentication

-   Token-based auth using Laravel Sanctum
-   Include token in header: `Authorization: Bearer {token}`
-   Tokens don't expire by default (configurable in `config/sanctum.php`)

### Authorization

-   Only post/comment owners can update or delete their content
-   Enforced via Laravel Policies (`PostPolicy`, `CommentPolicy`)

### Rate Limiting

-   60 requests per minute per user/IP
-   Headers included: `X-RateLimit-Limit`, `X-RateLimit-Remaining`

---

## 📮 Postman Collection (v2.0)

**File:** `Blog_API_Postman_Collection.json`

### Features

✅ **Auto-generated data** - Random names, emails, titles, content, comments
✅ **Smart variables** - Token, post_id, comment_id automatically saved
✅ **Pre-request scripts** - Generate realistic data before requests
✅ **Test scripts** - Validate responses automatically
✅ **Console logging** - See what's happening

### Quick Usage

1. Import `Blog_API_Postman_Collection.json` into Postman
2. Click **Register** → User auto-generated, token saved ✨
3. Click **Create Post** → Content auto-generated, ID saved ✨
4. Click **Create Comment** → Comment auto-generated ✨
5. All data generation is automatic - just click Send!

### Variables (Auto-managed)

-   `base_url` - API base URL (default: `http://localhost:8000/api`)
-   `token` - Authentication token (auto-saved on register/login)
-   `post_id` - Current post ID (auto-saved)
-   `comment_id` - Current comment ID (auto-saved)
-   `random_email`, `random_password`, `random_name` - User credentials

**Time Saved:** 80% reduction in manual testing work!

---

## 🧪 Testing

### Test Coverage

-   **45 tests** with **184 assertions**
-   **Feature tests (30):** AuthTest, PostTest, CommentTest
-   **Unit tests (15):** UserModelTest, PostModelTest, CommentModelTest

### Run Tests

```bash
composer test              # Run all tests
php artisan test          # Alternative command
php artisan test --filter AuthTest  # Run specific test file
```

### Test Results

```
Tests:    45 passed (184 assertions)
Duration: 0.63s
Status:   100% passing ✅
```

---

## ✨ Features

### Core Features

✅ User registration & authentication (Sanctum)
✅ Posts CRUD with image upload
✅ Comments system
✅ Owner-only authorization
✅ API Resources for consistent responses
✅ Input validation with Form Requests

### Bonus Features

✅ Search posts (title & content)
✅ Pagination (15 per page)
✅ Soft deletes with restore
✅ Rate limiting (60/min)
✅ Request/response logging
✅ Test data factories & seeders
✅ Comprehensive test suite
✅ Enhanced Postman collection

---

## 🗄️ Database

### Tables

-   `users` - User accounts
-   `posts` - Blog posts with soft deletes
-   `comments` - Post comments with soft deletes
-   `personal_access_tokens` - Sanctum tokens
-   `cache`, `jobs` - Framework tables

### Relationships

-   User **hasMany** Posts
-   User **hasMany** Comments
-   Post **belongsTo** User
-   Post **hasMany** Comments
-   Comment **belongsTo** User
-   Comment **belongsTo** Post

### Soft Deletes

Posts and comments use soft deletes (`deleted_at` column). Deleted items can be:

-   Listed: `GET /posts/trashed/list`
-   Restored: `POST /posts/{id}/restore`
-   Permanently deleted: `DELETE /posts/{id}/force`

---

## 📂 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php        # Register, login, logout
│   │   ├── PostController.php        # Posts CRUD + soft deletes
│   │   └── CommentController.php     # Comments CRUD + soft deletes
│   ├── Middleware/
│   │   └── LogApiRequests.php        # Request/response logging
│   ├── Requests/
│   │   ├── RegisterRequest.php       # Registration validation
│   │   ├── LoginRequest.php          # Login validation
│   │   ├── StorePostRequest.php      # Post creation validation
│   │   └── UpdatePostRequest.php     # Post update validation
│   └── Resources/
│       ├── UserResource.php          # User JSON response (privacy-aware)
│       ├── PostResource.php          # Post JSON response
│       └── CommentResource.php       # Comment JSON response
├── Models/
│   ├── User.php                      # User model (HasApiTokens)
│   ├── Post.php                      # Post model (SoftDeletes)
│   └── Comment.php                   # Comment model (SoftDeletes)
└── Policies/
    ├── PostPolicy.php                # Post authorization
    └── CommentPolicy.php             # Comment authorization

database/
├── factories/
│   ├── UserFactory.php               # Generate fake users
│   ├── PostFactory.php               # Generate fake posts
│   └── CommentFactory.php            # Generate fake comments
└── seeders/
    └── DatabaseSeeder.php            # Seed 5 users, 20 posts, 60+ comments

tests/
├── Feature/
│   ├── AuthTest.php                  # 6 auth tests
│   ├── PostTest.php                  # 15 post tests
│   └── CommentTest.php               # 9 comment tests
└── Unit/
    ├── UserModelTest.php             # 5 user model tests
    ├── PostModelTest.php             # 5 post model tests
    └── CommentModelTest.php          # 5 comment model tests
```

---

## ⚙️ Configuration

### Environment Variables

```env
APP_NAME="Laravel Blog API"
APP_URL=http://localhost:8000

# Database (SQLite default)
DB_CONNECTION=sqlite

# Queue & Cache
QUEUE_CONNECTION=database
CACHE_STORE=database

# Logging
LOG_CHANNEL=single
```

### Custom Composer Scripts

```bash
composer setup    # Complete setup (install, env, key, migrate)
composer dev      # Start server + queue + logs concurrently
composer test     # Run PHPUnit tests
```

---

## 🔧 Development

### Start Development Environment

```bash
composer dev
```

Runs 3 processes concurrently:

1. PHP server (`php artisan serve`)
2. Queue worker (`php artisan queue:listen`)
3. Log viewer (`php artisan pail`)

### Generate Test Data

```bash
php artisan migrate:fresh --seed
```

Creates:

-   5 users with realistic names
-   20 blog posts with images (via picsum.photos)
-   60-100 comments

### View Logs

```bash
php artisan pail         # Real-time log viewer
tail -f storage/logs/laravel.log  # Traditional tail
```

---

## 🚨 Error Responses

### Validation Error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

### Unauthorized (401)

```json
{
    "message": "Unauthenticated."
}
```

### Forbidden (403)

```json
{
    "message": "This action is unauthorized."
}
```

### Not Found (404)

```json
{
    "message": "Resource not found."
}
```

---

## 📊 Statistics

| Metric                  | Value             |
| ----------------------- | ----------------- |
| **Total Endpoints**     | 20+               |
| **Tests**               | 45 (100% passing) |
| **Test Assertions**     | 184               |
| **Controllers**         | 3                 |
| **Models**              | 3                 |
| **Policies**            | 2                 |
| **Resources**           | 3                 |
| **Form Requests**       | 4                 |
| **Migrations**          | 7                 |
| **Factories**           | 3                 |
| **Seeders**             | 1                 |
| **Documentation Lines** | 800+              |

---

## 🎯 Best Practices Implemented

✅ RESTful API design
✅ Token-based authentication
✅ Policy-based authorization
✅ API Resources for responses
✅ Form Request validation
✅ Soft deletes for data recovery
✅ Eager loading (avoid N+1)
✅ Pagination for large datasets
✅ Rate limiting
✅ Request/response logging
✅ Comprehensive testing
✅ Factory/Seeder separation

---

## 🛠️ Troubleshooting

### Issue: Token not working

**Solution:** Ensure `Authorization: Bearer {token}` header is included and token is valid

### Issue: 403 Forbidden on update/delete

**Solution:** You can only modify your own posts/comments. Create content first.

### Issue: Image upload fails

**Solution:** Run `php artisan storage:link` to create symbolic link

### Issue: Tests failing

**Solution:** Run `php artisan config:clear` then `composer test`

### Issue: Database errors

**Solution:** Run `php artisan migrate:fresh --seed`

---

## 📚 Additional Resources

-   **Laravel Documentation:** https://laravel.com/docs
-   **Sanctum Documentation:** https://laravel.com/docs/sanctum
-   **Postman Documentation:** https://learning.postman.com

---

## ✨ What Makes This API Special

🎯 **Complete Implementation** - Every requirement + bonuses
🔒 **Privacy-Aware** - User emails protected in public endpoints
⚡ **Fast Testing** - Postman collection with auto-generation
📖 **Well-Documented** - Comprehensive guides and examples
🧪 **Fully Tested** - 45 tests, 100% passing
🚀 **Production-Ready** - Rate limiting, logging, validation
💎 **Clean Code** - Laravel best practices, PSR standards

---

## 📝 Quick Reference

### Authentication Flow

1. Register → Get token
2. Use token in `Authorization: Bearer {token}` header
3. Access protected endpoints
4. Logout → Token revoked

### CRUD Flow

1. **Create:** POST with auth token
2. **Read:** GET (public or authenticated)
3. **Update:** PUT with auth token (owner only)
4. **Delete:** DELETE with auth token (owner only)

### Soft Delete Flow

1. **Delete:** Soft delete (can be restored)
2. **List Trashed:** View deleted items
3. **Restore:** Recover deleted item
4. **Force Delete:** Permanently remove (cannot undo)

---

**Status:** ✅ Production Ready
**Version:** 2.0
**Last Updated:** November 12, 2025
**License:** MIT
