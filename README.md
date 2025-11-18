# 📝 Laravel Blog API

A RESTful API for a blog application built with Laravel 12, featuring user authentication, posts management, and comments functionality.

## 📋 Table of Contents

-   [About](#about)
-   [Features](#features)
-   [Prerequisites](#prerequisites)
-   [Installation](#installation)
-   [Database Setup](#database-setup)
-   [Running the Application](#running-the-application)
-   [API Documentation](#api-documentation)
-   [Running Tests](#running-tests)
-   [Project Structure](#project-structure)
-   [License](#license)

## 🎯 About

This is a pure API-only Laravel application that provides endpoints for managing blog posts, user authentication, and comments. The API uses Laravel Sanctum for token-based authentication and returns JSON responses for all endpoints.

## ✨ Features

-   ✅ User registration and authentication (Laravel Sanctum)
-   ✅ JWT token-based API authentication
-   ✅ Full CRUD operations for blog posts
-   ✅ Image uploads powered by Spatie Laravel Media Library (original + thumbnail URLs)
-   ✅ Comments system for posts
-   ✅ Authorization (only owners can update/delete their content)
-   ✅ Search functionality for posts (query parameter & dedicated endpoint)
-   ✅ API Resources for consistent JSON responses
-   ✅ Pagination (15 posts per page)
-   ✅ Factory & Seeder support for rich test data
-   ✅ API Rate Limiting (60 requests per minute)
-   ✅ Request/Response Logging middleware
-   ✅ Comprehensive test coverage

## 🔧 Prerequisites

Before you begin, ensure you have the following installed:

-   **PHP**: >= 8.2
-   **Composer**: Latest version
-   **Node.js & NPM**: For development tools (concurrently)
-   **SQLite**: (default) or MySQL/PostgreSQL

## 📥 Installation

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd blog-api
```

### Step 2: Install Dependencies

```bash
composer install
npm install
```

## 🖼 Media Handling

-   Uses **spatie/laravel-medialibrary 11.x** for post cover uploads.
-   Images are stored on the `public` disk inside the `cover` collection with automatic cleanup on delete.
-   Two responsive conversions are generated synchronously (no queue):
    -   `thumb` – 300x300 for previews
    -   `large` – 1200x900 for full-width usage
-   API responses expose both `image` (full URL) and `image_thumb` (thumbnail URL).
-   Ensure `php artisan storage:link` has been run (handled during `composer setup`).
-   Legacy `posts.image` column remains temporarily for backward compatibility and will be removed after data migration.

### Step 3: Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Configure Environment Variables

Edit your `.env` file and configure the following:

```env
APP_NAME="Laravel Blog API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration (SQLite by default)
DB_CONNECTION=sqlite
# For MySQL, uncomment and configure:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=blog_api
# DB_USERNAME=root
# DB_PASSWORD=
```

## 🗄️ Database Setup

### Step 1: Create Database File (SQLite)

```bash
touch database/database.sqlite
```

Or use the automated setup:

```bash
composer setup
```

### Step 2: Run Migrations

```bash
php artisan migrate
```

This will create the following tables:

-   `users` - User accounts
-   `posts` - Blog posts with user relationship
-   `comments` - Comments on posts
-   `personal_access_tokens` - Sanctum authentication tokens

### Step 3: (Optional) Seed Sample Data

To populate the database with test data for development:

```bash
php artisan db:seed
```

This will create:

-   **5 users** with random names and emails
-   **20 blog posts** (4 posts per user) with:
    -   Realistic titles and content using Faker
    -   Random images from [picsum.photos](https://picsum.photos)
-   **60-100 comments** (2-5 random comments per post)

To refresh and reseed the entire database:

```bash
php artisan migrate:fresh --seed
```

## 🚀 Running the Application

### Development Mode

Run the complete development environment (server + queue worker + logs):

```bash
composer dev
```

This command starts:

-   Laravel development server on `http://localhost:8000`
-   Queue worker for background jobs
-   Real-time log viewer (Pail)

### Individual Commands

```bash
# Start development server only
php artisan serve

# Run queue worker
php artisan queue:listen

# View logs in real-time
php artisan pail
```

## 📚 API Documentation

### Base URL

```
http://localhost:8000/api
```

### Rate Limiting

All API endpoints are rate-limited to **60 requests per minute** per user/IP address. When the limit is exceeded, you'll receive a `429 Too Many Requests` response.

Rate limit headers are included in all responses:

-   `X-RateLimit-Limit`: Maximum requests allowed
-   `X-RateLimit-Remaining`: Remaining requests in current window
-   `Retry-After`: Seconds until rate limit resets (only on 429 responses)

### Request Logging

All API requests and responses are automatically logged for monitoring and debugging purposes. Logs include:

-   HTTP method, URL, and IP address
-   Authenticated user ID (if applicable)
-   Response status code and duration
-   User agent information

View logs in real-time with: `composer dev` or `php artisan pail`

### Authentication Endpoints

#### Register a New User

**POST** `/api/register`

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201 Created):**

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

#### Login

**POST** `/api/login`

**Request Body:**

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200 OK):**

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "2|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
}
```

#### Logout

**POST** `/api/logout`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200 OK):**

```json
{
    "message": "Logged out successfully"
}
```

---

### Posts Endpoints

#### List All Posts

**GET** `/api/posts`

**Response (204 No Content)**

**Note:** This action permanently removes the post from the database.
**Request Body (Form Data):**

```
title: "My New Post"
content: "This is the content of my post."
image: (file upload - optional)
```

**Response (201 Created):**

```json
{
    "data": {
        "id": 2,
        "title": "My New Post",
        "content": "This is the content of my post.",
        "image": "https://storage.example.com/media/1/cover.jpg",
        "image_thumb": "https://storage.example.com/media/1/conversions/cover-thumb.jpg",
        "user": {
            "id": 1,
            "name": "John Doe"
        },
        "created_at": "2025-11-12T11:00:00.000000Z"
    }
}
```

#### Update Post (Authenticated, Owner Only)

**PUT/PATCH** `/api/posts/{id}`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "title": "Updated Post Title",
    "content": "Updated content."
}
```

**Response (200 OK):**

```json
{
  "data": {
    "id": 2,
    "title": "Updated Post Title",
    "content": "Updated content.",
    "user": {...},
    "updated_at": "2025-11-12T12:00:00.000000Z"
  }
}
```

#### Delete Post (Authenticated, Owner Only)

**DELETE** `/api/posts/{id}`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (204 No Content)**

**Note:** This action permanently removes the post from the database.

---

#### List Trashed Posts (Authenticated)

**GET** `/api/posts/trashed/list`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200 OK):** Returns paginated list of soft-deleted posts.

---

#### Restore Deleted Post (Authenticated, Owner Only)

**POST** `/api/posts/{id}/restore`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200 OK):**

```json
{
    "message": "Post restored successfully.",
    "data": {
        "id": 1,
        "title": "Restored Post",
        ...
    }
}
```

---

#### Permanently Delete Post (Authenticated, Owner Only)

**DELETE** `/api/posts/{id}/force`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200 OK):**

```json
{
    "message": "Post permanently deleted."
}
```

**Warning:** This permanently removes the post from the database. This action cannot be undone.

---

### Comments Endpoints

#### Get Comments for a Post

**GET** `/api/posts/{post_id}/comments`

**Response (200 OK):**

```json
{
    "data": [
        {
            "id": 1,
            "comment": "Great post!",
            "user": {
                "id": 2,
                "name": "Jane Smith"
            },
            "created_at": "2025-11-12T11:30:00.000000Z"
        }
    ]
}
```

#### Add Comment to Post (Authenticated)

**POST** `/api/posts/{post_id}/comments`

**Headers:**

```
Authorization: Bearer {token}
```

**Request Body:**

```json
{
    "comment": "This is a great post!"
}
```

**Response (201 Created):**

```json
{
    "data": {
        "id": 3,
        "comment": "This is a great post!",
        "user": {
            "id": 1,
            "name": "John Doe"
        },
        "post_id": 1,
        "created_at": "2025-11-12T12:30:00.000000Z"
    }
}
```

#### Delete Comment (Authenticated, Owner Only)

**DELETE** `/api/comments/{id}`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (204 No Content)**

**Note:** This action permanently removes the comment from the database.

---

---

### Search Posts

**GET** `/api/posts/search?q={keyword}`

**Example:**

```
GET /api/posts/search?q=laravel
```

**Response:** Same as List All Posts, but filtered by search term.

---

### Error Responses

All error responses follow this format:

**Validation Error (422 Unprocessable Entity):**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

**Unauthorized (401):**

```json
{
    "message": "Unauthenticated."
}
```

**Forbidden (403):**

```json
{
    "message": "This action is unauthorized."
}
```

**Response (204 No Content)**

**Note:** This action permanently removes the post from the database.

-   Troubleshooting tips
-   Privacy notes (user emails hidden in posts/comments)

---

## 🧪 Running Tests

The project includes comprehensive test coverage with **45 tests** covering all features.

### Run Tests

```bash
# Run all tests
**Response (204 No Content)**

**Note:** This action permanently removes the post from the database.
│       ├── PostPolicy.php
│       └── CommentPolicy.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── storage/
    └── app/
        └── public/
            └── images/
```

## 🔑 Key Technologies

-   **Laravel 12**: PHP framework
-   **Laravel Sanctum**: API authentication
-   **SQLite**: Default database (easily switchable)
-   **PHPUnit**: Testing framework
-   **Laravel Pail**: Real-time log viewer

## 📝 Assumptions & Notes

1. **Database**: SQLite is used by default for simplicity. Can be easily switched to MySQL/PostgreSQL by updating `.env`

2. **Image Storage**: Images are stored in `storage/app/public/images` and accessed via symbolic link

3. **Authentication**: Token-based authentication using Laravel Sanctum. Tokens don't expire by default but can be configured in `config/sanctum.php`

4. **Authorization**: Post and Comment policies ensure only owners can update/delete their content

5. **Pagination**: Posts are paginated with 15 items per page by default

6. **Validation**: All inputs are validated using Form Request classes

7. **API Resources**: All responses use API Resources for consistent JSON formatting

8. **Privacy**: User emails are only returned in authentication endpoints (`/register`, `/login`, `/user`). Posts and comments responses only show user `id` and `name` to protect user privacy

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
