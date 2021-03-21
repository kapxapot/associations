<?php

namespace App\Controllers;

use App\Services\WordOverrideService;
use Plasticode\Core\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WordOverrideController extends Controller
{
    private WordOverrideService $wordOverrideService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->wordOverrideService = $container->get(WordOverrideService::class);
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $data = $request->getParsedBody();
        $user = $this->auth->getUser();

        $this->wordOverrideService->save($data, $user);

        return Response::json(
            $response,
            ['message' => $this->translate('Word override saved successfully.')]
        );
    }
}
