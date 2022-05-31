<?php

namespace App\Mapping\Providers;

use Plasticode\Collections\Generic\Collection;
use Plasticode\Data\Query;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Generic\Model;
use Plasticode\ObjectProxy;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Util\Arrays;
use Plasticode\Util\Classes;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;

class QueryLogProvider extends MappingProvider
{
    public function boot(ContainerInterface $container): void
    {
        parent::boot($container);

        Query::enableLog(function (array $caller) {
            $class = $caller['class'] ?? null;
            $function = $caller['function'] ?? null;

            return $class !== null
                && !Classes::isOrSubClassOf(
                    $class,
                    IdiormRepository::class,
                    Collection::class,
                    DbModel::class,
                    Model::class,
                    ObjectProxy::class,
                    SortStep::class,
                    Sort::class,
                    Arrays::class
                )
                && !Strings::endsWith($function, '{closure}');
        });
    }
}
