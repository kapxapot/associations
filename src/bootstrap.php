<?php

use App\Config\Bootstrap;
use Plasticode\Core\Env;
use Plasticode\Core\Settings;
use Plasticode\Middleware\CookieAuthMiddleware;
use Plasticode\Middleware\SlashMiddleware;
use Respect\Validation\Validator;
use Slim\App;

$dir = __DIR__;
$root = $dir . '/..';

require $root . '/vendor/autoload.php';

$env = Env::load($root);
$settings = Settings::load($root . '/settings');
$app = new App(['settings' => $settings]);
$container = $app->getContainer();

if ($settings['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    ini_set("log_errors_max_len", 0);
} else {
    $errorLevel = error_reporting();
    error_reporting($errorLevel & ~E_NOTICE & ~E_DEPRECATED);
}

session_start();

$bootstrap = new Bootstrap($settings, $dir);
$bootstrap->boot($container);

foreach ($settings['validation_namespaces'] as $namespace) {
    Validator::with($namespace);
}

$container['env'] = $env;

// middleware

$app->add(new SlashMiddleware());

$app->add(
    new CookieAuthMiddleware(
        $container->authService,
        $settings['auth_token_key']
    )
);

require $root . '/src/routes.php';

$app->run();
