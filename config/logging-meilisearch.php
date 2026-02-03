<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;

return [
    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application.
    |
    */

    'channels' => [
        // Canal dédié pour l'indexation Meilisearch
        'meilisearch' => [
            'driver' => 'daily',
            'path' => storage_path('logs/meilisearch.log'),
            'level' => 'info',
            'days' => 30,
        ],

        // Canal pour les jobs d'indexation
        'indexation' => [
            'driver' => 'daily',
            'path' => storage_path('logs/indexation.log'),
            'level' => 'info',
            'days' => 14,
        ],

        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 30,
        ],
    ],
];

