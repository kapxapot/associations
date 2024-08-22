<?php

namespace Brightwood\Testing\Factories;

use App\Testing\Factories\UserRepositoryFactory;
use Brightwood\Hydrators\StoryCandidateHydrator;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Repositories\StaticStoryRepository;
use Brightwood\Services\StoryService;
use Brightwood\Services\TelegramUserService;
use Brightwood\Testing\Mocks\Repositories\StoryCandidateRepositoryMock;
use Brightwood\Testing\Mocks\Repositories\StoryRepositoryMock;
use Brightwood\Testing\Mocks\Repositories\StoryVersionRepositoryMock;
use Brightwood\Testing\Seeders\StorySeeder;
use Plasticode\ObjectProxy;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Cases;

class StoryServiceTestFactory
{
    public static function make(SettingsProviderInterface $settingsProvider): StoryService
    {
        $woodStory = new WoodStory();

        $eightsStory = new EightsStory(
            RootDeserializerTestFactory::make(),
            new Cases()
        );

        $telegramUserService = new TelegramUserService($settingsProvider);

        $storyRepository = new StoryRepositoryMock(
            $telegramUserService,
            new StorySeeder($woodStory, $eightsStory)
        );

        $staticStoryRepository = new StaticStoryRepository(
            $woodStory,
            $eightsStory
        );

        $storyCandidateRepository = new StoryCandidateRepositoryMock(
            new ObjectProxy(
                fn () => new StoryCandidateHydrator(
                    UserRepositoryFactory::make()
                )
            )
        );

        return new StoryService(
            $staticStoryRepository,
            $storyRepository,
            $storyCandidateRepository,
            new StoryVersionRepositoryMock(),
            $telegramUserService
        );
    }
}
