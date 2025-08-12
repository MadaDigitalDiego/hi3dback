<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Liste blanche d'adresses IP
    |--------------------------------------------------------------------------
    |
    | Cette liste contient les adresses IP qui sont exemptées des limitations
    | de taux et d'autres restrictions de sécurité.
    |
    */
    'ip_whitelist' => [
        // Exemples d'adresses IP à mettre en liste blanche
        // '192.168.1.1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Liste noire d'adresses IP
    |--------------------------------------------------------------------------
    |
    | Cette liste contient les adresses IP qui sont bloquées et ne peuvent pas
    | accéder à l'API.
    |
    */
    'ip_blacklist' => [
        // Exemples d'adresses IP à bloquer
        // '10.0.0.1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres de limitation de taux
    |--------------------------------------------------------------------------
    |
    | Ces paramètres définissent les limites de taux pour différentes routes
    | de l'API.
    |
    */
    'rate_limits' => [
        'api' => [
            'attempts' => 60,
            'decay_minutes' => 1,
        ],
        'auth' => [
            'attempts' => 5,
            'decay_minutes' => 1,
        ],
        'profile' => [
            'attempts' => 30,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres de sécurité des mots de passe
    |--------------------------------------------------------------------------
    |
    | Ces paramètres définissent les exigences de sécurité pour les mots de passe.
    |
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numeric' => true,
        'require_special_char' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres de sécurité des sessions
    |--------------------------------------------------------------------------
    |
    | Ces paramètres définissent la sécurité des sessions.
    |
    */
    'session' => [
        'lifetime_minutes' => 120,
        'expire_on_close' => true,
        'same_site' => 'lax',
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres de sécurité des tokens API
    |--------------------------------------------------------------------------
    |
    | Ces paramètres définissent la sécurité des tokens API.
    |
    */
    'api_tokens' => [
        'lifetime_days' => 30,
        'refresh_token_lifetime_days' => 60,
    ],
];
