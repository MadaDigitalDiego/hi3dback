<?php

/**
 * Test Script for MeiliSearch Indexed Data API
 * 
 * This script tests the new /api/explorer/indexed-data endpoint
 * to verify that all indexed data can be retrieved correctly.
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MeiliSearch Indexed Data API Test ===\n\n";

// Test configuration
$baseUrl = 'http://localhost:8000/api/explorer/indexed-data';
$tests = [
    [
        'name' => 'Get All Indexed Data (Default)',
        'url' => $baseUrl,
        'description' => 'Retrieve all indexed data with default pagination'
    ],
    [
        'name' => 'Get All Indexed Data - Page 2',
        'url' => $baseUrl . '?page=2&per_page=20',
        'description' => 'Retrieve indexed data from page 2'
    ],
    [
        'name' => 'Get Professional Profiles Index',
        'url' => $baseUrl . '?index=professional_profiles_index',
        'description' => 'Retrieve only professional profiles'
    ],
    [
        'name' => 'Get Service Offers Index',
        'url' => $baseUrl . '?index=service_offers_index',
        'description' => 'Retrieve only service offers'
    ],
    [
        'name' => 'Get Achievements Index',
        'url' => $baseUrl . '?index=achievements_index',
        'description' => 'Retrieve only achievements'
    ],
    [
        'name' => 'Get Professional Profiles - Custom Pagination',
        'url' => $baseUrl . '?index=professional_profiles_index&page=1&per_page=5',
        'description' => 'Retrieve professional profiles with custom pagination'
    ],
];

function testAPI($url, $description) {
    echo "Testing: {$description}\n";
    echo "URL: {$url}\n";
    
    try {
        $response = @file_get_contents($url);
        
        if ($response === false) {
            echo "❌ Failed to connect to API\n";
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!$data) {
            echo "❌ Invalid JSON response\n";
            return false;
        }
        
        if (!isset($data['success'])) {
            echo "❌ Missing 'success' field in response\n";
            return false;
        }
        
        if ($data['success']) {
            echo "✅ Success\n";
            
            // Display statistics
            if (isset($data['pagination'])) {
                echo "   Pagination: Page {$data['pagination']['current_page']}/{$data['pagination']['last_page']}, Total: {$data['pagination']['total']}\n";
            }
            
            if (isset($data['index_stats'])) {
                echo "   Index Stats:\n";
                foreach ($data['index_stats'] as $indexName => $stats) {
                    echo "      - {$indexName}: {$stats['count']} items\n";
                }
            }
            
            if (isset($data['data'])) {
                echo "   Items returned: " . count($data['data']) . "\n";
                
                // Show first item as example
                if (count($data['data']) > 0) {
                    $firstItem = $data['data'][0];
                    echo "   First item type: {$firstItem['type']}\n";
                    echo "   First item index: {$firstItem['index']}\n";
                }
            }
            
            if (isset($data['performance'])) {
                echo "   Execution time: {$data['performance']['total_execution_time_ms']}ms\n";
            }
            
            return true;
        } else {
            echo "❌ API returned error: {$data['message']}\n";
            return false;
        }
        
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run tests
$successCount = 0;
$totalTests = count($tests);

foreach ($tests as $test) {
    echo "\n" . str_repeat("-", 60) . "\n";
    if (testAPI($test['url'], $test['description'])) {
        $successCount++;
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Test Results: {$successCount}/{$totalTests} tests passed\n";
echo str_repeat("=", 60) . "\n";

if ($successCount === $totalTests) {
    echo "✅ All tests passed!\n";
    exit(0);
} else {
    echo "❌ Some tests failed\n";
    exit(1);
}

