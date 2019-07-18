<?php

$debug = false;

// exclude notice errors by default
$errorLevel = error_reporting();
error_reporting($errorLevel & ~E_NOTICE);

$root = __DIR__ . '/..';

require $root . '/src/functions.php';

if (isset($_GET['debug'])) {
    debugModeOn();
}

require $root . '/vendor/autoload.php';

\Plasticode\Core\Env::load($root);

session_start();

$path = $root . '/settings';
$appSettings = \Plasticode\Core\Settings::load($path);

$app = \Plasticode\Core\App::get($appSettings);
$container = $app->getContainer();
$settings = $container->get('settings');

if ($settings['debug']) {
    debugModeOn();
}

$bootstrap = new \App\Config\Bootstrap($settings, $debug, __DIR__);
\Plasticode\Core\Core::bootstrap($container, $bootstrap->getMappings());

// middleware
$app->add(new \Plasticode\Middleware\SlashMiddleware($container));
$app->add(new \Plasticode\Middleware\CookieAuthMiddleware($container, $settings['auth_token_key']));

require $root . '/src/routes.php';

$app->run();
