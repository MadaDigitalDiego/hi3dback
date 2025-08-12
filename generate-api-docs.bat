@echo off
echo ===== Génération de la documentation API =====
php artisan l5-swagger:generate
echo.
echo ===== Documentation générée =====
echo Vous pouvez accéder à la documentation à l'adresse suivante :
echo http://localhost:8000/api/documentation
echo.
pause
