<?php

use Plasticode\Core\Core;
use Plasticode\Middleware\AuthMiddleware;
use Plasticode\Middleware\GuestMiddleware;
use Plasticode\Middleware\AccessMiddleware;
use Plasticode\Middleware\TokenAuthMiddleware;

$access = function($entity, $action, $redirect = null) use ($container) {
	return new AccessMiddleware($container, $entity, $action, $redirect);
};

$root = $settings['root'];
$trueRoot = (strlen($root) == 0);

$app->group($root, function() use ($trueRoot, $settings, $access, $container) {
	// api
	
	$this->group('/api/v1', function() use ($settings) {
		$this->get('/captcha', function($request, $response, $args) use ($settings) {
			$captcha = $this->captcha->generate($settings['captcha_digits'], true);
			return Core::json($response, [ 'captcha' => $captcha['captcha'] ]);
		});

    	$this->get('/public/words', \App\Controllers\WordController::class . ':publicWords')
    	    ->setName('api.public.words');
	});
	
	$this->group('/api/v1', function() use ($settings, $access, $container) {
		foreach ($settings['tables'] as $alias => $table) {
			if (isset($table['api'])) {
				$gen = $container->generatorResolver->resolveEntity($alias);
				$gen->generateAPIRoutes($this, $access);
			}
		}
	
		$this->post('/parser/parse', \Plasticode\Controllers\ParserController::class . ':parse')
			->setName('api.parser.parse');
	})->add(new TokenAuthMiddleware($container));
	
	// admin
	
	$this->get('/admin', function($request, $response, $args) {
		return $this->view->render($response, 'admin/index.twig');
	})->setName('admin.index');
	
	$this->group('/admin', function() use ($settings, $access, $container) {
		foreach (array_keys($settings['entities']) as $entity) {
			$gen = $container->generatorResolver->resolveEntity($entity);
			$gen->generateAdminPageRoute($this, $access);
		}
	})->add(new AuthMiddleware($container, 'admin.index'));

	// site
	
	$this->group('/actions', function() {
    	$this->post('/game/finish', \App\Controllers\GameController::class . ':finish')->setName('actions.game.finish');
    	$this->post('/feedback', \App\Controllers\FeedbackController::class . ':save')->setName('actions.feedback');
	})->add(new TokenAuthMiddleware($container));

	$this->get('/associations/{id:\d+}', \App\Controllers\AssociationController::class . ':item')->setName('main.association');
	$this->get('/games/{id:\d+}', \App\Controllers\GameController::class . ':item')->setName('main.game');

	$this->get('/words/{id:\d+}', \App\Controllers\WordController::class . ':item')->setName('main.word');
	$this->get('/words', \App\Controllers\WordController::class . ':index')->setName('main.words');
	
	$this->get('/test', \App\Controllers\TestController::class . ':index')->setName('main.test');

	$this->get($trueRoot ? '/' : '', \App\Controllers\IndexController::class . ':index')->setName('main.index');

	// auth
	
	$this->group('/auth', function() {
		$this->post('/signup', \Plasticode\Controllers\Auth\AuthController::class . ':postSignUp')->setName('auth.signup');
		$this->post('/signin', \Plasticode\Controllers\Auth\AuthController::class . ':postSignIn')->setName('auth.signin');
	})->add(new GuestMiddleware($container, 'main.index'));
		
	$this->group('/auth', function() {
		$this->post('/signout', \Plasticode\Controllers\Auth\AuthController::class . ':postSignOut')->setName('auth.signout');
		$this->post('/password/change', \Plasticode\Controllers\Auth\PasswordController::class . ':postChangePassword')->setName('auth.password.change');
	})->add(new AuthMiddleware($container, 'main.index'));
});
