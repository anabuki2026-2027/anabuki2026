<?php
/*
 * 【このファイルは？】
 * app_local.php（自分のパソコン専用の設定）の「見本」。
 * 最初の準備（composer install）のときに、これをもとに app_local.php が自動で作られる。
 *
 * 【担当】A チーム
 * 【くわしく】docs/FILES.md
 */

use function Cake\Core\env;

return [
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    'Security' => [
        'salt' => env('SECURITY_SALT', '__SALT__'),
    ],

    'Datasources' => [
        'default' => [
            'host' => 'localhost',

            'username' => 'my_app',
            'password' => 'secret',

            'database' => 'my_app',

            'url' => env('DATABASE_URL', null),
        ],

        'test' => [
            'host' => 'localhost',
            'username' => 'my_app',
            'password' => 'secret',
            'database' => 'test_myapp',
            'url' => env('DATABASE_TEST_URL', 'sqlite://127.0.0.1/tmp/tests.sqlite'),
        ],
    ],

    'EmailTransport' => [
        'default' => [
            'host' => 'localhost',
            'port' => 25,
            'username' => null,
            'password' => null,
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],
];
