<?php

$root = __DIR__;

require $root . '/vendor/autoload.php';

\Plasticode\Core\Env::load($root);

$db = [
    'adapter' => getenv('DB_ADAPTER'),
    'host' => getenv('DB_HOST'),
    'port' => getenv('DB_PORT'),
    'name' => getenv('DB_DATABASE'),
    'user' => getenv('DB_USER'),
    'pass' => getenv('DB_PASSWORD'),
    'charset' => 'utf8',
];

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => getenv('APP_ENV'),
        'dev' => $db,
        'prod' => $db,
    ],
    'version_order' => 'creation',
];
