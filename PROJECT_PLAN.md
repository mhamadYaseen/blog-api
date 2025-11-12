# 📋 Complete Project Plan: Laravel Blog API

## **Phase 1: Project Setup & Environment** 
1. **Create Laravel 12 Project**
   - Initialize new Laravel project
   - Verify Laravel version
   - Configure `.env` file (database, app settings)

2. **Database Configuration**
   - Set up database connection (MySQL/PostgreSQL/SQLite)
   - Configure database credentials

3. **Install Dependencies**
   - Install Laravel Sanctum for authentication
   - Install any additional packages (if needed)

---

## **Phase 2: Database Design & Migrations**
1. **Create Migrations**
   - Users table (comes with Laravel, may need modifications)
   - Posts table (id, user_id, title, content, image, timestamps)
   - Comments table (id, post_id, user_id, comment, timestamps)

2. **Create Models**
   - User model (update with relationships)
   - Post model (with relationships & fillable fields)
   - Comment model (with relationships & fillable fields)

3. **Define Relationships**
   - User hasMany Posts
   - User hasMany Comments
   - Post belongsTo User
   - Post hasMany Comments
   - Comment belongsTo User
   - Comment belongsTo Post

---

## **Phase 3: Authentication System**
1. **Configure Laravel Sanctum**
   - Publish Sanctum configuration
   - Add Sanctum middleware
   - Configure API tokens

2. **Create Auth Controllers**
   - AuthController for register/login/logout

3. **Create Auth Requests (Validation)**
   - RegisterRequest
   - LoginRequest

4. **Define Auth Routes**
   - POST /api/register
   - POST /api/login
   - POST /api/logout (protected)

---

## **Phase 4: Posts Module**
1. **Create Post Controller**
   - index() - list all posts
   - show() - view single post
   - store() - create post
   - update() - update post
   - destroy() - delete post

2. **Create Form Requests (Validation)**
   - StorePostRequest
   - UpdatePostRequest

3. **Create API Resources**
   - PostResource for clean JSON responses
   - PostCollection for list responses

4. **Implement Authorization**
   - Post Policy (only owner can update/delete)
   - Apply middleware for protected routes

5. **Image Upload Handling**
   - Configure storage
   - Handle image validation
   - Store images in storage/public
   - Create symbolic link for public access

6. **Define Post Routes**
   - All CRUD routes as specified

---

## **Phase 5: Comments Module**
1. **Create Comment Controller**
   - index() - list comments for a post
   - store() - add comment to post
   - destroy() - delete comment

2. **Create Form Requests**
   - StoreCommentRequest

3. **Create API Resources**
   - CommentResource
   - CommentCollection

4. **Implement Authorization**
   - Comment Policy (only owner can delete)

5. **Define Comment Routes**
   - GET /api/posts/{id}/comments
   - POST /api/posts/{id}/comments
   - DELETE /api/comments/{id}

---

## **Phase 6: Extra Features (Optional Bonus)**
1. **Search Functionality**
   - Add search endpoint: GET /api/posts/search?q=keyword
   - Implement search in title and content
   - Use query scopes in Post model

2. **Pagination**
   - Add pagination to posts listing
   - Add pagination to comments listing

3. **Advanced API Resources**
   - Ensure all responses use Resources
   - Add conditional fields
   - Include relationships when needed

---

## **Phase 7: Testing**
1. **Feature Tests**
   - Test user registration
   - Test user login/logout
   - Test post creation (authenticated)
   - Test post update/delete (authorization)
   - Test comment creation
   - Test comment deletion (authorization)

2. **Unit Tests**
   - Test model relationships
   - Test validation rules

---

## **Phase 8: Documentation**
1. **Create README.md**
   - Project description
   - Prerequisites (PHP, Composer, Laravel version)
   - Installation steps
   - Database setup instructions
   - Running migrations
   - Starting the server
   - API documentation with examples

2. **API Documentation**
   - Document all endpoints
   - Provide example requests (curl/Postman)
   - Show example responses
   - Document error responses
   - Authentication header examples

3. **Create Postman Collection** (Optional)
   - Export collection with all endpoints
   - Include example requests with variables
   - Add pre-request scripts for tokens

---

## **Phase 9: Code Quality & Best Practices**
1. **Code Organization**
   - Follow PSR standards
   - Use service classes if needed
   - Keep controllers thin

2. **Error Handling**
   - Implement global exception handler
   - Return consistent error responses
   - Handle validation errors properly

3. **Security**
   - Validate all inputs
   - Sanitize file uploads
   - Protect against SQL injection (Eloquent does this)
   - Rate limiting on auth endpoints

---

## **Phase 10: Final Review & Deployment Prep**
1. **Code Review**
   - Check all functionality
   - Review code for improvements
   - Ensure all requirements are met

2. **Testing**
   - Run all tests
   - Manual testing of all endpoints

3. **Documentation Review**
   - Verify README accuracy
   - Test setup instructions

4. **Git Repository**
   - Create .gitignore
   - Commit with meaningful messages
   - Push to GitHub
   - Add descriptive repository description

---

## 🎯 Summary of Key Deliverables
- ✅ Working Laravel 12 API
- ✅ Authentication with Sanctum (register, login, logout)
- ✅ Posts CRUD with image upload
- ✅ Comments system
- ✅ Authorization (only owners can modify)
- ✅ API Resources for clean responses
- ✅ Search functionality (bonus)
- ✅ Unit tests (bonus)
- ✅ Comprehensive README.md
- ✅ Postman collection
- ✅ GitHub repository

---

## 🧩 Assignment Requirements Reference

### 1. Authentication
- POST /api/register → register a new user (name, email, password)
- POST /api/login → login and return token
- POST /api/logout → logout user (invalidate token)

### 2. Posts Module
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/posts | List all posts (latest first) |
| GET | /api/posts/{id} | View single post |
| POST | /api/posts | Create post (auth required) |
| PUT | /api/posts/{id} | Update post (auth required & only owner) |
| DELETE | /api/posts/{id} | Delete post (auth required & only owner) |

**Post Fields:**
- title (string, required)
- content (text, required)
- image (nullable, file upload – optional)

### 3. Comments Module
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/posts/{id}/comments | List comments of a post |
| POST | /api/posts/{id}/comments | Add comment (auth required) |
| DELETE | /api/comments/{id} | Delete comment (only owner) |

**Comment Fields:**
- comment (text, required)

### 4. Extra Features (Optional Bonus)
- Implement search for posts by title or content
- Use API Resources (PostResource, CommentResource) for clean responses
- Add unit tests for post creation and comment creation
