<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    
    'indexation' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'indexation',
        'retry_after' => 90,
        'block_for' => null,
        'after_commit' => false,
    ],
];

