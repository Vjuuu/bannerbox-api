# CodeIgniter 3 API Backend

A comprehensive REST API backend built with CodeIgniter 3, featuring JWT authentication, user management, poster/banner management, and category system.

## Features

- ✅ **JWT Authentication System**
- ✅ **User Registration & Login**
- ✅ **User Profile Management**
- ✅ **Subscription Handling** (Basic/Pro/Premium)
- ✅ **Category Management**
- ✅ **Poster/Banner Management**
- ✅ **Trending Posters Endpoint**
- ✅ **API Key Authentication**
- ✅ **CORS Support**
- ✅ **Clean URL Support**
- ✅ **MySQL Database**

## Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Apache/Nginx with mod_rewrite enabled
- CodeIgniter 3.x system files

## Installation

1. **Download CodeIgniter 3 System Files**
   ```bash
   # Download CodeIgniter 3.1.13 (latest version)
   wget https://github.com/bcit-ci/CodeIgniter/archive/3.1.13.zip
   unzip 3.1.13.zip
   cp -r CodeIgniter-3.1.13/system ./
   ```

2. **Database Setup**
   - Create a MySQL database named `codeigniter_api`
   - Import the `database.sql` file
   - Update database credentials in `application/config/database.php`

3. **Configuration**
   - Update `base_url` in `application/config/config.php`
   - Set your JWT secret key in `application/config/config.php`
   - Set your API key in `application/config/config.php`

4. **Web Server Configuration**
   - Ensure mod_rewrite is enabled for Apache
   - Make sure the `.htaccess` file is working properly

## API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `GET /api/auth/profile` - Get user profile (JWT required)
- `PUT /api/auth/update-profile` - Update profile (JWT required)
- `PUT /api/auth/change-password` - Change password (JWT required)

### Categories
- `GET /api/categories` - Get all categories
- `GET /api/categories/{id}` - Get single category
- `POST /api/categories/create` - Create category (JWT required)
- `PUT /api/categories/update/{id}` - Update category (JWT required)
- `DELETE /api/categories/delete/{id}` - Delete category (JWT required)

### Posters
- `GET /api/posters` - Get all posters (with pagination)
- `GET /api/posters/{id}` - Get single poster
- `POST /api/posters/create` - Create poster (JWT required)
- `PUT /api/posters/update/{id}` - Update poster (JWT required)
- `DELETE /api/posters/delete/{id}` - Delete poster (JWT required)
- `GET /api/posters/trending` - Get trending posters
- `GET /api/posters/category/{id}` - Get posters by category

## Authentication

### API Key
All requests must include an API key in the header:
```
X-API-KEY: your-api-key-12345
```

### JWT Token
Protected endpoints require a JWT token:
```
Authorization: Bearer <jwt-token>
```

## Request Examples

### Login
```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: your-api-key-12345" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

### Get Posters
```bash
curl -X GET "http://localhost:8080/api/posters?page=1&limit=10" \
  -H "X-API-KEY: your-api-key-12345"
```

### Create Poster (JWT Required)
```bash
curl -X POST http://localhost:8080/api/posters/create \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: your-api-key-12345" \
  -H "Authorization: Bearer <jwt-token>" \
  -d '{
    "title": "My New Poster",
    "description": "A beautiful poster design",
    "image_url": "https://example.com/image.jpg",
    "category_id": 1,
    "tags": "design,poster,beautiful"
  }'
```

## Response Format

All API responses follow this format:

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description"
}
```

## Database Schema

### Users Table
- user_id (Primary Key)
- username (Unique)
- email (Unique)
- password (Hashed)
- full_name
- phone
- address
- subscription_type (basic/pro/premium)
- is_active
- last_login
- created_at
- updated_at

### Categories Table
- category_id (Primary Key)
- name (Unique)
- description
- icon
- color
- is_active
- created_at
- updated_at

### Posters Table
- poster_id (Primary Key)
- title
- description
- image_url
- thumbnail_url
- category_id (Foreign Key)
- tags
- is_premium
- is_featured
- download_count
- view_count
- created_at
- updated_at

## Security Features

- Password hashing using PHP's `password_hash()`
- JWT token-based authentication
- API key validation
- CORS headers for cross-origin requests
- SQL injection prevention through CodeIgniter's Query Builder
- Input validation and sanitization

## Development

### Default Admin Credentials
- Email: admin@example.com
- Password: password

### Test Users
- Email: john@example.com, Password: password (Pro subscription)
- Email: jane@example.com, Password: password (Basic subscription)

## Configuration Files

- `application/config/config.php` - Main configuration
- `application/config/database.php` - Database settings
- `application/config/routes.php` - API routes
- `.htaccess` - URL rewriting and CORS headers

## Error Handling

The API includes comprehensive error handling with appropriate HTTP status codes:
- 200: Success
- 400: Bad Request
- 401: Unauthorized
- 404: Not Found
- 405: Method Not Allowed
- 500: Internal Server Error

## Notes

- Ensure the CodeIgniter 3 `system` folder is properly installed
- Update configuration files with your specific settings
- Test all endpoints after setup
- The API supports CORS for frontend integration
- All responses are in JSON format
- Pagination is supported for poster listings