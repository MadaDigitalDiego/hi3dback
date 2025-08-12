@echo off
echo ===== Exécution des tests de profil =====
php artisan test --filter="ProfileApiTest|ProfessionalProfileTest|ClientProfileTest"
echo.
echo ===== Fin des tests =====
pause
