<?php

namespace App\Controllers;

use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChunkController extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $chunk = $args['chunk'];

        if ($chunk === 'word-origin') {
            return $this->wordOrigin($request, $response);
        }

        throw new BadRequestException();
    }

    private function wordOrigin(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $wordId = $request->getQueryParams()['id'] ?? null;

        $word = $this->wordRepository->get($wordId);
        $user = $this->auth->getUser();

        if (!$word || !$word->isVisibleFor($user)) {
            throw new BadRequestException();
        }

        return $this->renderChunk(
            $response,
            'word_origin',
            [
                'word' => $word,
            ]
        );
    }

    private function renderChunk(
        ResponseInterface $response,
        string $chunk,
        array $data
    ): ResponseInterface
    {
        $result = $this->view->fetch(
            sprintf('chunks/%s.twig', $chunk),
            $data
        );

        return Response::text($response, $result);
    }
}
