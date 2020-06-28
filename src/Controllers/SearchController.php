<?php

namespace App\Controllers;

use App\Services\SearchService;
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

        $this->searchService = $container->searchService;
    }

    public function search(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $query = $args['query'];

        $result = $this->searchService->search($query);

        return Response::json($response, $result);
    }
}
