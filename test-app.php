<?php

/**
 * Script de test simple pour vérifier que l'application fonctionne correctement
 */

// Vérifier que PHP est installé et fonctionne
echo "PHP version: " . PHP_VERSION . "\n";

// Vérifier que l'application Laravel est accessible
$basePath = __DIR__;
if (file_exists($basePath . '/artisan')) {
    echo "Laravel application found at: " . $basePath . "\n";
} else {
    echo "Error: Laravel application not found at: " . $basePath . "\n";
    exit(1);
}

// Vérifier que le fichier .env existe
if (file_exists($basePath . '/.env')) {
    echo "Environment file (.env) found\n";
} else {
    echo "Warning: Environment file (.env) not found\n";
}

// Vérifier que les dossiers importants existent
$directories = [
    'app',
    'bootstrap',
    'config',
    'database',
    'public',
    'resources',
    'routes',
    'storage',
    'tests',
    'vendor',
];

foreach ($directories as $directory) {
    if (is_dir($basePath . '/' . $directory)) {
        echo "Directory found: " . $directory . "\n";
    } else {
        echo "Error: Directory not found: " . $directory . "\n";
    }
}

// Vérifier que les fichiers de configuration importants existent
$configFiles = [
    'config/app.php',
    'config/auth.php',
    'config/database.php',
    'config/logging.php',
    'config/security.php',
];

foreach ($configFiles as $configFile) {
    if (file_exists($basePath . '/' . $configFile)) {
        echo "Config file found: " . $configFile . "\n";
    } else {
        echo "Error: Config file not found: " . $configFile . "\n";
    }
}

// Vérifier que les modèles existent
$models = [
    'app/Models/User.php',
    'app/Models/ProfessionalProfile.php',
    'app/Models/ClientProfile.php',
    'app/Models/Experience.php',
    'app/Models/Achievement.php',
    'app/Models/Project.php',
];

foreach ($models as $model) {
    if (file_exists($basePath . '/' . $model)) {
        echo "Model found: " . $model . "\n";
    } else {
        echo "Error: Model not found: " . $model . "\n";
    }
}

// Vérifier que les contrôleurs existent
$controllers = [
    'app/Http/Controllers/Api/ProfileController.php',
    'app/Http/Controllers/Api/UserController.php',
    'app/Http/Controllers/Api/DashboardController.php',
    'app/Http/Controllers/Api/ProjectController.php',
];

foreach ($controllers as $controller) {
    if (file_exists($basePath . '/' . $controller)) {
        echo "Controller found: " . $controller . "\n";
    } else {
        echo "Error: Controller not found: " . $controller . "\n";
    }
}

// Vérifier que les middlewares existent
$middlewares = [
    'app/Http/Middleware/ValidateJsonPayload.php',
    'app/Http/Middleware/IpRateLimiter.php',
    'app/Http/Middleware/CacheResponse.php',
    'app/Http/Middleware/PerformanceMonitor.php',
];

foreach ($middlewares as $middleware) {
    if (file_exists($basePath . '/' . $middleware)) {
        echo "Middleware found: " . $middleware . "\n";
    } else {
        echo "Error: Middleware not found: " . $middleware . "\n";
    }
}

// Vérifier que les services existent
$services = [
    'app/Services/ProfileCacheService.php',
];

foreach ($services as $service) {
    if (file_exists($basePath . '/' . $service)) {
        echo "Service found: " . $service . "\n";
    } else {
        echo "Error: Service not found: " . $service . "\n";
    }
}

// Vérifier que les routes API existent
$routesFile = $basePath . '/routes/api.php';
if (file_exists($routesFile)) {
    echo "API routes file found\n";
    $routesContent = file_get_contents($routesFile);

    // Vérifier quelques routes importantes
    $routes = [
        '/profile',
        '/dashboard',
        '/professionals',
        '/dashboard/projects',
    ];

    foreach ($routes as $route) {
        if (strpos($routesContent, $route) !== false) {
            echo "Route found: " . $route . "\n";
        } else {
            echo "Warning: Route not found in api.php: " . $route . "\n";
        }
    }
} else {
    echo "Error: API routes file not found\n";
}

echo "\nApplication check completed.\n";
