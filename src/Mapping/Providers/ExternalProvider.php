<?php

namespace App\Mapping\Providers;

use App\External\YandexDict;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;

class ExternalProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            YandexDict::class =>
                fn (ContainerInterface $c) => new YandexDict(
                    $c->get(SettingsProviderInterface::class)->get('yandex_dict.key')
                ),
        ];
    }
}
