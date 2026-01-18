# Authentication API

User authentication and profile management endpoints.

## Overview

The Authentication API handles user registration, login, profile management, and password changes. All endpoints require the `X-API-KEY` header, and protected endpoints additionally require a JWT token.

## Base URL

```
/api/auth
```

## Endpoints

---

### Login

Authenticate a user with email and password.

```http
POST /api/auth/login
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |
| `Content-Type` | `application/json` | Yes |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | Yes | User's email address |
| `password` | string | Yes | User's password |

#### Example Request

```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 2592000,
    "user": {
      "user_id": 1,
      "username": "johndoe",
      "email": "user@example.com",
      "full_name": "John Doe",
      "subscription_type": "basic",
      "subscription_status": "none"
    }
  }
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 400 | Email and password are required |
| 401 | Invalid credentials |
| 401 | Invalid API key |

---

### Google Login

Authenticate a user with Google OAuth.

```http
POST /api/auth/google-login
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |
| `Content-Type` | `application/json` | Yes |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id_token` | string | Yes | Google OAuth ID token from client-side authentication |

#### Example Request

```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "is_new_user": false,
    "user": {
      "user_id": 1,
      "email": "user@gmail.com",
      "full_name": "John Doe",
      "profile_picture": "https://lh3.googleusercontent.com/..."
    }
  }
}
```

#### Notes

- If the user doesn't exist, a new account is automatically created
- `is_new_user` indicates if this was a new registration

---

### Register

Create a new user account.

```http
POST /api/auth/register
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |
| `Content-Type` | `application/json` | Yes |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `username` | string | Yes | Unique username (alphanumeric, 3-50 chars) |
| `email` | string | Yes | Valid email address |
| `password` | string | Yes | Password (min 6 characters) |
| `full_name` | string | No | User's full name |

#### Example Request

```json
{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "securePassword123",
  "full_name": "John Doe"
}
```

#### Success Response (201)

```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user_id": 5
  }
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 400 | Username, email, and password are required |
| 400 | Password must be at least 6 characters |
| 400 | Invalid email format |
| 400 | Username already exists |
| 400 | Email already registered |

---

### Get Profile

Retrieve the authenticated user's profile.

```http
GET /api/auth/profile
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |
| `Authorization` | `Bearer <jwt_token>` | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "data": {
    "user_id": 1,
    "username": "johndoe",
    "email": "john@example.com",
    "full_name": "John Doe",
    "phone": "+1234567890",
    "address": "123 Main St",
    "profile_picture": null,
    "subscription_type": "premium",
    "subscription_status": "active",
    "subscription_expires_at": "2026-02-18 12:00:00",
    "created_at": "2025-01-01 10:00:00"
  }
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 401 | Authorization header missing |
| 401 | Invalid or expired token |

---

### Update Profile

Update the authenticated user's profile information.

```http
PUT /api/auth/update-profile
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |
| `Authorization` | `Bearer <jwt_token>` | Yes |
| `Content-Type` | `application/json` | Yes |

#### Request Body

All fields are optional. Only include fields you want to update.

| Field | Type | Description |
|-------|------|-------------|
| `full_name` | string | User's full name |
| `phone` | string | Phone number |
| `address` | string | Address |

#### Example Request

```json
{
  "full_name": "John Smith",
  "phone": "+1987654321",
  "address": "456 New Street, City"
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Profile updated successfully"
}
```

---

### Change Password

Change the authenticated user's password.

```http
PUT /api/auth/change-password
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |
| `Authorization` | `Bearer <jwt_token>` | Yes |
| `Content-Type` | `application/json` | Yes |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `current_password` | string | Yes | Current password |
| `new_password` | string | Yes | New password (min 6 characters) |

#### Example Request

```json
{
  "current_password": "oldPassword123",
  "new_password": "newSecurePassword456"
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 400 | Current password and new password are required |
| 400 | New password must be at least 6 characters |
| 400 | Current password is incorrect |

---

## JWT Token Details

### Token Structure

The JWT token contains the following claims:

| Claim | Description |
|-------|-------------|
| `user_id` | User's unique identifier |
| `email` | User's email address |
| `subscription_type` | 'basic' or 'premium' |
| `iat` | Issued at timestamp |
| `exp` | Expiration timestamp |

### Token Expiration

- Default expiration: 30 days
- After expiration, the user must log in again to get a new token

### Using the Token

Include the token in the `Authorization` header:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```
