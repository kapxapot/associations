<?php

namespace Brightwood\Tests;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Answers\Answerer;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Models\Data\EightsData;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\WoodStory;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Services\StoryService;
use Brightwood\Testing\Factories\LoggerFactory;
use Brightwood\Testing\Factories\RootDeserializerFactory;
use Brightwood\Testing\Factories\TelegramUserRepositoryFactory;
use Brightwood\Testing\Mocks\Repositories\StoryRepositoryMock;
use Brightwood\Testing\Mocks\Repositories\StoryStatusRepositoryMock;
use Brightwood\Testing\Mocks\Repositories\StoryVersionRepositoryMock;
use Brightwood\Testing\Seeders\StorySeeder;
use PHPUnit\Framework\TestCase;
use Plasticode\ObjectProxy;
use Plasticode\Semantics\Gender;
use Plasticode\Util\Cases;
use Psr\Log\LoggerInterface;

final class AnswererTest extends TestCase
{
    private StoryStatusRepositoryInterface $storyStatusRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;

    private LoggerInterface $logger;

    private Answerer $answerer;

    public function setUp(): void
    {
        parent::setUp();

        $this->telegramUserRepository = TelegramUserRepositoryFactory::make();

        $woodStory = new WoodStory();

        $eightsStory = new EightsStory(
            RootDeserializerFactory::make(),
            new Cases()
        );

        $storyRepository = new StoryRepositoryMock(
            new StorySeeder($woodStory, $eightsStory)
        );

        $storyVersionRepository = new StoryVersionRepositoryMock();

        $storyService = new StoryService(
            $storyRepository,
            $woodStory,
            $eightsStory
        );

        $this->storyStatusRepository = new StoryStatusRepositoryMock(
            new ObjectProxy(
                fn () => new StoryStatusHydrator(
                    $storyVersionRepository,
                    $this->telegramUserRepository,
                    $storyService
                )
            )
        );

        $this->logger = LoggerFactory::make();

        $this->answerer = new Answerer(
            $this->storyStatusRepository,
            $this->telegramUserRepository,
            $storyService,
            $this->logger
        );
    }

    public function tearDown(): void
    {
        unset($this->answerer);
        unset($this->logger);

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
