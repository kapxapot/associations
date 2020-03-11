<?php

namespace App\Config;

use App\Core\Linker;
use App\Repositories\WordRepository;
use App\Services\AssociationFeedbackService;
use App\Services\AssociationRecountService;
use App\Services\AssociationService;
use App\Services\LanguageService;
use App\Services\WordFeedbackService;
use App\Services\WordRecountService;
use App\Services\WordService;
use Plasticode\Config\Bootstrap as BootstrapBase;
use Psr\Container\ContainerInterface;

class Bootstrap extends BootstrapBase
{
    /**
     * Get mappings for DI container.
     *
     * @return array
     */
    public function getMappings() : array
    {
        $mappings = parent::getMappings();
        
        return array_merge(
            $mappings,
            [
                'wordRepository' => function (ContainerInterface $container) {
                    return new WordRepository(
                        $container->db
                    );
                },

                'localizationConfig' => function (ContainerInterface $container) {
                    return new \App\Config\LocalizationConfig();
                },

                'linker' => function (ContainerInterface $container) {
                    return new Linker(
                        $container->settingsProvider,
                        $container->router
                    );
                },
                
                'config' => function (ContainerInterface $container) {
                    return new Config(
                        $container->settingsProvider
                    );
                },
            
                'captchaConfig' => function (ContainerInterface $container) {
                    return new \App\Config\CaptchaConfig();
                },
        
                'eventProcessors' => function (ContainerInterface $container) {
                    return [
                        $container->wordRecountService,
                        $container->associationRecountService,
                    ];
                },
                
                // services

                'wordRecountService' => function (ContainerInterface $container) {
                    return new WordRecountService(
                        $container->config
                    );
                },

                'associationRecountService' => function (ContainerInterface $container) {
                    return new AssociationRecountService(
                        $container->config
                    );
                },
                
                'associationService' => function (ContainerInterface $container) {
                    return new AssociationService($container);
                },
                
                'associationFeedbackService' => function (ContainerInterface $container) {
                    return new AssociationFeedbackService(
                        $container->validator,
                        $container->validationRules
                    );
                },
                
                'languageService' => function (ContainerInterface $container) {
                    return new LanguageService(
                        $container->settingsProvider,
                        $container->wordService
                    );
                },

                'gameService' => function (ContainerInterface $container) {
                    return new \App\Services\GameService($container);
                },

                'turnService' => function (ContainerInterface $container) {
                    return new \App\Services\TurnService($container);
                },
                
                'wordService' => function (ContainerInterface $container) {
                    return new WordService(
                        $container->settingsProvider,
                        $container->config,
                        $container->validator,
                        $container->wordRepository
                    );
                },
                
                'wordFeedbackService' => function (ContainerInterface $container) {
                    return new WordFeedbackService(
                        $container->validator,
                        $container->validationRules,
                        $container->wordService
                    );
                },

                'yandexDictService' => function (ContainerInterface $container) {
                    return new \App\Services\YandexDictService($container);
                },

                'dictionaryService' => function (ContainerInterface $container) {
                    return new \App\Services\DictionaryService(
                        $container->yandexDictService
                    );
                },

                // external

                'yandexDict' => function (ContainerInterface $container) {
                    $key = $this->settings['yandex_dict']['key'];
                    return new \App\External\YandexDict($key);
                },

                // handlers
                
                'notFoundHandler' => function (ContainerInterface $container) {
                    return new \App\Handlers\NotFoundHandler($container);
                },
            ]
        );
    }
}
