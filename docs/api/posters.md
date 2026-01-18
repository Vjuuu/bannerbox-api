# Posters API

Poster management and browsing endpoints.

## Overview

The Posters API allows you to create, read, update, delete, and search posters. All endpoints require the `X-API-KEY` header. Write operations (create, update, delete) additionally require a JWT token with admin privileges.

## Base URL

```
/api/posters
```

## Endpoints

---

### Get All Posters

Retrieve a paginated list of posters.

```http
GET /api/posters
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `limit` | integer | No | 10 | Items per page (max: 100) |
| `search` | string | No | - | Search term for title/description/tags |

#### Success Response (200)

```json
{
  "success": true,
  "data": [
    {
      "poster_id": 1,
      "title": "Business Meeting Poster",
      "description": "Professional poster for business meetings",
      "image_url": "https://example.com/poster1.jpg",
      "thumbnail_url": "https://example.com/poster1_thumb.jpg",
      "category_id": 1,
      "category_name": "Business",
      "tags": "business,meeting,corporate",
      "is_premium": 1,
      "is_featured": 0,
      "view_count": 150,
      "download_count": 45,
      "created_at": "2025-01-15 10:00:00"
    }
  ],
  "pagination": {
    "total": 250,
    "page": 1,
    "limit": 10,
    "pages": 25
  }
}
```

---

### Get Single Poster

Retrieve a single poster by ID.

```http
GET /api/posters/{id}
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Poster ID |

#### Success Response (200)

```json
{
  "success": true,
  "data": {
    "poster_id": 1,
    "title": "Business Meeting Poster",
    "description": "Professional poster for business meetings and corporate events",
    "image_url": "https://example.com/poster1.jpg",
    "thumbnail_url": "https://example.com/poster1_thumb.jpg",
    "category": {
      "category_id": 1,
      "name": "Business",
      "color": "#3B82F6"
    },
    "tags": ["business", "meeting", "corporate"],
    "is_premium": 1,
    "is_featured": 0,
    "view_count": 150,
    "download_count": 45,
    "created_at": "2025-01-15 10:00:00",
    "updated_at": "2025-06-20 14:30:00"
  }
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 404 | Poster not found |

---

### Create Poster

Create a new poster.

```http
POST /api/posters/create
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
| `title` | string | Yes | Poster title |
| `description` | string | No | Poster description |
| `image_url` | string | Yes | Full URL to poster image |
| `thumbnail_url` | string | No | URL to thumbnail image |
| `category_id` | integer | Yes | Category ID |
| `tags` | string | No | Comma-separated tags |
| `is_premium` | integer | No | 0 = free, 1 = premium (default: 0) |
| `is_featured` | integer | No | 0 = normal, 1 = featured (default: 0) |

#### Example Request

```json
{
  "title": "Summer Sale Banner",
  "description": "Vibrant summer sale promotional banner",
  "image_url": "https://example.com/summer-sale.jpg",
  "thumbnail_url": "https://example.com/summer-sale-thumb.jpg",
  "category_id": 3,
  "tags": "summer,sale,promotion,discount",
  "is_premium": 0,
  "is_featured": 1
}
```

#### Success Response (201)

```json
{
  "success": true,
  "message": "Poster created successfully",
  "data": {
    "poster_id": 156
  }
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 400 | Title and image_url are required |
| 400 | Category not found |
| 401 | Authorization header missing |

---

### Update Poster

Update an existing poster.

```http
PUT /api/posters/update/{id}
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
| `id` | integer | Yes | Poster ID to update |

#### Request Body

All fields are optional. Only include fields you want to update.

| Field | Type | Description |
|-------|------|-------------|
| `title` | string | Poster title |
| `description` | string | Poster description |
| `image_url` | string | Full URL to poster image |
| `thumbnail_url` | string | URL to thumbnail image |
| `category_id` | integer | Category ID |
| `tags` | string | Comma-separated tags |
| `is_premium` | integer | 0 = free, 1 = premium |
| `is_featured` | integer | 0 = normal, 1 = featured |
| `is_active` | integer | 0 = hidden, 1 = visible |

#### Example Request

```json
{
  "title": "Updated Summer Sale Banner",
  "tags": "summer,sale,promotion,discount,hot",
  "is_featured": 0
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Poster updated successfully"
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 400 | No data provided for update |
| 404 | Poster not found |

---

### Delete Poster

Delete a poster.

```http
DELETE /api/posters/delete/{id}
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |
| `Authorization` | `Bearer <jwt_token>` | Yes |

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Poster ID to delete |

#### Success Response (200)

```json
{
  "success": true,
  "message": "Poster deleted successfully"
}
```

#### Error Responses

| Code | Message |
|------|---------|
| 404 | Poster not found |

---

### Get Trending Posters

Get trending posters based on views and downloads.

```http
GET /api/posters/trending
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 10 | Number of posters to return |
| `days` | integer | No | 7 | Number of days to consider for trending calculation |

#### Success Response (200)

```json
{
  "success": true,
  "data": [
    {
      "poster_id": 42,
      "title": "Diwali Festival Poster",
      "image_url": "https://example.com/diwali.jpg",
      "category_name": "Festivals",
      "view_count": 1250,
      "download_count": 340,
      "is_premium": 0
    }
  ]
}
```

---

### Get Posters by Category

Get posters filtered by category.

```http
GET /api/posters/category/{category_id}
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `category_id` | integer | Yes | Category ID to filter by |

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `limit` | integer | No | 10 | Items per page |

#### Success Response (200)

```json
{
  "success": true,
  "data": [
    {
      "poster_id": 1,
      "title": "Business Meeting Poster",
      "image_url": "https://example.com/poster1.jpg",
      "is_premium": 1
    }
  ],
  "category": {
    "category_id": 1,
    "name": "Business"
  },
  "pagination": {
    "total": 45,
    "page": 1,
    "limit": 10,
    "pages": 5
  }
}
```

---

### Search Posters

Search posters by title, description, or tags.

```http
GET /api/posters?search={query}
```

#### Headers

| Header | Value | Required |
|--------|-------|----------|
| `X-API-KEY` | `your-api-key` | Yes |

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | Yes | Search term |
| `page` | integer | No | Page number (default: 1) |
| `limit` | integer | No | Items per page (default: 10) |

#### Example

```
GET /api/posters?search=diwali&page=1&limit=20
```

#### Success Response (200)

```json
{
  "success": true,
  "data": [
    {
      "poster_id": 42,
      "title": "Diwali Festival Poster",
      "description": "Beautiful Diwali celebration poster with diyas",
      "image_url": "https://example.com/diwali.jpg",
      "tags": "diwali,festival,celebration,lights"
    }
  ],
  "search_term": "diwali",
  "pagination": {
    "total": 15,
    "page": 1,
    "limit": 20,
    "pages": 1
  }
}
```

---

## Poster Object

### Fields

| Field | Type | Description |
|-------|------|-------------|
| `poster_id` | integer | Unique identifier |
| `title` | string | Poster title |
| `description` | string | Poster description |
| `image_url` | string | Full-size image URL |
| `thumbnail_url` | string | Thumbnail image URL |
| `category_id` | integer | Associated category ID |
| `category_name` | string | Category name |
| `tags` | string/array | Associated tags |
| `is_premium` | integer | 1 = premium content, 0 = free |
| `is_featured` | integer | 1 = featured, 0 = normal |
| `is_active` | integer | 1 = visible, 0 = hidden |
| `view_count` | integer | Number of views |
| `download_count` | integer | Number of downloads |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Last update timestamp |

---

## Premium Content

### Accessing Premium Posters

- Premium posters (`is_premium: 1`) require an active subscription
- Users without a subscription can view poster metadata but may have restricted access to full-resolution downloads
- Check user's `subscription_type` to determine access level

### Subscription Types

| Type | Access |
|------|--------|
| `basic` | Free posters only |
| `premium` | All posters including premium |

---

## Usage Examples

### JavaScript (React)

```javascript
// Fetch posters with pagination
const fetchPosters = async (page = 1, limit = 10) => {
  const response = await fetch(
    `${API_URL}/api/posters?page=${page}&limit=${limit}`,
    {
      headers: {
        'X-API-KEY': API_KEY
      }
    }
  );
  return response.json();
};

// Search posters
const searchPosters = async (query) => {
  const response = await fetch(
    `${API_URL}/api/posters?search=${encodeURIComponent(query)}`,
    {
      headers: {
        'X-API-KEY': API_KEY
      }
    }
  );
  return response.json();
};

// Get posters by category
const getPostersByCategory = async (categoryId, page = 1) => {
  const response = await fetch(
    `${API_URL}/api/posters/category/${categoryId}?page=${page}`,
    {
      headers: {
        'X-API-KEY': API_KEY
      }
    }
  );
  return response.json();
};
```
