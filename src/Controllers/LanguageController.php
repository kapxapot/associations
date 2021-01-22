<?php

namespace App\Controllers;

use App\Services\AnniversaryService;
use Plasticode\Core\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LanguageController extends Controller
{
    private AnniversaryService $anniversaryService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->anniversaryService = $container->get(AnniversaryService::class);
    }

    public function statsChunk(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $user = $this->auth->getUser();

        $language = $this->languageService->getCurrentLanguageFor($user);

        $wordCount = $this
            ->wordRepository
            ->getCountByLanguage($language);

        $wordCountStr = $this
            ->casesService
            ->wordCount($wordCount);

        $associationCount = $this
            ->associationRepository
            ->getCountByLanguage($language);

        $associationCountStr = $this
            ->casesService
            ->associationCount($associationCount);

        $result = $this->renderer->component(
            'language_stats',
            [
                'word_count' => $wordCount,
                'word_count_str' => $wordCountStr,

                'word_anniversary' => $this
                    ->anniversaryService
                    ->toAnniversary($wordCount),

                'association_count' => $associationCount,
                'association_count_str' => $associationCountStr,

                'association_anniversary' => $this
                    ->anniversaryService
                    ->toAnniversary($associationCount),
            ]
        );

        return Response::text($response, $result);
    }
}
