#!/bin/bash

# MeiliSearch Indexed Data API - CURL Examples
# This script contains various curl examples to test the indexed data endpoint

BASE_URL="http://localhost:8000"
API_ENDPOINT="/api/explorer/indexed-data"

echo "=========================================="
echo "MeiliSearch Indexed Data API - CURL Tests"
echo "=========================================="
echo ""

# Test 1: Get all indexed data (default pagination)
echo "Test 1: Get all indexed data (default pagination)"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}\""
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}" | jq '.' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}"
echo ""
echo ""

# Test 2: Get indexed data with custom per_page
echo "Test 2: Get indexed data with custom per_page (50 items)"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}?per_page=50\""
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}?per_page=50" | jq '.pagination' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}?per_page=50"
echo ""
echo ""

# Test 3: Get indexed data from page 2
echo "Test 3: Get indexed data from page 2"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}?page=2&per_page=20\""
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}?page=2&per_page=20" | jq '.pagination' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}?page=2&per_page=20"
echo ""
echo ""

# Test 4: Filter by professional profiles index
echo "Test 4: Filter by professional profiles index"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}?index=professional_profiles_index\""
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}?index=professional_profiles_index" | jq '.index_stats' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}?index=professional_profiles_index"
echo ""
echo ""

# Test 5: Filter by service offers index
echo "Test 5: Filter by service offers index"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}?index=service_offers_index\""
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}?index=service_offers_index" | jq '.index_stats' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}?index=service_offers_index"
echo ""
echo ""

# Test 6: Filter by achievements index
echo "Test 6: Filter by achievements index"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}?index=achievements_index\""
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}?index=achievements_index" | jq '.index_stats' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}?index=achievements_index"
echo ""
echo ""

# Test 7: Get professional profiles with custom pagination
echo "Test 7: Get professional profiles with custom pagination (5 per page)"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}?index=professional_profiles_index&page=1&per_page=5\""
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}?index=professional_profiles_index&page=1&per_page=5" | jq '.pagination' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}?index=professional_profiles_index&page=1&per_page=5"
echo ""
echo ""

# Test 8: Get service offers with custom pagination
echo "Test 8: Get service offers with custom pagination (10 per page)"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}?index=service_offers_index&page=1&per_page=10\""
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}?index=service_offers_index&page=1&per_page=10" | jq '.pagination' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}?index=service_offers_index&page=1&per_page=10"
echo ""
echo ""

# Test 9: Get achievements with custom pagination
echo "Test 9: Get achievements with custom pagination (10 per page)"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}?index=achievements_index&page=1&per_page=10\""
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}?index=achievements_index&page=1&per_page=10" | jq '.pagination' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}?index=achievements_index&page=1&per_page=10"
echo ""
echo ""

# Test 10: Get performance metrics
echo "Test 10: Get performance metrics"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}\" | jq '.performance'"
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}" | jq '.performance' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}"
echo ""
echo ""

# Test 11: Get index statistics
echo "Test 11: Get index statistics"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}\" | jq '.index_stats'"
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}" | jq '.index_stats' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}"
echo ""
echo ""

# Test 12: Get first item from each index
echo "Test 12: Get first item from each index"
echo "Command:"
echo "curl \"${BASE_URL}${API_ENDPOINT}?per_page=1\" | jq '.data[0]'"
echo ""
echo "Response:"
curl -s "${BASE_URL}${API_ENDPOINT}?per_page=1" | jq '.data[0]' 2>/dev/null || curl -s "${BASE_URL}${API_ENDPOINT}?per_page=1"
echo ""
echo ""

echo "=========================================="
echo "All tests completed!"
echo "=========================================="

