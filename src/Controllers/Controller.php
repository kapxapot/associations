<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\Core\Interfaces\LinkerInterface;
use App\Core\Serializer;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Semantics\PartOfSpeech;
use App\Semantics\Scope;
use App\Semantics\Severity;
use App\Services\CasesService;
use App\Services\LanguageService;
use Plasticode\Controllers\Controller as BaseController;
use Plasticode\Core\AppContext;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Events\EventDispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller extends BaseController
{
    protected AssociationRepositoryInterface $associationRepository;
    protected WordRepositoryInterface $wordRepository;

    protected CasesService $casesService;
    protected LanguageService $languageService;

    protected AssociationConfigInterface $associationConfig;
    protected WordConfigInterface $wordConfig;

    protected AuthInterface $auth;
    protected EventDispatcher $eventDispatcher;
    protected LinkerInterface $linker;
    protected RendererInterface $renderer;
    protected Serializer $serializer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct(
            $container->get(AppContext::class)
        );

        $this->associationRepository =
            $container->get(AssociationRepositoryInterface::class);

        $this->wordRepository =
            $container->get(WordRepositoryInterface::class);

        $this->casesService = $container->get(CasesService::class);
        $this->languageService = $container->get(LanguageService::class);

        $this->associationConfig = $container->get(AssociationConfigInterface::class);
        $this->wordConfig = $container->get(WordConfigInterface::class);

        $this->auth = $container->get(AuthInterface::class);
        $this->eventDispatcher = $container->get(EventDispatcher::class);
        $this->linker = $container->get(LinkerInterface::class);
        $this->renderer = $container->get(RendererInterface::class);
        $this->serializer = $container->get(Serializer::class);
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

        $params['parts_of_speech'] = PartOfSpeech::known();
        $params['scopes'] = Scope::allNames();
        $params['severities'] = Severity::allNames();

        $settings['params'] = $params;

        return parent::buildParams($settings);
    }

    /**
     * Checks if the query params contain "debug" var.
     */
    protected function isDebug(ServerRequestInterface $request): bool
    {
        $debug = $request->getQueryParams()['debug'] ?? null;

        return $debug !== null;
    }
}
