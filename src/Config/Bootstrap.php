<?php

namespace App\Config;

use App\Core\Linker;
use App\Core\Serializer;
use App\External\YandexDict;
use App\Factories\LoadUncheckedDictWordsJobFactory;
use App\Factories\MatchDanglingDictWordsJobFactory;
use App\Factories\UpdateAssociationsJobFactory;
use App\Factories\UpdateWordsJobFactory;
use App\Handlers\NotFoundHandler;
use App\Mapping\Providers\CoreProvider;
use App\Mapping\Providers\EventProvider;
use App\Mapping\Providers\GeneratorProvider;
use App\Mapping\Providers\RepositoryProvider;
use App\Mapping\Providers\ServiceProvider;
use App\Models\News;
use App\Models\Page;
use App\Models\Validation\AgeValidation;
use App\Models\Validation\UserValidation;
use App\Specifications\AssociationSpecification;
use App\Specifications\WordSpecification;
use Brightwood\Config\Bootstrap as BrightwoodBootstrap;
use Plasticode\Mapping\Providers as CoreProviders;
use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Config\TagsConfig;
use Plasticode\Data\Idiorm\DatabaseProvider;
use Plasticode\Events\EventDispatcher;
use Plasticode\Mapping\MappingAggregator;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
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
            new CoreProviders\RepositoryProvider(),
            new CoreProviders\ValidatorProvider(),
            new CoreProviders\ParsingProvider(),
            new CoreProvider(),
            new DatabaseProvider(),
            new GeneratorProvider(),
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
        $map['config'] = fn (ContainerInterface $c) =>
            new Config(
                $c->get(SettingsProviderInterface::class)
            );

        $map['captchaConfig'] = fn (ContainerInterface $c) =>
            new CaptchaConfig();

        $map['localizationConfig'] = fn (ContainerInterface $c) =>
            new LocalizationConfig();

        $map['tagsConfig'] = fn (ContainerInterface $c) =>
            new TagsConfig(
                [
                    News::class => 'news',
                    Page::class => 'pages',
                ]
            );

        $map['linker'] = fn (ContainerInterface $c) =>
            new Linker(
                $c->get(SettingsProviderInterface::class),
                $c->router,
                $c->tagsConfig
            );

        $map['serializer'] = fn (ContainerInterface $c) =>
            new Serializer();

        $map['tagLinkMapper'] = fn (ContainerInterface $c) =>
            new TagLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['pageLinkMapper'] = fn (ContainerInterface $c) =>
            new PageLinkMapper(
                $c->pageRepository,
                $c->tagRepository,
                $c->renderer,
                $c->linker,
                $c->tagLinkMapper
            );

        $map['newsLinkMapper'] = fn (ContainerInterface $c) =>
            new NewsLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map[DoubleBracketsConfig::class] = function (ContainerInterface $c) {
            $config = new DoubleBracketsConfig();

            $config->setDefaultMapper($c->pageLinkMapper);
            
            $config->registerTaggedMappers(
                $c->newsLinkMapper,
                $c->tagLinkMapper
            );

            return $config;
        };

        $map['ageValidation'] = fn (ContainerInterface $c) =>
            new AgeValidation(
                $c->validationRules
            );

        $map['userValidation'] = fn (ContainerInterface $c) =>
            new UserValidation(
                $c->validationRules,
                $c->ageValidation,
                $c->userRepository
            );

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
