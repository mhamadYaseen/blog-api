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
-   ✅ Image upload support for posts
-   ✅ Comments system for posts
-   ✅ Authorization (only owners can update/delete their content)
-   ✅ Search functionality for posts
-   ✅ API Resources for consistent JSON responses
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

```bash
php artisan db:seed
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

**Query Parameters:**

-   `search` (optional): Search by title or content
-   `page` (optional): Pagination page number

**Response (200 OK):**

```json
{
  "data": [
    {
      "id": 1,
      "title": "My First Post",
      "content": "This is the content of my first post.",
      "image": "images/post1.jpg",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "created_at": "2025-11-12T10:30:00.000000Z",
      "updated_at": "2025-11-12T10:30:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

#### Get Single Post

**GET** `/api/posts/{id}`

**Response (200 OK):**

```json
{
    "data": {
        "id": 1,
        "title": "My First Post",
        "content": "This is the content of my first post.",
        "image": "images/post1.jpg",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "comments_count": 5,
        "created_at": "2025-11-12T10:30:00.000000Z",
        "updated_at": "2025-11-12T10:30:00.000000Z"
    }
}
```

#### Create Post (Authenticated)

**POST** `/api/posts`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

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
        "image": "images/xyz123.jpg",
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

**Not Found (404):**

```json
{
    "message": "Resource not found."
}
```

## 🧪 Running Tests

Run the test suite:

```bash
# Run all tests
composer test

# Or directly with artisan
php artisan test

# Run specific test file
php artisan test --filter=PostTest

# Run with coverage
php artisan test --coverage
```

## 📁 Project Structure

```
blog-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── PostController.php
│   │   │   └── CommentController.php
│   │   ├── Requests/
│   │   │   ├── LoginRequest.php
│   │   │   ├── RegisterRequest.php
│   │   │   ├── StorePostRequest.php
│   │   │   ├── UpdatePostRequest.php
│   │   │   └── StoreCommentRequest.php
│   │   └── Resources/
│   │       ├── PostResource.php
│   │       ├── CommentResource.php
│   │       └── UserResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Post.php
│   │   └── Comment.php
│   └── Policies/
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

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
