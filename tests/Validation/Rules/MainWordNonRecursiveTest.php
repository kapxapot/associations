<?php

namespace App\Tests\Validation\Rules;

use App\Hydrators\GameHydrator;
use App\Hydrators\TurnHydrator;
use App\Models\Language;
use App\Models\Word;
use App\Parsing\DefinitionParser;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\CasesService;
use App\Services\LanguageService;
use App\Services\WordService;
use App\Testing\Factories\LanguageRepositoryFactory;
use App\Testing\Factories\UserRepositoryFactory;
use App\Testing\Factories\WordRepositoryFactory;
use App\Testing\Mocks\Config\WordConfigMock;
use App\Testing\Mocks\LinkerMock;
use App\Testing\Mocks\Repositories\AssociationRepositoryMock;
use App\Testing\Mocks\Repositories\GameRepositoryMock;
use App\Testing\Mocks\Repositories\TurnRepositoryMock;
use App\Validation\Rules\MainWordNonRecursive;
use PHPUnit\Framework\TestCase;
use Plasticode\Events\EventDispatcher;
use Plasticode\ObjectProxy;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Util\Cases;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;

final class MainWordNonRecursiveTest extends TestCase
{
    private GameRepositoryInterface $gameRepository;
    private TurnRepositoryInterface $turnRepository;
    private WordRepositoryInterface $wordRepository;

    private LanguageService $languageService;

    private Language $language;

    private Word $word1;
    private Word $word2;
    private Word $word3;
    private Word $mainWord;

    public function setUp(): void
    {
        parent::setUp();

        $languageRepository = LanguageRepositoryFactory::make();
        $associationRepository = new AssociationRepositoryMock();

        $this->wordRepository = WordRepositoryFactory::make(
            $languageRepository
        );

        $userRepository = UserRepositoryFactory::make();

        $this->turnRepository = new TurnRepositoryMock(
            new ObjectProxy(
                fn () => new TurnHydrator(
                    $associationRepository,
                    $this->gameRepository,
                    $this->turnRepository,
                    $userRepository,
                    $this->wordRepository
                )
            )
        );

        $this->gameRepository = new GameRepositoryMock(
            new ObjectProxy(
                fn () => new GameHydrator(
                    $languageRepository,
                    $this->turnRepository,
                    $userRepository,
                    new LinkerMock()
                )
            )
        );

        $casesService = new CasesService(
            new Cases()
        );

        $validator = new Validator();

        $settingsProvider = new SettingsProvider(); // dummy
        $eventDispatcher = new EventDispatcher();

        $wordService = new WordService(
            $this->turnRepository,
            $this->wordRepository,
            $casesService,
            $validator,
            new ValidationRules($settingsProvider),
            new WordConfigMock(),
            $eventDispatcher,
            new DefinitionParser()
        );

        $this->languageService = new LanguageService(
            $languageRepository,
            $this->wordRepository,
            $settingsProvider,
            $wordService
        );

        // init words

        $this->language = $languageRepository->get(Language::RUSSIAN);

        $this->word1 = $this->wordRepository->store(['word' => 'word1']);
        $this->word1->withLanguage($this->language);

        $this->word2 = $this->wordRepository->store(['word' => 'word2']);
        $this->word2->withLanguage($this->language);

        $this->word3 = $this->wordRepository->store(['word' => 'word3']);
        $this->word3->withLanguage($this->language);

        $this->mainWord = $this->wordRepository->store(['word' => 'main word']);
        $this->mainWord->withLanguage($this->language);

        $this->word1->withMain($this->word2);
        $this->word2->withMain($this->word3);
        $this->word3->withMain(null);
        $this->mainWord->withMain(null);
    }

    public function tearDown(): void
    {
        unset($this->mainWord);
        unset($this->word2);
        unset($this->word1);

        unset($this->language);

        unset($this->languageService);

        unset($this->gameRepository);
        unset($this->turnRepository);
        unset($this->wordRepository);

        parent::tearDown();
    }

    /** @dataProvider nonRecursiveProvider */
    public function testNonRecursive(
        string $dependentWordStr,
        ?string $mainWordStr,
        bool $expected
    ): void
    {
        $dependentWord = $this->languageService->findWord(
            $this->language,
            $dependentWordStr
        );

        $rule = new MainWordNonRecursive($this->languageService, $dependentWord);

        $this->assertEquals($expected, $rule->validate($mainWordStr));
    }

    public function nonRecursiveProvider(): array
    {
        // ok: word1 +> null
        // ok: word1 +> mainWord
        // ok: word1 -> word2 +> mainWord
        // not ok: word1 +> word1
        // not ok: word1 -> word2 +> word1
        // not ok: word1 -> word2 -> word3 +> word1

        return [
            ['word1', null, true],
            ['word1', 'main word', true],
            ['word2', 'main word', true],
            ['word1', 'word1', false],
            ['word2', 'word1', false],
            ['word3', 'word1', false],
        ];
    }
}
