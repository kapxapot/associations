<?php

namespace App\Controllers;

use App\Services\SearchService;
use Plasticode\Collections\Generic\ArrayCollection;
use Plasticode\Core\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SearchController extends Controller
{
    private SearchService $searchService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->searchService = $container->get(SearchService::class);
    }

    public function search(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $query = $args['query'];

        $result = (strlen($query) > 0)
            ? $this->searchService->search($query)
            : ArrayCollection::empty();

        return Response::json($response, $result);
    }
}
