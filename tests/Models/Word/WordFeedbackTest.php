<?php

namespace App\Tests\Models\Word;

use App\Hydrators\GameHydrator;
use App\Hydrators\TurnHydrator;
use App\Hydrators\WordFeedbackHydrator;
use App\Models\Word;
use App\Models\WordFeedback;
use App\Parsing\DefinitionParser;
use App\Repositories\Interfaces\DefinitionRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\LanguageService;
use App\Services\WordFeedbackService;
use App\Services\WordService;
use App\Testing\Factories\LanguageRepositoryFactory;
use App\Testing\Factories\WordRepositoryFactory;
use App\Testing\Mocks\Config\WordConfigMock;
use App\Testing\Mocks\LinkerMock;
use App\Testing\Mocks\Repositories\AssociationRepositoryMock;
use App\Testing\Mocks\Repositories\DefinitionRepositoryMock;
use App\Testing\Mocks\Repositories\GameRepositoryMock;
use App\Testing\Mocks\Repositories\TurnRepositoryMock;
use App\Testing\Mocks\Repositories\WordFeedbackRepositoryMock;
use App\Tests\IntegrationTest;
use Plasticode\Events\EventDispatcher;
use Plasticode\Exceptions\ValidationException;
use Plasticode\ObjectProxy;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;

final class WordFeedbackTest extends IntegrationTest
{
    private DefinitionRepositoryInterface $definitionRepository;
    private GameRepositoryInterface $gameRepository;
    private TurnRepositoryInterface $turnRepository;
    private WordFeedbackRepositoryInterface $wordFeedbackRepository;
    private WordRepositoryInterface $wordRepository;

    private WordFeedbackService $wordFeedbackService;

    public function setUp(): void
    {
        parent::setUp();

        $languageRepository = LanguageRepositoryFactory::make();

        $this->wordRepository = WordRepositoryFactory::make(
            $languageRepository
        );

        $this->definitionRepository = new DefinitionRepositoryMock(
            $this->wordRepository
        );

        $this->wordFeedbackRepository = new WordFeedbackRepositoryMock(
            new ObjectProxy(
                fn () => new WordFeedbackHydrator(
                    $this->userRepository,
                    $this->wordRepository
                )
            )
        );

        $associationRepository = new AssociationRepositoryMock();

        $this->turnRepository = new TurnRepositoryMock(
            new ObjectProxy(
                fn () => new TurnHydrator(
                    $associationRepository,
                    $this->gameRepository,
                    $this->turnRepository,
                    $this->userRepository,
                    $this->wordRepository
                )
            )
        );

        $this->gameRepository = new GameRepositoryMock(
            new ObjectProxy(
                fn () => new GameHydrator(
                    $languageRepository,
                    $this->turnRepository,
                    $this->userRepository,
                    new LinkerMock()
                )
            )
        );

        $settingsProvider = new SettingsProvider($this->settings);

        $validator = new Validator();
        $validationRules = new ValidationRules($settingsProvider);

        $eventDispatcher = new EventDispatcher();

        $wordConfig = new WordConfigMock();

        $wordService = new WordService(
            $this->definitionRepository,
            $this->turnRepository,
            $this->wordRepository,
            $validator,
            $validationRules,
            $wordConfig,
            $eventDispatcher,
            new DefinitionParser()
        );

        $languageService = new LanguageService(
            $languageRepository,
            $this->wordRepository,
            $settingsProvider,
            $wordConfig,
            $wordService
        );

        $this->wordFeedbackService = new WordFeedbackService(
            $this->wordFeedbackRepository,
            $this->wordRepository,
            $languageService,
            $wordService,
            $validator,
            $validationRules,
            $eventDispatcher
        );
    }

    public function tearDown(): void
    {
        unset($this->wordFeedbackService);

        unset($this->gameRepository);
        unset($this->turnRepository);
        unset($this->wordFeedbackRepository);
        unset($this->definitionRepository);
        unset($this->wordRepository);

        parent::tearDown();
    }

    /**
     * @dataProvider toModelProvider
     */
    public function testToModel(array $data, array $expected): void
    {
        $user = $this->getDefaultUser();
        $model = $this->wordFeedbackService->toModel($data, $user);

        $this->assertInstanceOf(WordFeedback::class, $model);
        $this->assertInstanceOf(Word::class, $model->word());

        $duplicateId = $model->duplicate()
            ? $model->duplicate()->getId()
            : null;

        $this->assertEquals(
            $expected,
            [
                $model->word()->getId(),
                $model->dislike,
                $model->hasTypo(),
                $model->typo,
                $duplicateId,
                $model->mature
            ]
        );
    }

    public function toModelProvider(): array
    {
        return [
            [
                [
                    'word_id' => '1',
                    'dislike' => 'true',
                    'typo' => 'ababa',
                    'duplicate' => 'табурет',
                    'mature' => 'true'
                ],
                [1, 1, true, 'ababa', 2, 1]
            ],
            [
                [
                    'word_id' => 1,
                ],
                [1, 0, false, null, null, 0]
            ],
        ];
    }

    public function testInvalidDataFails(): void
    {
        $this->expectException(ValidationException::class);

        $user = $this->getDefaultUser();
        $this->wordFeedbackService->toModel([], $user);
    }

    public function testTypoEqualsToWordFails(): void
    {
        $this->expectException(ValidationException::class);

        $user = $this->getDefaultUser();

        $this->wordFeedbackService->toModel(
            [
                'word_id' => '1',
                'typo' => 'стол',
            ],
            $user
        );
    }
}
