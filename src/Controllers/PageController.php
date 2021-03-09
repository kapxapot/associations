<?php

namespace App\Controllers;

use App\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Controllers\Traits\NewsPageDescription;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PageController extends Controller
{
    use NewsPageDescription;

    private PageRepositoryInterface $pageRepository;

    private NotFoundHandlerInterface $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->pageRepository = $container->get(PageRepositoryInterface::class);

        $this->notFoundHandler = $container->get(NotFoundHandlerInterface::class);
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $slug = $args['slug'];

        $page = $this->pageRepository->getBySlug($slug);

        if (!$page) {
            return ($this->notFoundHandler)($request, $response);
        }

        $params = $this->buildParams(
            [
                'large_image' => $page->largeImage(),
                'image' => $page->image(),
                'params' => [
                    'page' => $page,
                    'title' => $page->displayTitle(),
                    'breadcrumbs' => $page->breadcrumbs(),
                    'page_description' => $this->makeNewsPageDescription($page),
                    'canonical_url' => $this->linker->abs($page->url()),
                    'disqus_id' => 'page' . $page->getId(),
                ],
            ]
        );

        return $this->render($response, 'main/pages/item.twig', $params);
    }
}
