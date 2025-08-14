<?php

/**
 * Script d'analyse des logs de performance
 * 
 * Ce script analyse les logs de performance et génère un rapport
 * sur les requêtes les plus lentes et les plus gourmandes en mémoire.
 */

// Chemin vers le fichier de log de performance
$logFile = __DIR__ . '/storage/logs/performance.log';

// Vérifier si le fichier existe
if (!file_exists($logFile)) {
    echo "Le fichier de log de performance n'existe pas.\n";
    exit(1);
}

// Lire le contenu du fichier
$content = file_get_contents($logFile);

// Extraire les entrées de log
$pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)/';
preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

// Tableau pour stocker les données de performance
$performanceData = [];

// Analyser chaque entrée de log
foreach ($matches as $match) {
    $timestamp = $match[1];
    $level = $match[2];
    $message = $match[4];
    
    // Ignorer les entrées qui ne sont pas des données de performance
    if ($level !== 'debug' || strpos($message, 'Performance') !== 0) {
        continue;
    }
    
    // Extraire les données JSON
    $jsonStart = strpos($message, '{');
    if ($jsonStart === false) {
        continue;
    }
    
    $jsonString = substr($message, $jsonStart);
    $data = json_decode($jsonString, true);
    
    if (!$data) {
        continue;
    }
    
    // Convertir les chaînes de temps d'exécution et d'utilisation de mémoire en nombres
    $executionTime = floatval(str_replace(' ms', '', $data['execution_time']));
    $memoryUsage = floatval(str_replace(' MB', '', $data['memory_usage']));
    
    // Ajouter les données au tableau
    $performanceData[] = [
        'timestamp' => $timestamp,
        'url' => $data['url'],
        'method' => $data['method'],
        'execution_time' => $executionTime,
        'memory_usage' => $memoryUsage,
        'status_code' => $data['status_code'],
        'user_id' => $data['user_id'],
    ];
}

// Trier les données par temps d'exécution (du plus lent au plus rapide)
usort($performanceData, function ($a, $b) {
    return $b['execution_time'] <=> $a['execution_time'];
});

// Générer un rapport sur les requêtes les plus lentes
echo "=== Requêtes les plus lentes ===\n";
echo str_pad('URL', 50) . " | " . str_pad('Méthode', 8) . " | " . str_pad('Temps (ms)', 10) . " | " . str_pad('Mémoire (MB)', 12) . " | " . str_pad('Code', 5) . " | " . "Utilisateur\n";
echo str_repeat('-', 100) . "\n";

$slowestRequests = array_slice($performanceData, 0, 10);
foreach ($slowestRequests as $data) {
    echo str_pad(substr($data['url'], 0, 47) . '...', 50) . " | ";
    echo str_pad($data['method'], 8) . " | ";
    echo str_pad(number_format($data['execution_time'], 2), 10) . " | ";
    echo str_pad(number_format($data['memory_usage'], 2), 12) . " | ";
    echo str_pad($data['status_code'], 5) . " | ";
    echo $data['user_id'] . "\n";
}

echo "\n";

// Trier les données par utilisation de mémoire (du plus gourmand au moins gourmand)
usort($performanceData, function ($a, $b) {
    return $b['memory_usage'] <=> $a['memory_usage'];
});

// Générer un rapport sur les requêtes les plus gourmandes en mémoire
echo "=== Requêtes les plus gourmandes en mémoire ===\n";
echo str_pad('URL', 50) . " | " . str_pad('Méthode', 8) . " | " . str_pad('Temps (ms)', 10) . " | " . str_pad('Mémoire (MB)', 12) . " | " . str_pad('Code', 5) . " | " . "Utilisateur\n";
echo str_repeat('-', 100) . "\n";

$memoryHungryRequests = array_slice($performanceData, 0, 10);
foreach ($memoryHungryRequests as $data) {
    echo str_pad(substr($data['url'], 0, 47) . '...', 50) . " | ";
    echo str_pad($data['method'], 8) . " | ";
    echo str_pad(number_format($data['execution_time'], 2), 10) . " | ";
    echo str_pad(number_format($data['memory_usage'], 2), 12) . " | ";
    echo str_pad($data['status_code'], 5) . " | ";
    echo $data['user_id'] . "\n";
}

echo "\n";

// Calculer des statistiques globales
$totalRequests = count($performanceData);
$totalExecutionTime = array_sum(array_column($performanceData, 'execution_time'));
$totalMemoryUsage = array_sum(array_column($performanceData, 'memory_usage'));
$averageExecutionTime = $totalRequests > 0 ? $totalExecutionTime / $totalRequests : 0;
$averageMemoryUsage = $totalRequests > 0 ? $totalMemoryUsage / $totalRequests : 0;

// Générer un rapport de statistiques globales
echo "=== Statistiques globales ===\n";
echo "Nombre total de requêtes: " . $totalRequests . "\n";
echo "Temps d'exécution total: " . number_format($totalExecutionTime, 2) . " ms\n";
echo "Utilisation totale de mémoire: " . number_format($totalMemoryUsage, 2) . " MB\n";
echo "Temps d'exécution moyen: " . number_format($averageExecutionTime, 2) . " ms\n";
echo "Utilisation moyenne de mémoire: " . number_format($averageMemoryUsage, 2) . " MB\n";

// Créer un script batch pour exécuter l'analyse
$batchScript = <<<EOT
@echo off
echo ===== Analyse des performances =====
php analyze-performance.php > performance-report.txt
echo Rapport généré dans performance-report.txt
echo.
echo ===== Fin de l'analyse =====
pause
EOT;

file_put_contents(__DIR__ . '/analyze-performance.bat', $batchScript);
