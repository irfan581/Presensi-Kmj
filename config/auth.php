<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        // Guard untuk Admin (Filament)
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Guard untuk Sales (Aplikasi Mobile/API)
        'sales-api' => [
            'driver' => 'sanctum',
            'provider' => 'sales',
        ],
    ],

    'providers' => [
        // Provider untuk Admin
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // Provider untuk Sales (Tabel sales dari SQL kamu)
        'sales' => [
            'driver' => 'eloquent',
            'model' => App\Models\Sales::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];