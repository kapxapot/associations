<?php

namespace App\Config;

use App\Services\AssociationFeedbackService;
use App\Services\WordFeedbackService;
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
                'userClass' => function (ContainerInterface $container) {
                    return \App\Models\User::class;
                },

                'localizationConfig' => function (ContainerInterface $container) {
                    return new \App\Config\LocalizationConfig();
                },

                'linker' => function (ContainerInterface $container) {
                    return new \App\Core\Linker($container);
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
                    return new \App\Services\WordRecountService($container);
                },

                'associationRecountService' => function (ContainerInterface $container) {
                    return new \App\Services\AssociationRecountService(
                        $container
                    );
                },
                
                'associationService' => function (ContainerInterface $container) {
                    return new \App\Services\AssociationService($container);
                },
                
                'associationFeedbackService' => function (ContainerInterface $container) {
                    return new AssociationFeedbackService(
                        $container,
                        $container->validator
                    );
                },
                
                'languageService' => function (ContainerInterface $container) {
                    return new \App\Services\LanguageService($container);
                },

                'gameService' => function (ContainerInterface $container) {
                    return new \App\Services\GameService($container);
                },

                'turnService' => function (ContainerInterface $container) {
                    return new \App\Services\TurnService($container);
                },
                
                'wordService' => function (ContainerInterface $container) {
                    return new WordService(
                        $container,
                        $container->config,
                        $container->validator
                    );
                },
                
                'wordFeedbackService' => function (ContainerInterface $container) {
                    return new WordFeedbackService(
                        $container,
                        $container->validator
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
