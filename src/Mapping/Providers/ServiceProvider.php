<?php

namespace App\Mapping\Providers;

use App\Services\Factories\NewsAggregatorServiceFactory;
use App\Services\Interfaces\ExternalDictServiceInterface;
use App\Services\YandexDictService;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Services\NewsAggregatorService;

class ServiceProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            ExternalDictServiceInterface::class => YandexDictService::class,
            NewsAggregatorService::class => NewsAggregatorServiceFactory::class,
        ];
    }
}
