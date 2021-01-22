<?php

namespace App\Controllers;

use App\Services\TagPartsProviderService;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TagController extends Controller
{
    private TagPartsProviderService $tagPartsProviderService;

    private NotFoundHandlerInterface $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->tagPartsProviderService = $container->get(TagPartsProviderService::class);

        $this->notFoundHandler = $container->get(NotFoundHandlerInterface::class);
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $tag = $args['tag'];

        if (strlen($tag) == 0) {
            return ($this->notFoundHandler)($request, $response);
        }

        $parts = $this->tagPartsProviderService->getParts($tag);

        $params = $this->buildParams(
            [
                'params' => [
                    'tag' => $tag,
                    'title' => 'Тег «' . $tag . '»', 
                    'parts' => $parts,
                ],
            ]
        );

        return $this->render($response, 'main/tags/item.twig', $params);
    }
}
