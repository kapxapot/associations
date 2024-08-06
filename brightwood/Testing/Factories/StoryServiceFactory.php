<?php

namespace Brightwood\Testing\Factories;

use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Repositories\StaticStoryRepository;
use Brightwood\Services\StoryService;
use Brightwood\Services\TelegramUserService;
use Brightwood\Testing\Mocks\Repositories\StoryRepositoryMock;
use Brightwood\Testing\Seeders\StorySeeder;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Cases;

class StoryServiceFactory
{
    public static function make(SettingsProviderInterface $settingsProvider): StoryService
    {
        $woodStory = new WoodStory();

        $eightsStory = new EightsStory(
            RootDeserializerFactory::make(),
            new Cases()
        );

        $storyRepository = new StoryRepositoryMock(
            new TelegramUserService($settingsProvider),
            new StorySeeder($woodStory, $eightsStory)
        );

        $staticStoryRepository = new StaticStoryRepository(
            $woodStory,
            $eightsStory
        );

        return new StoryService(
            $staticStoryRepository,
            $storyRepository
        );
    }
}
