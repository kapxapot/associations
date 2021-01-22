<?php

namespace Brightwood\Mapping\Providers;

use Brightwood\External\TelegramTransport;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;

class ExternalProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            TelegramTransport::class =>
                fn (ContainerInterface $c) => new TelegramTransport(
                    $c
                        ->get(SettingsProviderInterface::class)
                        ->get('telegram.brightwood_bot_token')
                ),
        ];
    }
}
