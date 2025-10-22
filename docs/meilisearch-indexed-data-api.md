# MeiliSearch Indexed Data API Documentation

## Overview

The **Indexed Data API** endpoint allows you to retrieve all data currently indexed on MeiliSearch. This is useful for:
- Monitoring what data is indexed
- Debugging search issues
- Auditing indexed content
- Exporting indexed data

## Endpoint

### GET `/api/explorer/indexed-data`

Retrieves all indexed data from MeiliSearch with pagination support.

## Parameters

### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number for pagination |
| `per_page` | integer | 20 | Number of items per page |
| `index` | string | null | Filter by specific index (optional) |

### Supported Index Filters

- `professional_profiles_index` - Professional profiles
- `service_offers_index` - Service offers
- `achievements_index` - Achievements

## Response Format

### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "professional_profile",
      "index": "professional_profiles_index",
      "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "full_name": "John Doe",
        "email": "john@example.com",
        "title": "Senior Developer",
        "profession": "Software Engineer",
        "bio": "Experienced developer",
        "description": "Full stack developer",
        "city": "Paris",
        "country": "France",
        "skills": ["PHP", "Laravel", "React"],
        "languages": ["French", "English"],
        "services_offered": ["Web Development"],
        "expertise": ["Backend", "Frontend"],
        "years_of_experience": 5,
        "hourly_rate": 75.00,
        "availability_status": "available",
        "rating": 4.8,
        "completion_percentage": 95,
        "type": "professional_profile"
      },
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-20T14:45:00Z"
    },
    {
      "id": 2,
      "type": "service_offer",
      "index": "service_offers_index",
      "data": {
        "id": 2,
        "title": "Web Development Service",
        "description": "Professional web development",
        "price": 500,
        "status": "active",
        "is_private": false,
        "categories": ["web", "development"],
        "rating": 4.5,
        "views": 150,
        "likes": 25,
        "user_id": 1,
        "type": "service_offer"
      },
      "created_at": "2024-01-10T08:00:00Z",
      "updated_at": "2024-01-18T16:20:00Z"
    },
    {
      "id": 3,
      "type": "achievement",
      "index": "achievements_index",
      "data": {
        "id": 3,
        "title": "Award Winner",
        "description": "Won best developer award",
        "category": "award",
        "cover_photo": "path/to/photo.jpg",
        "gallery_photos": ["photo1.jpg", "photo2.jpg"],
        "youtube_link": "https://youtube.com/...",
        "status": "published",
        "professional_profile_id": 1,
        "professional_name": "John Doe",
        "type": "achievement"
      },
      "created_at": "2024-01-05T12:00:00Z",
      "updated_at": "2024-01-19T09:15:00Z"
    }
  ],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 1,
    "last_page": 8
  },
  "index_stats": {
    "professional_profiles_index": {
      "count": 50,
      "index_name": "professional_profiles_index"
    },
    "service_offers_index": {
      "count": 75,
      "index_name": "service_offers_index"
    },
    "achievements_index": {
      "count": 25,
      "index_name": "achievements_index"
    }
  },
  "performance": {
    "total_execution_time_ms": 45.32
  }
}
```

### Error Response (400/500)

```json
{
  "success": false,
  "message": "Error description"
}
```

## Usage Examples

### 1. Get All Indexed Data (First Page)

```bash
curl "http://localhost:8000/api/explorer/indexed-data"
```

### 2. Get Specific Page with Custom Per Page

```bash
curl "http://localhost:8000/api/explorer/indexed-data?page=2&per_page=50"
```

### 3. Filter by Professional Profiles Index

```bash
curl "http://localhost:8000/api/explorer/indexed-data?index=professional_profiles_index"
```

### 4. Filter by Service Offers Index

```bash
curl "http://localhost:8000/api/explorer/indexed-data?index=service_offers_index"
```

### 5. Filter by Achievements Index

```bash
curl "http://localhost:8000/api/explorer/indexed-data?index=achievements_index"
```

## Data Filtering Rules

### Professional Profiles
- Only profiles with `completion_percentage >= 80` are indexed
- Includes all searchable fields from the model

### Service Offers
- Only public services (`is_private = false`) are indexed
- Includes real-time view and like counts

### Achievements
- Only achievements with `status != 'draft'` are indexed
- Includes professional profile information

## Performance Notes

- The endpoint returns execution time in milliseconds
- Pagination is applied after collecting all data
- Large datasets may take longer to retrieve
- Consider using the `index` filter to reduce response time

## Integration with Frontend

### React Example

```javascript
const fetchIndexedData = async (page = 1, perPage = 20, index = null) => {
  const params = new URLSearchParams({
    page,
    per_page: perPage,
    ...(index && { index })
  });

  const response = await fetch(
    `http://localhost:8000/api/explorer/indexed-data?${params}`
  );
  const data = await response.json();
  
  if (data.success) {
    console.log('Indexed data:', data.data);
    console.log('Stats:', data.index_stats);
  }
};
```

## Related Endpoints

- `GET /api/explorer/search-stats` - Get search statistics
- `GET /api/explorer/professionals` - Search professionals
- `GET /api/explorer/services` - Search services

