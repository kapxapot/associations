<?php

use Plasticode\Core\Env;
use Plasticode\DI\Autowirer;
use Plasticode\DI\Containers\AutowiringContainer;
use Plasticode\DI\ParamResolvers\UntypedContainerParamResolver;
use Plasticode\Mapping\Aggregators\WritableMappingAggregator;
use Plasticode\Middleware\CookieAuthMiddleware;
use Plasticode\Middleware\SlashMiddleware;
use Plasticode\Services\AuthService;
use Plasticode\Settings\SettingsFactory;
use Respect\Validation\Validator;

$dir = __DIR__;
$root = $dir . '/..';

require $root . '/vendor/autoload.php';

$env = Env::load($root);
$settings = SettingsFactory::make($root);

$debug = $settings['debug'] ?? false;

if ($debug) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    ini_set("log_errors_max_len", 0);
} else {
    $errorLevel = error_reporting();
    error_reporting($errorLevel & ~E_NOTICE & ~E_DEPRECATED);
}


$slimSettings = [
    'httpVersion' => '1.1',
    'responseChunkSize' => 4096,
    'outputBuffering' => 'append',
    'determineRouteBeforeAppMiddleware' => false,
    'displayErrorDetails' => $debug,
    'addContentLengthHeader' => true,
    'routerCacheFile' => false,
];

$autowirer = (new Autowirer())
    ->withUntypedParamResolver(
        new UntypedContainerParamResolver()
    );

$container = new AutowiringContainer($autowirer);

$container['settings'] = fn () => new Slim\Collection($slimSettings);

$defaultSlimProvider = new Slim\DefaultServicesProvider();
$defaultSlimProvider->register($container);


$app = new Slim\App($container);

session_start();

$bootstrap = new WritableMappingAggregator($container);

$bootstrap->registerMany(
    new Plasticode\Mapping\Providers($settings),
    new Plasticode\Data\Idiorm\Providers(),
    new App\Mapping\Providers(),
    new Brightwood\Mapping\Providers()
);

$bootstrap->boot();

foreach ($settings['validation_namespaces'] as $namespace) {
    Validator::with($namespace);
}

$container[Env::class] = $env;

// middleware

$app->add(new SlashMiddleware());

$app->add(
    new CookieAuthMiddleware(
        $container->get(AuthService::class),
        $settings['auth_token_key']
    )
);

require $root . '/src/routes.php';

$app->run();
