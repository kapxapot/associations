<?php

namespace App\Controllers;

use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\WordRecountService;
use Plasticode\Core\Response;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WordRecountController
{
    private WordRepositoryInterface $wordRepository;
    private WordRecountService $wordRecountService;

    private NotFoundHandlerInterface $notFoundHandler;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        WordRecountService $wordRecountService,
        NotFoundHandlerInterface $notFoundHandler
    )
    {
        $this->wordRepository = $wordRepository;
        $this->wordRecountService = $wordRecountService;

        $this->notFoundHandler = $notFoundHandler;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $id = $args['id'];

        $word = $this->wordRepository->get($id);

        if ($word === null) {
            return ($this->notFoundHandler)($request, $response);
        }

        $word = $this->wordRecountService->recountAll($word);

        return Response::json($response, $word->serialize());
    }
}
