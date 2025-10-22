# Quick Test Guide - MeiliSearch Indexed Data API

## Prerequisites

1. Ensure Laravel application is running:
   ```bash
   php artisan serve
   ```

2. Ensure MeiliSearch is running and configured in `.env`:
   ```
   SCOUT_DRIVER=meilisearch
   MEILISEARCH_HOST=http://127.0.0.1:7700
   MEILISEARCH_KEY=your_key
   ```

3. Ensure data is indexed:
   ```bash
   php artisan scout:import "App\Models\ProfessionalProfile"
   php artisan scout:import "App\Models\ServiceOffer"
   php artisan scout:import "App\Models\Achievement"
   ```

## Quick Tests

### Test 1: Get All Indexed Data
```bash
curl "http://localhost:8000/api/explorer/indexed-data"
```

**Expected Response:**
- Status: 200
- `success`: true
- `data`: Array of indexed items
- `pagination`: Pagination info
- `index_stats`: Statistics for each index

### Test 2: Filter by Professional Profiles
```bash
curl "http://localhost:8000/api/explorer/indexed-data?index=professional_profiles_index"
```

**Expected Response:**
- All items should have `type: "professional_profile"`
- All items should have `index: "professional_profiles_index"`

### Test 3: Filter by Service Offers
```bash
curl "http://localhost:8000/api/explorer/indexed-data?index=service_offers_index"
```

**Expected Response:**
- All items should have `type: "service_offer"`
- All items should have `index: "service_offers_index"`

### Test 4: Filter by Achievements
```bash
curl "http://localhost:8000/api/explorer/indexed-data?index=achievements_index"
```

**Expected Response:**
- All items should have `type: "achievement"`
- All items should have `index: "achievements_index"`

### Test 5: Test Pagination
```bash
curl "http://localhost:8000/api/explorer/indexed-data?page=1&per_page=5"
```

**Expected Response:**
- `pagination.per_page`: 5
- `pagination.current_page`: 1
- `data`: Array with max 5 items

### Test 6: Test Custom Per Page
```bash
curl "http://localhost:8000/api/explorer/indexed-data?per_page=50"
```

**Expected Response:**
- `pagination.per_page`: 50
- `data`: Array with max 50 items

## Using Postman

1. Import the collection:
   - File: `hi3dback/docs/postman-indexed-data-collection.json`

2. Set the environment variable:
   - `base_url`: `http://localhost:8000`

3. Run the requests in the collection

## Using the Standalone Test Script

```bash
cd hi3dback
php test_indexed_data_api.php
```

This will run all tests and display results.

## Expected Data Structure

### Professional Profile Item
```json
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
    "skills": ["PHP", "Laravel"],
    "rating": 4.8,
    "completion_percentage": 95,
    "type": "professional_profile"
  },
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-20T14:45:00Z"
}
```

### Service Offer Item
```json
{
  "id": 2,
  "type": "service_offer",
  "index": "service_offers_index",
  "data": {
    "id": 2,
    "title": "Web Development",
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
}
```

### Achievement Item
```json
{
  "id": 3,
  "type": "achievement",
  "index": "achievements_index",
  "data": {
    "id": 3,
    "title": "Award Winner",
    "description": "Won best developer award",
    "category": "award",
    "professional_profile_id": 1,
    "professional_name": "John Doe",
    "type": "achievement"
  },
  "created_at": "2024-01-05T12:00:00Z",
  "updated_at": "2024-01-19T09:15:00Z"
}
```

## Troubleshooting

### Error: "MeiliSearch is not configured as the search driver"
- Check `.env` file
- Ensure `SCOUT_DRIVER=meilisearch`
- Restart the application

### No data returned
- Check if data is indexed:
  ```bash
  php artisan scout:import "App\Models\ProfessionalProfile"
  php artisan scout:import "App\Models\ServiceOffer"
  php artisan scout:import "App\Models\Achievement"
  ```

### Slow response times
- Use the `index` filter to reduce data retrieval
- Reduce `per_page` parameter
- Check MeiliSearch performance

## Performance Metrics

The response includes `performance.total_execution_time_ms` which shows:
- Time to retrieve all indexed data
- Time to apply pagination
- Total response generation time

Typical response times:
- Small dataset (< 100 items): 10-50ms
- Medium dataset (100-1000 items): 50-200ms
- Large dataset (> 1000 items): 200-500ms+

## Next Steps

1. ✅ Verify endpoint is working
2. ✅ Test with different filters
3. ✅ Test pagination
4. ✅ Monitor performance
5. Integrate with frontend components

