<?php

namespace App\Controllers;

use App\Handlers\NotFoundHandler;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use Plasticode\Controllers\Traits\NewsPageDescription;
use Plasticode\Services\NewsAggregatorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;

class NewsController extends Controller
{
    use NewsPageDescription;

    private NewsRepositoryInterface $newsRepository;
    private NewsAggregatorService $newsAggregatorService;
    private NotFoundHandler $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->newsRepository = $container->newsRepository;
        $this->newsAggregatorService = $container->newsAggregatorService;
        $this->notFoundHandler = $container->notFoundHandler;
    }

    public function __invoke(
        Request $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];

        $news = $this->newsRepository->get($id);

        if (!$news) {
            return ($this->notFoundHandler)($request, $response);
        }

        $prev = $this->newsAggregatorService->getPrev($news);
        $next = $this->newsAggregatorService->getNext($news);

        $params = $this->buildParams(
            [
                'large_image' => $news->largeImage(),
                'image' => $news->image(),
                'params' => [
                    'news' => $news,
                    'title' => $news->displayTitle(),
                    'page_description' => $this->makeNewsPageDescription($news),
                    'news_prev' => $prev,
                    'news_next' => $next,
                    'rel_prev' => $prev ? $prev->url() : null,
                    'rel_next' => $next ? $next->url() : null,
                    'canonical_url' => $this->linker->abs($news->url()),
                    'disqus_id' => 'news' . $id,
                ],
            ]
        );

        return $this->render($response, 'main/news/item.twig', $params);
    }
}
