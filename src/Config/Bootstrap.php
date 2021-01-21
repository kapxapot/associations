<?php

namespace App\Config;

use App\External\YandexDict;
use App\Factories\LoadUncheckedDictWordsJobFactory;
use App\Factories\MatchDanglingDictWordsJobFactory;
use App\Factories\UpdateAssociationsJobFactory;
use App\Factories\UpdateWordsJobFactory;
use App\Handlers\NotFoundHandler;
use App\Mapping\Providers\CoreProvider;
use App\Mapping\Providers\EventProvider;
use App\Mapping\Providers\GeneratorProvider;
use App\Mapping\Providers\ParsingProvider;
use App\Mapping\Providers\RepositoryProvider;
use App\Mapping\Providers\ServiceProvider;
use App\Mapping\Providers\ValidationProvider;
use App\Specifications\AssociationSpecification;
use App\Specifications\WordSpecification;
use Brightwood\Config\Bootstrap as BrightwoodBootstrap;
use Plasticode\Mapping\Providers as CoreProviders;
use Plasticode\Events\EventDispatcher;
use Plasticode\Mapping\MappingAggregator;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;
use Slim\Container;

class Bootstrap extends MappingAggregator
{
    public function __construct(array $settings)
    {
        $this->register(
            new CoreProviders\SlimProvider(),
            new CoreProviders\CoreProvider($settings),
            new CoreProviders\GeneratorProvider(),
            new CoreProviders\ValidationProvider(),
            new CoreProviders\ParsingProvider(),
            new \Plasticode\Data\Idiorm\DatabaseProvider(),
            new \Plasticode\Data\Idiorm\RepositoryProvider(),
            new CoreProvider(),
            new GeneratorProvider(),
            new ValidationProvider(),
            new ParsingProvider(),
            new RepositoryProvider(),
            new ServiceProvider(),
            new EventProvider()
        );
    }

    /**
     * @param Container $container
     */
    protected function wireUpContainer(ContainerInterface $container): void
    {
        foreach ($this->getMappings() as $key => $value) {
            $container[$key] = $value;
        }
    }

    public function getMappings(): array
    {
        $map['associationSpecification'] = fn (ContainerInterface $c) =>
            new AssociationSpecification(
                $c->config
            );

        $map['wordSpecification'] = fn (ContainerInterface $c) =>
            new WordSpecification(
                $c->config
            );

        // factories

        $map['loadUncheckedDictWordsJobFactory'] = fn (ContainerInterface $c) =>
            new LoadUncheckedDictWordsJobFactory(
                $c->wordRepository,
                $c->get(SettingsProviderInterface::class),
                $c->dictionaryService
            );

        $map['matchDanglingDictWordsJobFactory'] = fn (ContainerInterface $c) =>
            new MatchDanglingDictWordsJobFactory(
                $c->dictWordRepository,
                $c->wordRepository,
                $c->dictionaryService,
                $c->get(SettingsProviderInterface::class)
            );

        $map['updateAssociationsJobFactory'] = fn (ContainerInterface $c) =>
            new UpdateAssociationsJobFactory(
                $c->associationRepository,
                $c->get(SettingsProviderInterface::class),
                $c->get(EventDispatcher::class)
            );

        $map['updateWordsJobFactory'] = fn (ContainerInterface $c) =>
            new UpdateWordsJobFactory(
                $c->wordRepository,
                $c->get(SettingsProviderInterface::class),
                $c->get(EventDispatcher::class)
            );

        // external

        $map['yandexDict'] = fn (ContainerInterface $c) =>
            new YandexDict(
                $this->settings['yandex_dict']['key']
            );

        // handlers

        $map['notFoundHandler'] = fn (ContainerInterface $c) =>
            new NotFoundHandler($c);

        // Brightwood

        $map = $this->addBrightwood($map);

        return $map;
    }

    private function addBrightwood(array $map): array
    {
        $brightwoodBootstrap = new BrightwoodBootstrap();

        return array_merge(
            $map,
            $brightwoodBootstrap->getMappings($this->settings)
        );
    }
}
