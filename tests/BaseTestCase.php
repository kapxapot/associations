<?php

namespace App\Tests;

use App\Config\Bootstrap;
use App\Models\User;
use PHPUnit\Framework\TestCase;
use Plasticode\Core\App;
use Plasticode\Core\Core;
use Plasticode\Core\Env;
use Plasticode\Core\Settings;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class BaseTestCase extends TestCase
{
    const DEFAULT_USER_ID = 1;

    /** @var \Slim\App */
    protected $app;

    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /** @var array */
    protected $settings;

    /**
     * Use middleware when running application?
     *
     * @var bool
     */
    protected $withMiddleware = true;

    protected function setUp() : void
    {
        parent::setUp();

        $this->createApplication();
    }

    protected function tearDown() : void
    {
        unset($this->app);

        parent::tearDown();
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string            $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string            $requestUri    the request URI
     * @param array|object|null $requestData   the request data
     *
     * @param array             $headers
     *
     * @return \Psr\Http\Message\ResponseInterface|\Slim\Http\Response
     */
    public function runApp($requestMethod, $requestUri, $requestData = null, $headers = [])
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            array_merge(
                [
                    'REQUEST_METHOD'   => $requestMethod,
                    'REQUEST_URI'      => $requestUri,
                    'Content-Type'     => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
                $headers
            )
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        // Set up a response object
        $response = new Response();

        // Process the application and Return the response
        return $this->app->process($request, $response);
    }

    /**
     * Make a request to the Api
     *
     * @param       $requestMethod
     * @param       $requestUri
     * @param null  $requestData
     * @param array $headers
     *
     * @return \Psr\Http\Message\ResponseInterface|\Slim\Http\Response
     */
    public function request($requestMethod, $requestUri, $requestData = null, $headers = [])
    {
        return $this->runApp($requestMethod, $requestUri, $requestData, $headers);
    }

    protected function createApplication()
    {
        $root = __DIR__ . '/..';
        $dir = $root . '/src';
        
        $env = Env::load($root);
        
        $appSettings = Settings::load($root . '/settings');
        
        $this->app = $app = App::get($appSettings);
        $this->container = $container = $this->app->getContainer();
        $this->settings = $settings = $this->container->get('settings');
        
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set("display_errors", 1);
        ini_set("log_errors_max_len", 0);
        
        $bootstrap = new Bootstrap($settings, $dir);
        
        Core::bootstrap(
            $container,
            $bootstrap->getMappings(),
            ['App\\Validation\\Rules\\']
        );
        
        // middleware
 
        if ($this->withMiddleware) {
            $app->add(
                new \Plasticode\Middleware\SlashMiddleware()
            );
            
            $app->add(
                new \Plasticode\Middleware\CookieAuthMiddleware(
                    $container->auth,
                    $settings['auth_token_key']
                )
            );
        }
        
        require $root . '/src/routes.php';
    }

    protected function getDefaultUser() : User
    {
        return $this->container->userRepository->get(self::DEFAULT_USER_ID);
    }
}
