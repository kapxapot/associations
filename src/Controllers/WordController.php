<?php

namespace App\Controllers;

use App\Data\QueryReportBuilder;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use App\Services\WordService;
use Plasticode\Core\Response;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Plasticode\Search\SearchParams;
use Plasticode\Semantics\Sentence;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WordController extends Controller
{
    private WordRelationTypeRepositoryInterface $wordRelationTypeRepository;
    private WordService $wordService;

    private NotFoundHandlerInterface $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->wordRelationTypeRepository = $container->get(
            WordRelationTypeRepositoryInterface::class
        );

        $this->wordService = $container->get(WordService::class);

        $this->notFoundHandler = $container->get(NotFoundHandlerInterface::class);
    }

    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $debug = $this->isDebug($request);

        $params = $this->buildParams([
            'params' => [
                'title' => 'Слова',
                'debug' => $debug,
            ],
        ]);

        return $this->render($response, 'main/words/index.twig', $params);
    }

    public function publicWords(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $searchParams = SearchParams::fromRequest($request);

        $searchResult = $this
            ->wordService
            ->searchAllPublic($searchParams);

        return Response::json($response, $searchResult);
    }

    public function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $id = $args['id'];

        $debug = $this->isDebug($request);

        $word = $this->wordRepository->get($id);
        $user = $this->auth->getUser();

        if ($word === null || !$word->isVisibleFor($user)) {
            return ($this->notFoundHandler)($request, $response);
        }

        // preload
        $word->aggregatedAssociations();

        $approvedInvis = $word->approvedInvisibleAssociations();
        $notApprovedInvis = $word->notApprovedInvisibleAssociations();

        $approvedInvisStr = $this
            ->casesService
            ->invisibleAssociationCountStr(
                $approvedInvis->count()
            );

        $notApprovedInvisStr = $this
            ->casesService
            ->invisibleAssociationCountStr(
                $notApprovedInvis->count()
            );

        $parsedDefinition = $this
            ->wordService
            ->getParsedTransitiveDefinition($word);

        $params = [
            'word' => $word,
            'approved_invisible_associations_str' => $approvedInvisStr,
            'not_approved_invisible_associations_str' => $notApprovedInvisStr,
            'definition' => $parsedDefinition,
            'word_relation_types' => $this->wordRelationTypeRepository->getAll(),
            'disqus_id' => 'word' . $word->getId(),
            'debug' => $debug,
        ];

        if ($parsedDefinition !== null) {
            $params['page_description'] = Sentence::buildCased([
                $parsedDefinition->word()->word,
                ' — ',
                $parsedDefinition->firstDefinition(),
            ]);
        }

        $data = $this->buildParams(['params' => $params]);

        $render = $this->render($response, 'main/words/item.twig', $data);

        $showQueries = array_key_exists('show_queries', $request->getQueryParams());

        if ($showQueries && $user && $user->isAdmin()) {
            $reportBuilder = new QueryReportBuilder();
            $report = $reportBuilder->buildReport();

            return Response::json($response, $report);
        }

        return $render;
    }

    public function latestChunk(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $user = $this->auth->getUser();

        $language = $this->languageService->getCurrentLanguageFor($user);

        $words = $this
            ->wordRepository
            ->getLastAddedByLanguage(
                $language,
                $this->wordConfig->wordLastAddedLimit()
            );

        return $words->any()
            ? $this->render(
                $response,
                'components/word_list.twig',
                $this->buildParams([
                    'params' => ['words' => $words],
                ])
            )
            : Response::text($response, $this->translate('No words yet. :('));
    }
}
