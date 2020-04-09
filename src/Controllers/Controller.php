<?php

namespace App\Controllers;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\Models\Game;
use App\Models\Language;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\AnniversaryService;
use App\Services\CasesService;
use App\Services\LanguageService;
use Plasticode\Controllers\Controller as BaseController;
use Psr\Container\ContainerInterface;

class Controller extends BaseController
{
    protected AssociationRepositoryInterface $associationRepository;
    protected WordRepositoryInterface $wordRepository;

    protected AnniversaryService $anniversaryService;
    protected CasesService $casesService;
    protected LanguageService $languageService;

    protected AssociationConfigInterface $associationConfig;
    protected WordConfigInterface $wordConfig;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->appContext);

        $this->associationRepository = $container->associationRepository;
        $this->wordRepository = $container->wordRepository;

        $this->anniversaryService = $container->anniversaryService;
        $this->casesService = $container->casesService;
        $this->languageService = $container->languageService;

        $this->associationConfig = $container->config;
        $this->wordConfig = $container->config;
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
        }
        
        // todo: move this to SidebarPartsProviderService
        if ($language) {
            $wordCount = $this
                ->wordRepository
                ->getByLanguageCount($language);

            $wordCountStr = $this
                ->casesService
                ->wordCount($wordCount);

            $associationCount = $this
                ->associationRepository
                ->getByLanguageCount($language);

            $associationCountStr = $this
                ->casesService
                ->associationCount($associationCount);
            
            $params['language'] = $language;
            
            $params = array_merge(
                $params,
                [
                    'word_count' => $wordCount,
                    'word_count_str' => $wordCountStr,

                    'word_anniversary' => $this
                        ->anniversaryService
                        ->toAnniversary($wordCount),

                    'last_added_words' => $this
                        ->wordRepository
                        ->getLastAddedByLanguage(
                            $language,
                            $this->wordConfig->wordLastAddedLimit()
                        ),
                    
                    'association_count' => $associationCount,
                    'association_count_str' => $associationCountStr,

                    'association_anniversary' => $this
                        ->anniversaryService
                        ->toAnniversary($associationCount),

                    'last_added_associations' => $this
                        ->associationRepository
                        ->getLastAddedByLanguage(
                            $language,
                            $this->associationConfig->associationLastAddedLimit()
                        ),
                ]
            );
        }
        
        return parent::buildParams(['params' => $params]);
    }
}
