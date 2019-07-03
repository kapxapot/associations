<?php

namespace App\Config;

use Plasticode\Config\Bootstrap as BootstrapBase;
use Plasticode\Util\Cases;

class Bootstrap extends BootstrapBase
{
    public function getMappings()
    {
        $mappings = parent::getMappings();
        
        return array_merge(
            $mappings,
            [
                // $c == $container
                
                'userClass' => function ($c) {
                    return \App\Models\User::class;
                },

                'localization' => function ($c) {
                    return new \App\Config\Localization();
                },
            
                'captchaConfig' => function ($c) {
                    return new \App\Config\Captcha;  
                },

                'linker' => function ($c) {
                    return new \App\Core\Linker($c);
                },
                
                'config' => function ($c) {
                    return new \App\Config\Config($c);
                },

                'eventProcessors' => function ($c) {
                    return [
                        $c->wordRecountService,
                        $c->associationRecountService,
                    ];
                },
                
                // services

                'wordRecountService' => function ($c) {
                    return new \App\Services\WordRecountService($c);
                },

                'associationRecountService' => function ($c) {
                    return new \App\Services\AssociationRecountService($c);
                },
                
                'associationService' => function ($c) {
                    return new \App\Services\AssociationService($c);
                },
                
                'associationFeedbackService' => function ($c) {
                    return new \App\Services\AssociationFeedbackService($c);
                },
                
                'languageService' => function ($c) {
                    return new \App\Services\LanguageService($c);
                },

                'gameService' => function ($c) {
                    return new \App\Services\GameService($c);
                },

                'turnService' => function ($c) {
                    return new \App\Services\TurnService($c);
                },
                
                'wordService' => function ($c) {
                    return new \App\Services\WordService($c);
                },
                
                'wordFeedbackService' => function ($c) {
                    return new \App\Services\WordFeedbackService($c);
                },

                // handlers
                
                'notFoundHandler' => function ($c) {
                    return new \App\Handlers\NotFoundHandler($c);
                },
            ]
        );
    }
}
