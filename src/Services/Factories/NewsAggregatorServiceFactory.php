<?php

namespace App\Services\Factories;

use App\Repositories\Interfaces\NewsRepositoryInterface;
use App\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Services\NewsAggregatorService;

class NewsAggregatorServiceFactory
{
    public function __invoke(
        LinkerInterface $linker,
        NewsRepositoryInterface $newsRepository,
        PageRepositoryInterface $pageRepository
    ): NewsAggregatorService
    {
        $service = new NewsAggregatorService($linker);

        return $service
            ->registerStrictSource($newsRepository)
            ->registerSource($pageRepository);
    }
}
