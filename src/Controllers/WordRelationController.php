<?php

namespace App\Controllers;

use App\Services\WordRelationService;
use Plasticode\Core\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WordRelationController extends Controller
{
    private WordRelationService $wordRelationService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->wordRelationService = $container->get(WordRelationService::class);
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $data = $request->getParsedBody();
        $user = $this->auth->getUser();

        $this->wordRelationService->save($data, $user);

        return Response::json(
            $response,
            ['message' => $this->translate('Word relation saved successfully.')]
        );
    }
}
