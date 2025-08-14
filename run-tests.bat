@echo off
echo ===== Exécution des tests avec rapport de couverture =====
php artisan test --coverage --min=80
echo.
echo ===== Fin des tests =====
pause
