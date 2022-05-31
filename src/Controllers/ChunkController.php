<?php

namespace App\Controllers;

use App\Chunks\Core\ChunkSource;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChunkController extends Controller
{
    private ChunkSource $chunkSource;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->chunkSource = $container->get(ChunkSource::class);
    }

    public function get(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $params = $request->getQueryParams();
        $chunkName = $params['chunk'] ?? null;

        if (!$chunkName) {
            throw new BadRequestException('Chunk name is missing.');
        }

        $chunk = $this->chunkSource->get($chunkName);

        if (!$chunk) {
            throw new BadRequestException('Unknown chunk.');
        }

        // can throw exceptions as a bad result / no result
        $chunkResult = $chunk->process($params);

        $result = $this->view->fetch(
            sprintf('chunks/%s.twig', $chunkResult->template),
            $chunkResult->data
        );

        return Response::text($response, $result);
    }
}
