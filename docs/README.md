# BannerBox API Documentation

Welcome to the BannerBox API documentation. This API provides endpoints for managing posters, categories, user authentication, and subscription payments via Razorpay.

## Table of Contents

- [Getting Started](#getting-started)
- [Authentication](#authentication)
- [API Reference](#api-reference)
- [Error Handling](#error-handling)

## Getting Started

### Base URL

```
Production: https://api.bannerbox.com
Development: http://bannerbox.test
```

### Headers

All API requests should include the following headers:

| Header | Value | Required | Description |
|--------|-------|----------|-------------|
| `Content-Type` | `application/json` | Yes | For POST/PUT requests |
| `X-API-KEY` | `your-api-key` | Yes* | Required for Auth, Categories, Posters |
| `Authorization` | `Bearer <jwt_token>` | Conditional | Required for protected endpoints |

*Note: Subscription endpoints use JWT only (no API key required).

## Authentication

BannerBox uses a dual authentication system:

1. **API Key**: Static key for identifying the client application
2. **JWT Token**: Dynamic token for user authentication

### Getting a JWT Token

```http
POST /api/auth/login
```

```json
{
  "email": "user@example.com",
  "password": "yourpassword"
}
```

The response includes a JWT token valid for 30 days:

```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "user_id": 1,
      "email": "user@example.com",
      "subscription_type": "basic"
    }
  }
}
```

## API Reference

| Section | Description | Documentation |
|---------|-------------|---------------|
| Authentication | User login, registration, profile management | [authentication.md](./api/authentication.md) |
| Categories | Category CRUD operations | [categories.md](./api/categories.md) |
| Posters | Poster management and search | [posters.md](./api/posters.md) |
| Subscription | Razorpay payment integration | [subscription.md](./api/subscription.md) |
| Webhooks | External service notifications | [webhooks.md](./api/webhooks.md) |

## Error Handling

All API responses follow a consistent format:

### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description"
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request - Invalid input |
| 401 | Unauthorized - Invalid or missing authentication |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 405 | Method Not Allowed |
| 500 | Internal Server Error |

## Rate Limiting

Currently, there are no rate limits implemented. This may change in future versions.

## Versioning

The current API version is v1. All endpoints are prefixed with `/api/`.

## Support

For API support, contact: support@bannerbox.com
