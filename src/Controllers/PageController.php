<?php

namespace App\Controllers;

use App\Handlers\NotFoundHandler;
use App\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Controllers\Traits\NewsPageDescription;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

class PageController extends Controller
{
    use NewsPageDescription;

    private PageRepositoryInterface $pageRepository;
    private NotFoundHandler $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->pageRepository = $container->pageRepository;
        $this->notFoundHandler = $container->notFoundHandler;
    }

    public function get(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
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
