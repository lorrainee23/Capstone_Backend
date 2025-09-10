<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'admins',
    ],

    'api' => [
        'driver' => 'sanctum',
        'provider' => null, 
    ],

    'admin' => [
        'driver' => 'sanctum',
        'provider' => 'admins',
    ],

    'head' => [
        'driver' => 'sanctum',
        'provider' => 'heads',
    ],

    'deputy' => [
        'driver' => 'sanctum',
        'provider' => 'deputies',
    ],

    'enforcer' => [
        'driver' => 'sanctum',
        'provider' => 'enforcers',
    ],

    'violator' => [
        'driver' => 'sanctum',
        'provider' => 'violators',
    ],
],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
        'deputies' => [
            'driver' => 'eloquent',
            'model' => App\Models\Deputy::class,
        ],
        'heads' => [
            'driver' => 'eloquent',
            'model' => App\Models\Head::class,
        ],
        'enforcers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Enforcer::class,
        ],
        'violators' => [
            'driver' => 'eloquent',
            'model' => App\Models\Violator::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
