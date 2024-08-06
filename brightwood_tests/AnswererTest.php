<?php

namespace Brightwood\Tests;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Testing\Mocks\LinkerMock;
use Brightwood\Answers\Answerer;
use Brightwood\Factories\TelegramTransportFactory;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Models\Data\EightsData;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Testing\Factories\LoggerFactory;
use Brightwood\Testing\Factories\SettingsProviderFactory;
use Brightwood\Testing\Factories\StoryServiceFactory;
use Brightwood\Testing\Factories\TelegramUserRepositoryFactory;
use Brightwood\Testing\Mocks\Repositories\StoryStatusRepositoryMock;
use Brightwood\Testing\Mocks\Repositories\StoryVersionRepositoryMock;
use PHPUnit\Framework\TestCase;
use Plasticode\ObjectProxy;
use Plasticode\Semantics\Gender;

final class AnswererTest extends TestCase
{
    private StoryStatusRepositoryInterface $storyStatusRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;

    private Answerer $answerer;

    public function setUp(): void
    {
        parent::setUp();

        $settingsProvider = (new SettingsProviderFactory())();
        $storyService = StoryServiceFactory::make($settingsProvider);

        $this->telegramUserRepository = TelegramUserRepositoryFactory::make();

        $this->storyStatusRepository = new StoryStatusRepositoryMock(
            new ObjectProxy(
                fn () => new StoryStatusHydrator(
                    new StoryVersionRepositoryMock(),
                    $this->telegramUserRepository,
                    $storyService
                )
            )
        );

        $this->answerer = new Answerer(
            LoggerFactory::make(),
            $settingsProvider,
            new LinkerMock(),
            $this->storyStatusRepository,
            $storyService,
            new TelegramTransportFactory($settingsProvider)
        );
    }

    public function tearDown(): void
    {
        unset($this->answerer);

        unset($this->storyStatusRepository);
        unset($this->telegramUserRepository);

        parent::tearDown();
    }

    public function testDebug1(): void
    {
        $tgUser = $this->telegramUserRepository->store([
            'id' => 2,
            'username' => 'kapxapot',
            'gender_id' => Gender::MAS
        ]);

        $this->storyStatusRepository->store([
            'id' => 15,
            'telegram_user_id' => $tgUser->getId(),
            'story_id' => EightsStory::ID,
            'step_id' => 8,
            'json_data' => file_get_contents('brightwood_tests/Files/eights_data_debug1.json')
        ]);

        $answers = $this->answerer->getAnswers($tgUser, '♻ Начать заново');

        $this->assertInstanceOf(StoryMessageSequence::class, $answers);
        $this->assertTrue($answers->hasText());
    }

    public function testDebug2(): void
    {
        $tgUser = $this->telegramUserRepository->store([
            'id' => 31,
            'username' => 'kapxapot',
            'gender_id' => Gender::MAS
        ]);

        $this->storyStatusRepository->store([
            'id' => 24,
            'telegram_user_id' => $tgUser->getId(),
            'story_id' => EightsStory::ID,
            'step_id' => 5,
            'json_data' => file_get_contents('brightwood_tests/Files/eights_data_debug2.json')
        ]);

        $answers = $this->answerer->getAnswers($tgUser, '4');

        $this->assertInstanceOf(StoryMessageSequence::class, $answers);
        $this->assertTrue($answers->hasText());

        $this->assertEquals('Раздаем по 4 карты', $answers->messages()[2]->lines()[0]);

        $this->assertInstanceOf(EightsData::class, $answers->data());

        /** @var EightsData */
        $eightsData = $answers->data();

        $this->assertEquals(4, $eightsData->playerCount);
    }
}
