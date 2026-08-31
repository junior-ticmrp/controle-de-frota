<?php

use App\Ldap\Rules\AuthorizedFuelUser;
use App\Ldap\UserAttributeHandler;
use App\Models\User;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'ldap'),
        'passwords' => 'local-users',
    ],

    'guards' => [
        'ldap' => [
            'driver' => 'session',
            'provider' => 'ldap-users',
        ],
        'local' => [
            'driver' => 'session',
            'provider' => 'local-users',
        ],
    ],

    'providers' => [
        'ldap-users' => [
            'driver' => 'ldap',
            'model' => LdapUser::class,
            'rules' => [
                AuthorizedFuelUser::class,
            ],
            'scopes' => [],
            'database' => [
                'model' => User::class,
                'sync_passwords' => false,
                'sync_attributes' => [
                    UserAttributeHandler::class,
                ],
                'password_column' => 'password',
            ],
        ],
        'local-users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],
    ],

    'passwords' => [
        'local-users' => [
            'provider' => 'local-users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
