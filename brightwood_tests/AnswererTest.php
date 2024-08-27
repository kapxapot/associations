<?php

namespace Brightwood\Tests;

use App\Models\Language;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use App\Testing\Mocks\LinkerMock;
use Brightwood\Answers\AnswererFactory;
use Brightwood\Factories\TelegramTransportFactory;
use Brightwood\Hydrators\StoryStatusHydrator;
use Brightwood\Models\Data\EightsData;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Parsing\StoryParserFactory;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Testing\Factories\LoggerTestFactory;
use Brightwood\Testing\Factories\SettingsProviderTestFactory;
use Brightwood\Testing\Factories\StoryServiceTestFactory;
use Brightwood\Testing\Factories\TelegramUserRepositoryTestFactory;
use Brightwood\Testing\Factories\TranslatorTestFactory;
use Brightwood\Testing\Mocks\Repositories\StoryStatusRepositoryMock;
use Brightwood\Testing\Mocks\Repositories\StoryVersionRepositoryMock;
use PHPUnit\Framework\TestCase;
use Plasticode\ObjectProxy;
use Plasticode\Semantics\Gender;

final class AnswererTest extends TestCase
{
    private StoryStatusRepositoryInterface $storyStatusRepository;
    private TelegramUserRepositoryInterface $telegramUserRepository;

    private AnswererFactory $answererFactory;

    public function setUp(): void
    {
        parent::setUp();

        $settingsProvider = SettingsProviderTestFactory::make();
        $storyService = StoryServiceTestFactory::make($settingsProvider);

        $this->telegramUserRepository = TelegramUserRepositoryTestFactory::make();

        $storyVersionRepository = new StoryVersionRepositoryMock();

        $this->storyStatusRepository = new StoryStatusRepositoryMock(
            new ObjectProxy(
                fn () => new StoryStatusHydrator(
                    $storyVersionRepository,
                    $this->telegramUserRepository,
                    $storyService
                )
            )
        );

        $storyParserFactory = new StoryParserFactory(
            new TranslatorTestFactory()
        );

        $this->answererFactory = new AnswererFactory(
            LoggerTestFactory::make(),
            $settingsProvider,
            new LinkerMock(),
            $this->storyStatusRepository,
            $storyService,
            ($storyParserFactory)(),
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
            'gender_id' => Gender::MAS,
            'lang_code' => Language::RU
        ]);

        $this->storyStatusRepository->store([
            'id' => 15,
            'telegram_user_id' => $tgUser->getId(),
            'story_id' => EightsStory::ID,
            'step_id' => 8,
            'json_data' => file_get_contents('brightwood_tests/Files/eights_data_debug1.json')
        ]);

        $answerer = ($this->answererFactory)($tgUser, Language::RU);
        $answers = $answerer->getAnswers('♻ Начать заново');

        $this->assertInstanceOf(StoryMessageSequence::class, $answers);
        $this->assertTrue($answers->hasText());
    }

    public function testDebug2(): void
    {
        $tgUser = $this->telegramUserRepository->store([
            'id' => 31,
            'username' => 'kapxapot',
            'gender_id' => Gender::MAS,
            'lang_code' => Language::RU
        ]);

        $this->storyStatusRepository->store([
            'id' => 24,
            'telegram_user_id' => $tgUser->getId(),
            'story_id' => EightsStory::ID,
            'step_id' => 5,
            'json_data' => file_get_contents('brightwood_tests/Files/eights_data_debug2.json')
        ]);

        $answerer = ($this->answererFactory)($tgUser, Language::RU);
        $answers = $answerer->getAnswers('4');

        $this->assertInstanceOf(StoryMessageSequence::class, $answers);
        $this->assertTrue($answers->hasText());

        $this->assertEquals('Раздаем по 4 карты', $answers->messages()[2]->lines()[0]);

        $this->assertInstanceOf(EightsData::class, $answers->data());

        /** @var EightsData */
        $eightsData = $answers->data();

        $this->assertEquals(4, $eightsData->playerCount);
    }
}
