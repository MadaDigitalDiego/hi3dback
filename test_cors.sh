#!/bin/bash

echo "=== Test de configuration CORS ==="
echo ""

# URL de base (modifiez selon votre environnement)
BASE_URL="http://localhost:8001"
ORIGIN="https://dev-backend.hi-3d.com"

echo "1. Test de la requête OPTIONS (preflight) :"
echo "curl -X OPTIONS $BASE_URL/api/register -H \"Origin: $ORIGIN\" -H \"Access-Control-Request-Method: POST\" -H \"Access-Control-Request-Headers: Content-Type, X-Requested-With, Authorization\" -v"
echo ""

curl -X OPTIONS "$BASE_URL/api/register" \
  -H "Origin: $ORIGIN" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type, X-Requested-With, Authorization" \
  -v 2>&1 | grep -E "(Access-Control|HTTP/|Origin)"

echo ""
echo "2. Test de la requête POST avec CORS :"
echo ""

curl -X POST "$BASE_URL/api/ping" \
  -H "Origin: $ORIGIN" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -v 2>&1 | grep -E "(Access-Control|HTTP/|Origin)"

echo ""
echo "3. Test avec une origine non autorisée :"
echo ""

curl -X OPTIONS "$BASE_URL/api/register" \
  -H "Origin: https://malicious-site.com" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -v 2>&1 | grep -E "(Access-Control|HTTP/|Origin)"

echo ""
echo "=== Fin des tests ==="
