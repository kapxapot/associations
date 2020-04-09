<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Handlers\NotFoundHandler;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\WordService;
use Plasticode\Core\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;

class WordController extends Controller
{
    private WordRepositoryInterface $wordRepository;

    private AuthInterface $auth;
    private NotFoundHandler $notFoundHandler;
    private WordService $wordService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->wordRepository = $container->wordRepository;

        $this->auth = $container->auth;
        $this->notFoundHandler = $container->notFoundHandler;
        $this->wordService = $container->wordService;
    }

    public function index(
        SlimRequest $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $debug = $request->getQueryParam('debug', null) !== null;

        $params = $this->buildParams(
            [
                'params' => [
                    'title' => 'Слова',
                    'debug' => $debug,
                ],
            ]
        );
        
        return $this->render($response, 'main/words/index.twig', $params);
    }

    public function publicWords(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $words = $this
            ->wordRepository
            ->getAllPublic()
            ->map(
                fn ($word) => $word->serialize()
            );

        return Response::json(
            $response,
            $words,
            ['params' => $request->getQueryParams()]
        );
    }
    
    public function get(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];
        
        $debug = $request->getQueryParam('debug', null) !== null;

        $word = $this->wordRepository->get($id);
        $user = $this->auth->getUser();

        if (
            is_null($word)
            || is_null($user)
            || !$word->isVisibleFor($user)
        ) {
            return ($this->notFoundHandler)($request, $response);
        }

        $approvedStr = $this
            ->wordService
            ->approvedInvisibleAssociationsStr($word);

        $notApprovedStr = $this
            ->wordService
            ->notApprovedInvisibleAssociationsStr($word);

        $params = $this->buildParams(
            [
                'params' => [
                    'word' => $word,
                    'approved_invisible_associations_str' => $approvedStr,
                    'not_approved_invisible_associations_str' => $notApprovedStr,
                    'disqus_id' => 'word' . $word->getId(),
                    'debug' => $debug,
                ],
            ]
        );
        
        return $this->render($response, 'main/words/item.twig', $params);
    }
}
