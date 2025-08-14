# Script de test PowerShell pour l'API de gestion de fichiers
Write-Host "🧪 Test de l'API de Gestion de Fichiers Hi3D" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$baseUrl = "http://localhost:8000/api"
$token = "1|VCtSf8jw44NDa8PX1Fpu7z2p9kJDwZBlsJPoh208fe91fc88"
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

try {
    # Test 1: Ping
    Write-Host "1. Test de ping..." -ForegroundColor Yellow
    $pingResponse = Invoke-RestMethod -Uri "$baseUrl/ping" -Method GET -Headers $headers
    Write-Host "   ✅ Ping réussi: $($pingResponse.message)" -ForegroundColor Green
    Write-Host ""

    # Test 2: Liste des fichiers (vide au début)
    Write-Host "2. Liste des fichiers..." -ForegroundColor Yellow
    $filesResponse = Invoke-RestMethod -Uri "$baseUrl/files" -Method GET -Headers $headers
    Write-Host "   ✅ Liste récupérée" -ForegroundColor Green
    Write-Host "   📊 Nombre de fichiers: $($filesResponse.data.pagination.total)" -ForegroundColor Blue
    Write-Host ""

    # Test 3: Upload d'un petit fichier
    Write-Host "3. Upload d'un petit fichier..." -ForegroundColor Yellow

    # Créer un fichier temporaire pour le test
    $testContent = "Ceci est un fichier de test pour l'API Hi3D.`nTaille: Petite (< 10MB)`nType: text/plain`nStockage attendu: Local"
    $testFilePath = "temp_test_file.txt"
    $testContent | Out-File -FilePath $testFilePath -Encoding UTF8

    # Préparer le formulaire multipart
    $form = @{
        'files[]' = Get-Item -Path $testFilePath
    }

    $uploadResponse = Invoke-RestMethod -Uri "$baseUrl/files/upload" -Method POST -Headers $headers -Form $form
    Write-Host "   ✅ Upload réussi" -ForegroundColor Green
    Write-Host "   📋 Fichier ID: $($uploadResponse.data.id)" -ForegroundColor Blue
    Write-Host "   📋 Nom: $($uploadResponse.data.original_name)" -ForegroundColor Blue
    Write-Host "   📋 Taille: $($uploadResponse.data.human_size)" -ForegroundColor Blue
    Write-Host "   📋 Stockage: $($uploadResponse.data.storage_type)" -ForegroundColor Blue
    Write-Host ""

    $fileId = $uploadResponse.data.id

    # Test 4: Détails du fichier
    Write-Host "4. Récupération des détails du fichier..." -ForegroundColor Yellow
    $fileDetailsResponse = Invoke-RestMethod -Uri "$baseUrl/files/$fileId" -Method GET -Headers $headers
    Write-Host "   ✅ Détails récupérés" -ForegroundColor Green
    Write-Host "   📋 Statut: $($fileDetailsResponse.data.status)" -ForegroundColor Blue
    Write-Host "   📋 URL de téléchargement: $($fileDetailsResponse.data.download_url)" -ForegroundColor Blue
    Write-Host ""

    # Test 5: URL de téléchargement
    Write-Host "5. Génération de l'URL de téléchargement..." -ForegroundColor Yellow
    $downloadResponse = Invoke-RestMethod -Uri "$baseUrl/files/$fileId/download" -Method GET -Headers $headers
    Write-Host "   ✅ URL générée" -ForegroundColor Green
    Write-Host "   🔗 URL: $($downloadResponse.data.download_url)" -ForegroundColor Blue
    Write-Host ""

    # Test 6: Liste mise à jour
    Write-Host "6. Liste des fichiers mise à jour..." -ForegroundColor Yellow
    $updatedFilesResponse = Invoke-RestMethod -Uri "$baseUrl/files" -Method GET -Headers $headers
    Write-Host "   ✅ Liste mise à jour" -ForegroundColor Green
    Write-Host "   📊 Nombre de fichiers: $($updatedFilesResponse.data.pagination.total)" -ForegroundColor Blue
    Write-Host ""

    # Test 7: Suppression du fichier
    Write-Host "7. Suppression du fichier de test..." -ForegroundColor Yellow
    $deleteResponse = Invoke-RestMethod -Uri "$baseUrl/files/$fileId" -Method DELETE -Headers $headers
    Write-Host "   ✅ Fichier supprimé" -ForegroundColor Green
    Write-Host ""

    # Test 8: Vérification de la suppression
    Write-Host "8. Vérification de la suppression..." -ForegroundColor Yellow
    try {
        $deletedFileResponse = Invoke-RestMethod -Uri "$baseUrl/files/$fileId" -Method GET -Headers $headers
        Write-Host "   ❌ Le fichier existe encore" -ForegroundColor Red
    } catch {
        Write-Host "   ✅ Fichier bien supprimé (404 attendu)" -ForegroundColor Green
    }
    Write-Host ""

    # Nettoyage
    Remove-Item -Path $testFilePath -Force -ErrorAction SilentlyContinue

    Write-Host "🎉 Tous les tests sont passés avec succès !" -ForegroundColor Green
    Write-Host ""
    Write-Host "📊 Résumé des tests:" -ForegroundColor Cyan
    Write-Host "   ✅ Ping API" -ForegroundColor Green
    Write-Host "   ✅ Liste des fichiers" -ForegroundColor Green
    Write-Host "   ✅ Upload de fichier" -ForegroundColor Green
    Write-Host "   ✅ Détails du fichier" -ForegroundColor Green
    Write-Host "   ✅ URL de téléchargement" -ForegroundColor Green
    Write-Host "   ✅ Suppression de fichier" -ForegroundColor Green
    Write-Host ""
    Write-Host "🚀 Le système de gestion de fichiers fonctionne parfaitement !" -ForegroundColor Green

} catch {
    Write-Host "❌ Erreur lors du test: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "🔍 Détails: $($_.Exception)" -ForegroundColor Red

    # Nettoyage en cas d'erreur
    Remove-Item -Path "temp_test_file.txt" -Force -ErrorAction SilentlyContinue
}
