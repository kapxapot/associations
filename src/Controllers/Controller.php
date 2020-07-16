<?php

namespace App\Controllers;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\Core\Interfaces\LinkerInterface;
use App\Core\Serializer;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\CasesService;
use App\Services\LanguageService;
use Plasticode\Controllers\Controller as BaseController;
use Psr\Container\ContainerInterface;

class Controller extends BaseController
{
    protected AssociationRepositoryInterface $associationRepository;
    protected WordRepositoryInterface $wordRepository;

    protected CasesService $casesService;
    protected LanguageService $languageService;

    protected AssociationConfigInterface $associationConfig;
    protected WordConfigInterface $wordConfig;

    protected LinkerInterface $linker;
    protected Serializer $serializer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->appContext);

        $this->associationRepository = $container->associationRepository;
        $this->wordRepository = $container->wordRepository;

        $this->casesService = $container->casesService;
        $this->languageService = $container->languageService;

        $this->associationConfig = $container->config;
        $this->wordConfig = $container->config;

        $this->linker = $container->linker;
        $this->serializer = $container->serializer;
    }

    /**
     * Auto-switch to one-column layout?
     */
    protected bool $autoOneColumn = false;

    protected function buildParams(array $settings) : array
    {
        $params = $settings['params'] ?? [];

        /** @var Game|null */
        $game = $params['game'] ?? null;

        /** @var Language|null */
        $language = $params['language'] ?? null;

        if (is_null($language)) {
            $language = $game
                ? $game->language()
                : $this->languageService->getDefaultLanguage();

            $params['language'] = $language;
        }

        return parent::buildParams(['params' => $params]);
    }
}
