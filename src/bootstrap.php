<?php

$dir = __DIR__;
$root = $dir . '/..';

require $root . '/vendor/autoload.php';
require $root . '/src/functions.php';

\Plasticode\Core\Env::load($root);

$appSettings = \Plasticode\Core\Settings::load($root . '/settings');

$app = \Plasticode\Core\App::get($appSettings);
$container = $app->getContainer();
$settings = $container->get('settings');

if ($settings['debug']) {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set("display_errors", 1);
    ini_set("log_errors_max_len", 0);
}
else {
    $errorLevel = error_reporting();
    error_reporting($errorLevel & ~E_NOTICE);
}

session_start();

$bootstrap = new \App\Config\Bootstrap($settings, $dir);
\Plasticode\Core\Core::bootstrap($container, $bootstrap->getMappings());

// middleware
$app->add(new \Plasticode\Middleware\SlashMiddleware($container));
$app->add(new \Plasticode\Middleware\CookieAuthMiddleware($container, $settings['auth_token_key']));

require $root . '/src/routes.php';

$app->run();
