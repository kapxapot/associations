<?php

use App\Controllers\AssociationController;
use App\Controllers\FeedbackController;
use App\Controllers\GameController;
use App\Controllers\IndexController;
use App\Controllers\JobController;
use App\Controllers\LanguageController;
use App\Controllers\NewsController;
use App\Controllers\PageController;
use App\Controllers\SearchController;
use App\Controllers\TagController;
use App\Controllers\TelegramBotController;
use App\Controllers\TestController;
use App\Controllers\TurnController;
use App\Controllers\WordController;
use Brightwood\Controllers\BrightwoodBotController;
use Brightwood\Controllers\CardsTestController;
use Brightwood\Controllers\EightsTestController;
use Plasticode\Controllers\Auth\AuthController;
use Plasticode\Controllers\Auth\PasswordController;
use Plasticode\Controllers\ParserController;
use Plasticode\Core\Response;
use Plasticode\Generators\Basic\GeneratorResolver;
use Plasticode\Generators\Interfaces\EntityGeneratorInterface;
use Plasticode\Middleware\AuthMiddleware;
use Plasticode\Middleware\GuestMiddleware;
use Plasticode\Middleware\AccessMiddleware;
use Plasticode\Middleware\TokenAuthMiddleware;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */

/**
 * Creates AccessMiddleware.
 * 
 * @var callable
 */
$access = fn (string $entity, string $action, ?string $redirect = null)
    => new AccessMiddleware(
        $container->access,
        $container->auth,
        $container->router,
        $entity,
        $action,
        $redirect
    );

$root = $settings['root'];
$trueRoot = (strlen($root) == 0);

$apiPrefix = '/api/v1';

$app->group(
    $root,
    function () use ($trueRoot, $settings, $access, $container, $env, $apiPrefix) {
        // public api

        $this->group(
            $apiPrefix,
            function () use ($settings) {
                $this->get(
                    '/captcha',
                    function ($request, $response, $args) use ($settings) {
                        $captcha = $this->captcha->generate(
                            $settings['captcha_digits'],
                            true
                        );

                        return Response::json(
                            $response,
                            ['captcha' => $captcha['captcha']]
                        );
                    }
                );

                $this->get(
                    '/search/{query}',
                    SearchController::class . ':search'
                )->setName('api.search');

                $this->post(
                    '/play',
                    GameController::class . ':play'
                )->setName('api.public.play');

                $this->get(
                    '/public/words',
                    WordController::class . ':publicWords'
                )->setName('api.public.words');
            }
        );

        // private api

        $this->group(
            $apiPrefix,
            function () use ($settings, $access, $container) {
                foreach ($settings['tables'] as $alias => $table) {
                    if (isset($table['api'])) {
                        /** @var EntityGeneratorInterface */
                        $gen = $container
                            ->get(GeneratorResolver::class)
                            ->resolve($alias);

                        $gen->generateAPIRoutes($this, $access);
                    }
                }

                $this
                    ->post('/parser/parse', ParserController::class)
                    ->setName('api.parser.parse');
            }
        )->add(new TokenAuthMiddleware($container->authService));

        // admin

        $this->get(
            '/admin',
            function ($request, $response, $args) {
                return $this->view->render($response, 'admin/index.twig');
            }
        )->setName('admin.index');

        $this->group(
            '/admin',
            function () use ($settings, $access, $container) {
                foreach (array_keys($settings['entities']) as $entity) {
                    /** @var EntityGeneratorInterface */
                    $gen = $container
                        ->get(GeneratorResolver::class)
                        ->resolve($entity);

                    $gen->generateAdminPageRoute($this, $access);
                }
            }
        )->add(
            new AuthMiddleware(
                $container->router,
                $container->authService,
                'admin.index'
            )
        );

        // site

        $this->group(
            '/actions',
            function () {
                $this->post(
                    '/game/start',
                    GameController::class . ':start'
                )->setName('actions.game.start');

                $this->post(
                    '/game/finish',
                    GameController::class . ':finish'
                )->setName('actions.game.finish');

                $this->post(
                    '/turn/create',
                    TurnController::class . ':create'
                )->setName('actions.turn.create');

                $this->post(
                    '/turn/skip',
                    TurnController::class . ':skip'
                )->setName('actions.turn.skip');

                $this->post(
                    '/feedback',
                    FeedbackController::class . ':save'
                )->setName('actions.feedback');
            }
        )->add(new TokenAuthMiddleware($container->authService));

        $this->get(
            '/associations/{id:\d+}',
            AssociationController::class . ':get'
        )->setName('main.association');

        $this->get(
            '/games/{id:\d+}',
            GameController::class . ':get'
        )->setName('main.game');

        $this->get(
            '/words/{id:\d+}',
            WordController::class . ':get'
        )->setName('main.word');

        $this->get(
            '/words',
            WordController::class . ':index'
        )->setName('main.words');

        $this->get(
            '/chunks/stats/language',
            LanguageController::class . ':statsChunk'
        )->setName('main.chunks.stats.language');

        $this->get(
            '/chunks/latest/words',
            WordController::class . ':latestChunk'
        )->setName('main.chunks.latest.words');

        $this->get(
            '/chunks/latest/associations',
            AssociationController::class . ':latestChunk'
        )->setName('main.chunks.latest.associations');

        if ($env->isDev()) {
            $this->get(
                '/test',
                TestController::class . ':index'
            )->setName('main.test');

            $this->get(
                '/test/deck',
                CardsTestController::class . ':deck'
            );

            $this->get(
                '/test/eights/play',
                EightsTestController::class . ':play'
            );

            $this->get(
                '/test/eights/serialize',
                EightsTestController::class . ':serialize'
            );
        }

        $this->get('/news/{id:\d+}', NewsController::class . ':get')
            ->setName('main.news');

        $this->get('/tags/{tag}', TagController::class . ':get')
            ->setName('main.tag');

        $telegramBotToken = $settings['telegram']['bot_token'];

        if (strlen($telegramBotToken) > 0) {
            $this->post(
                '/bots/telegram/' . $telegramBotToken,
                TelegramBotController::class
            );
        }

        $brightwoodBotToken = $settings['telegram']['brightwood_bot_token'];

        if (strlen($brightwoodBotToken) > 0) {
            $this->post(
                '/bots/telegram/' . $brightwoodBotToken,
                BrightwoodBotController::class
            );
        }

        $this->get('/{slug}', PageController::class . ':get')
            ->setName('main.page');

        $this->get(
            $trueRoot ? '/' : '',
            IndexController::class . ':index'
        )->setName('main.index');

        // jobs

        $jobsSecret = $settings['jobs']['secret'];

        if (strlen($jobsSecret) > 0) {
            $this->group(
                '/jobs/' . $jobsSecret,
                function () {
                    $this->get(
                        '/update/associations',
                        JobController::class . ':updateAssociations'
                    )->setName('main.jobs.update_associations');

                    $this->get(
                        '/update/words',
                        JobController::class . ':updateWords'
                    )->setName('main.jobs.update_words');

                    $this->get(
                        '/load_unchecked_dict_words',
                        JobController::class . ':loadUncheckedDictWords'
                    )->setName('main.jobs.load_unchecked_dict_words');

                    $this->get(
                        '/match_dangling_dict_words',
                        JobController::class . ':matchDanglingDictWords'
                    )->setName('main.jobs.match_dangling_dict_words');
                }
            );
        }

        // auth

        $this->group(
            '/auth',
            function () {
                $this->post(
                    '/signup',
                    AuthController::class . ':signUp'
                )->setName('auth.signup');

                $this->post(
                    '/signin',
                    AuthController::class . ':signIn'
                )->setName('auth.signin');
            }
        )->add(
            new GuestMiddleware(
                $container->router,
                $container->authService,
                'main.index'
            )
        );

        $this->group(
            '/auth',
            function () {
                $this->post(
                    '/signout',
                    AuthController::class . ':signOut'
                )->setName('auth.signout');

                $this
                    ->post('/password/change', PasswordController::class)
                    ->setName('auth.password.change');
            }
        )->add(
            new AuthMiddleware(
                $container->router,
                $container->authService,
                'main.index'
            )
        );
    }
);
