# Test simple de l'API
Write-Host "🧪 Test Simple de l'API" -ForegroundColor Cyan

$baseUrl = "http://localhost:8000/api"
$token = "1|VCtSf8jw44NDa8PX1Fpu7z2p9kJDwZBlsJPoh208fe91fc88"
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

# Test 1: Ping
Write-Host "1. Test de ping..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/ping" -Method GET -Headers $headers
    Write-Host "   ✅ Ping réussi: $($response.message)" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Erreur ping: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 2: Liste des fichiers
Write-Host "2. Liste des fichiers..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/files" -Method GET -Headers $headers
    Write-Host "   ✅ Liste récupérée, total: $($response.data.pagination.total)" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Erreur liste: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "Test terminé !" -ForegroundColor Green
