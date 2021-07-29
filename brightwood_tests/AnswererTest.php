<?php

namespace Brightwood\Tests;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Testing\Mocks\Repositories\TelegramUserRepositoryMock;
use Brightwood\Answers\Answerer;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Repositories\StoryRepository;
use Brightwood\Testing\Factories\LoggerFactory;
use Brightwood\Testing\Factories\RootDeserializerFactory;
use Brightwood\Testing\Mocks\Repositories\StoryStatusRepositoryMock;
use PHPUnit\Framework\TestCase;
use Plasticode\Semantics\Gender;
use Psr\Log\LoggerInterface;

final class AnswererTest extends TestCase
{
    private StoryRepositoryInterface $storyRepository;
    private StoryStatusRepositoryInterface $storyStatusRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;

    private LoggerInterface $logger;

    private Answerer $answerer;

    public function setUp() : void
    {
        parent::setUp();

        $this->storyStatusRepository = new StoryStatusRepositoryMock();
        $this->telegramUserRepository = new TelegramUserRepositoryMock();

        $this->storyRepository = new StoryRepository(
            RootDeserializerFactory::make($this->telegramUserRepository)
        );

        $this->logger = LoggerFactory::make();

        $this->answerer = new Answerer(
            $this->storyRepository,
            $this->storyStatusRepository,
            $this->telegramUserRepository,
            $this->logger
        );
    }

    public function tearDown() : void
    {
        unset($this->answerer);
        unset($this->logger);
        unset($this->storyRepository);
        unset($this->telegramUserRepository);
        unset($this->storyStatusRepository);

        parent::tearDown();
    }

    public function testDebug1() : void
    {
        $tgUser = $this->telegramUserRepository->store(
            [
                'id' => 2,
                'username' => 'kapxapot',
                'gender_id' => Gender::MAS
            ]
        );

        $status = $this->storyStatusRepository->store(
            [
                'id' => 15,
                'telegram_user_id' => $tgUser->getId(),
                'story_id' => 3,
                'step_id' => 8,
                'json_data' => file_get_contents('brightwood_tests/Files/eights_data_debug1.json')
            ]
        );

        $answers = $this->answerer->getAnswers($tgUser, '♻ Начать заново');

        $this->assertInstanceOf(StoryMessageSequence::class, $answers);
        $this->assertTrue($answers->hasText());
    }
}
