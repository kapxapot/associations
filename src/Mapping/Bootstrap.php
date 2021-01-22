<?php

namespace App\Mapping;

use Plasticode\Mapping\MappingAggregator;
use Psr\Container\ContainerInterface;
use Slim\Container;

class Bootstrap extends MappingAggregator
{
    public function __construct(array $settings)
    {
        $this->registerMany(
            new \Plasticode\Mapping\Providers($settings),
            new \Plasticode\Data\Idiorm\Providers(),
            new \App\Mapping\Providers(),
            new \Brightwood\Mapping\Providers()
        );
    }

    /**
     * @param Container $container Slim container.
     */
    protected function wireUpContainer(ContainerInterface $container): void
    {
        foreach ($this->getMappings() as $key => $value) {
            $container[$key] = $value;
        }
    }
}
