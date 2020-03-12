<?php

use App\Controllers\AssociationController;
use App\Controllers\FeedbackController;
use App\Controllers\GameController;
use App\Controllers\IndexController;
use App\Controllers\JobController;
use App\Controllers\TestController;
use App\Controllers\TurnController;
use App\Controllers\WordController;
use Plasticode\Controllers\Auth\AuthController;
use Plasticode\Controllers\Auth\PasswordController;
use Plasticode\Controllers\ParserController;
use Plasticode\Core\Response;
use Plasticode\Middleware\AuthMiddleware;
use Plasticode\Middleware\GuestMiddleware;
use Plasticode\Middleware\AccessMiddleware;
use Plasticode\Middleware\TokenAuthMiddleware;

/**
 * Creates AccessMiddleware
 * 
 * @var \Closure
 */
$access = function (string $entity, string $action, string $redirect = null) use ($container) {
    return new AccessMiddleware(
        $container->access,
        $container->router,
        $entity,
        $action,
        $redirect
    );
};

$root = $settings['root'];
$trueRoot = (strlen($root) == 0);

$app->group($root, function () use ($trueRoot, $settings, $access, $container, $env) {

    // public api
    
    $this->group('/api/v1', function () use ($settings) {
        $this->get('/captcha', function ($request, $response, $args) use ($settings) {
            $captcha = $this->captcha->generate($settings['captcha_digits'], true);
            return Response::json($response, [ 'captcha' => $captcha['captcha'] ]);
        });

        $this->get('/public/words', WordController::class . ':publicWords')
            ->setName('api.public.words');
    });

    // private api
    
    $this->group('/api/v1', function () use ($settings, $access, $container) {
        foreach ($settings['tables'] as $alias => $table) {
            if (isset($table['api'])) {
                $gen = $container->generatorResolver->resolveEntity($alias);
                $gen->generateAPIRoutes($this, $access);
            }
        }
    
        $this->post('/parser/parse', ParserController::class . ':parse')
            ->setName('api.parser.parse');
    })->add(new TokenAuthMiddleware($container->auth));
    
    // admin
    
    $this->get('/admin', function ($request, $response, $args) {
        return $this->view->render($response, 'admin/index.twig');
    })->setName('admin.index');
    
    $this->group('/admin', function () use ($settings, $access, $container) {
        foreach (array_keys($settings['entities']) as $entity) {
            $gen = $container->generatorResolver->resolveEntity($entity);
            $gen->generateAdminPageRoute($this, $access);
        }
    })->add(new AuthMiddleware($container->router, $container->auth, 'admin.index'));

    // site
    
    $this->group('/actions', function () {
        $this->post('/game/start', GameController::class . ':start')->setName('actions.game.start');
        $this->post('/game/finish', GameController::class . ':finish')->setName('actions.game.finish');
        $this->post('/turn/create', TurnController::class . ':create')->setName('actions.turn.create');
        $this->post('/feedback', FeedbackController::class . ':save')->setName('actions.feedback');
    })->add(new TokenAuthMiddleware($container->auth));

    $this->get('/associations/{id:\d+}', AssociationController::class . ':get')->setName('main.association');

    $this->get('/games/{id:\d+}', GameController::class . ':get')->setName('main.game');

    $this->get('/words/{id:\d+}', WordController::class . ':get')->setName('main.word');
    $this->get('/words', WordController::class . ':index')->setName('main.words');
    
    if ($env->isDev()) {
        $this->get('/test', TestController::class . ':index')->setName('main.test');
    }

    $this->get($trueRoot ? '/' : '', IndexController::class . ':index')->setName('main.index');

    // jobs
    
    $this->group('/jobs', function () {
        $this->get('/update/associations', JobController::class . ':updateAssociations')->setName('main.jobs.update.associations');
        $this->get('/update/words', JobController::class . ':updateWords')->setName('main.jobs.update.words');
    });

    // auth
    
    $this->group('/auth', function () {
        $this->post('/signup', AuthController::class . ':postSignUp')->setName('auth.signup');
        $this->post('/signin', AuthController::class . ':postSignIn')->setName('auth.signin');
    })->add(new GuestMiddleware($container->router, $container->auth, 'main.index'));
        
    $this->group('/auth', function () {
        $this->post('/signout', AuthController::class . ':postSignOut')->setName('auth.signout');
        $this->post('/password/change', PasswordController::class . ':postChangePassword')->setName('auth.password.change');
    })->add(new AuthMiddleware($container->router, $container->auth, 'main.index'));
});
