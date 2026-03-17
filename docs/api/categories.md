# Categories API

Category management endpoints for organizing posters.

## Overview

The Categories API allows you to create, read, update, and delete categories that are used to organize posters. All endpoints require the `X-API-KEY` header. Write operations (create, update, delete) additionally require a JWT token with admin privileges.

## Base URL

```
/api/categories
```

## Endpoints

---

### Get All Categories

Retrieve a list of all categories.

```http
GET /api/categories
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "data": [
    {
      "category_id": 1,
      "name": "Business",
      "description": "Professional business posters and templates",
      "icon": "briefcase",
      "color": "#3B82F6",
      "poster_count": 45,
      "created_at": "2025-01-01 10:00:00"
    },
    {
      "category_id": 2,
      "name": "Festivals",
      "description": "Festival and celebration posters",
      "icon": "celebration",
      "color": "#F59E0B",
      "poster_count": 120,
      "created_at": "2025-01-01 10:00:00"
    }
  ]
}
```

---

### Get Single Category

Retrieve a single category by ID.

```http
GET /api/categories/{id}
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Category ID |

#### Success Response (200)

```json
{
  "success": true,
  "data": {
    "category_id": 1,
    "name": "Business",
    "description": "Professional business posters and templates",
    "icon": "briefcase",
    "color": "#3B82F6",
    "poster_count": 45,
    "is_active": 1,
    "created_at": "2025-01-01 10:00:00",
    "updated_at": "2025-06-15 14:30:00"
  }
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 404 | Category not found |

---

### Create Category

Create a new category.

```http
POST /api/categories/create
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
| `name` | string | Yes | Category name (unique) |
| `description` | string | No | Category description |
| `icon` | string | No | Icon identifier (e.g., Material Icons name) |
| `color` | string | No | Hex color code (e.g., #FF5733) |

#### Example Request

```json
{
  "name": "Technology",
  "description": "Tech and IT related posters",
  "icon": "computer",
  "color": "#10B981"
}
```

#### Success Response (201)

```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "category_id": 10
  }
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 400 | Category name is required |
| 400 | Category name already exists |
| 401 | Authorization header missing |
| 401 | Invalid or expired token |

---

### Update Category

Update an existing category.

```http
PUT /api/categories/update/{id}
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |
| `Authorization` | `Bearer <jwt_token>` | Yes |
| `Content-Type` | `application/json` | Yes |

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Category ID to update |

#### Request Body

All fields are optional. Only include fields you want to update.

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Category name |
| `description` | string | Category description |
| `icon` | string | Icon identifier |
| `color` | string | Hex color code |
| `is_active` | integer | 0 = inactive, 1 = active |

#### Example Request

```json
{
  "name": "Technology & IT",
  "description": "Updated description for tech posters",
  "color": "#059669"
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Category updated successfully"
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 400 | No data provided for update |
| 400 | Category name already exists |
| 404 | Category not found |

---

### Delete Category

Delete a category.

```http
DELETE /api/categories/delete/{id}
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |
| `Authorization` | `Bearer <jwt_token>` | Yes |

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Category ID to delete |

#### Success Response (200)

```json
{
  "success": true,
  "message": "Category deleted successfully"
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 400 | Cannot delete category with associated posters |
| 404 | Category not found |

---

## Category Object

### Fields

| Field | Type | Description |
|-------|------|-------------|
| `category_id` | integer | Unique identifier |
| `name` | string | Category name |
| `description` | string | Category description |
| `icon` | string | Icon identifier for UI |
| `color` | string | Hex color code for UI |
| `poster_count` | integer | Number of posters in category |
| `is_active` | integer | 1 = active, 0 = inactive |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Last update timestamp |

---

## Usage Examples

### cURL

```bash
# Get all categories
curl -X GET "http://bannerbox.test/api/categories" \
  -H "X-API-KEY: your-api-key"

# Create a category
curl -X POST "http://bannerbox.test/api/categories/create" \
  -H "X-API-KEY: your-api-key" \
  -H "Authorization: Bearer your-jwt-token" \
  -H "Content-Type: application/json" \
  -d '{"name": "New Category", "color": "#FF5733"}'
```

### JavaScript (Fetch)

```javascript
// Get all categories
const response = await fetch('http://bannerbox.test/api/categories', {
  headers: {
    'X-API-KEY': 'your-api-key'
  }
});
const data = await response.json();

// Create a category
const response = await fetch('http://bannerbox.test/api/categories/create', {
  method: 'POST',
  headers: {
    'X-API-KEY': 'your-api-key',
    'Authorization': `Bearer ${jwtToken}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'New Category',
    description: 'Description here',
    color: '#FF5733'
  })
});
```
