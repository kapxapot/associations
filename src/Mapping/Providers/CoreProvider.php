<?php

namespace App\Mapping\Providers;

use App\Auth\Auth;
use App\Auth\Interfaces\AuthInterface;
use App\Config\CaptchaConfig;
use App\Config\Config;
use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\NewsConfigInterface;
use App\Config\Interfaces\UserConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\Config\LocalizationConfig;
use App\Core\Interfaces\LinkerInterface;
use App\Core\Linker;
use App\Core\Serializer;
use App\Models\News;
use App\Models\Page;
use Plasticode\Auth\Interfaces as AuthCore;
use Plasticode\Config\Interfaces as ConfigCore;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouterInterface;

class CoreProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            AuthInterface::class =>
                fn (ContainerInterface $c) => new Auth(
                    $c->get(Core\SessionInterface::class)
                ),

            LinkerInterface::class =>
                fn (ContainerInterface $c) => new Linker(
                    $c->get(SettingsProviderInterface::class),
                    $c->get(RouterInterface::class),
                    $c->get(ConfigCore\TagsConfigInterface::class)
                ),

            Config::class =>
                fn (ContainerInterface $c) => new Config(
                    $c->get(SettingsProviderInterface::class)
                ),

            ConfigCore\TagsConfigInterface::class =>
                fn (ContainerInterface $c) => new \Plasticode\Config\TagsConfig(
                    [
                        News::class => 'news',
                        Page::class => 'pages',
                    ]
                ),

            ConfigCore\CaptchaConfigInterface::class =>
                fn (ContainerInterface $c) => new CaptchaConfig(),

            ConfigCore\LocalizationConfigInterface::class =>
                fn (ContainerInterface $c) => new LocalizationConfig(),

            Serializer::class =>
                fn (ContainerInterface $c) => new Serializer(),
        ];
    }

    public function getAliases(): array
    {
        return [
            AuthCore\AuthInterface::class => AuthInterface::class,
            Core\LinkerInterface::class => LinkerInterface::class,

            \Plasticode\Config\Config::class => Config::class,
            AssociationConfigInterface::class => Config::class,
            NewsConfigInterface::class => Config::class,
            UserConfigInterface::class => Config::class,
            WordConfigInterface::class => Config::class,
        ];
    }
}
