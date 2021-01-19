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
use Plasticode\Config\Config;
use Plasticode\Controllers\AuthController;
use Plasticode\Controllers\CaptchaController;
use Plasticode\Controllers\ParserController;
use Plasticode\Controllers\PasswordController;
use Plasticode\Core\Env;
use Plasticode\Generators\Core\GeneratorResolver;
use Plasticode\Middleware\AuthMiddleware;
use Plasticode\Middleware\GuestMiddleware;
use Plasticode\Middleware\TokenAuthMiddleware;
use Plasticode\Services\AuthService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Interfaces\RouterInterface;

/** @var App $app */
/** @var ContainerInterface $container */

/** @var SettingsProviderInterface */
$settingsProvider = $container->get(SettingsProviderInterface::class);

$root = $settingsProvider->get('root');

$app->group(
    $root,
    function () use ($root, $settingsProvider, $container) {
        $apiPrefix = '/api/v1';

        // public api

        $this->group(
            $apiPrefix,
            function () {
                $this->get(
                    '/captcha',
                    CaptchaController::class
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
            function () use ($container) {
                /** @var Config */
                $config = $container->get(Config::class);

                /** @var GeneratorResolver */
                $resolver = $container->get(GeneratorResolver::class);

                foreach ($config->tableMetadata()->all() as $table) {
                    if (!isset($table['api'])) {
                        continue;
                    }

                    $generator = $resolver->resolve($table['entity']);

                    $generator->generateAPIRoutes($this);
                }

                $this
                    ->post('/parser/parse', ParserController::class)
                    ->setName('api.parser.parse');
            }
        )->add(
            new TokenAuthMiddleware(
                $container->get(AuthService::class)
            )
        );

        // admin

        $this->get(
            '/admin',
            function ($request, $response) {
                return $this->view->render($response, 'admin/index.twig');
            }
        )->setName('admin.index');

        $this->group(
            '/admin',
            function () use ($container) {
                /** @var Config */
                $config = $container->get(Config::class);

                /** @var GeneratorResolver */
                $resolver = $container->get(GeneratorResolver::class);

                $entityNames = array_keys(
                    $config->entitySettings()->all()
                );

                foreach ($entityNames as $entityName) {
                    $entityClass = $config
                        ->tableMetadata()
                        ->get($entityName . '.entity');

                    $generator = $resolver->resolve($entityClass);

                    $generator->generateAdminPageRoute($this);
                }
            }
        )->add(
            new AuthMiddleware(
                $container->get(RouterInterface::class),
                $container->get(AuthService::class),
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
        )->add(
            new TokenAuthMiddleware(
                $container->get(AuthService::class)
            )
        );

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

        /** @var Env */
        $env = $container->get(Env::class);

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

        $this
            ->get('/news/{id:\d+}', NewsController::class)
            ->setName('main.news');

        $this
            ->get('/tags/{tag}', TagController::class)
            ->setName('main.tag');

        $telegramBotToken = $settingsProvider->get('telegram.bot_token');

        if (strlen($telegramBotToken) > 0) {
            $this->post(
                '/bots/telegram/' . $telegramBotToken,
                TelegramBotController::class
            );
        }

        $brightwoodBotToken = $settingsProvider->get('telegram.brightwood_bot_token');

        if (strlen($brightwoodBotToken) > 0) {
            $this->post(
                '/bots/telegram/' . $brightwoodBotToken,
                BrightwoodBotController::class
            );
        }

        $this
            ->get('/{slug}', PageController::class)
            ->setName('main.page');

        $trueRoot = (strlen($root) == 0);

        $this->get(
            $trueRoot ? '/' : '',
            IndexController::class . ':index'
        )->setName('main.index');

        // jobs

        $jobsSecret = $settingsProvider->get('jobs.secret');

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
                $container->get(RouterInterface::class),
                $container->get(AuthService::class),
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
                $container->get(RouterInterface::class),
                $container->get(AuthService::class),
                'main.index'
            )
        );
    }
);
