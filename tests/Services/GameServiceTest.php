<?php

namespace App\Tests\Services;

use App\Collections\WordCollection;
use App\Hydrators\GameHydrator;
use App\Hydrators\TurnHydrator;
use App\Models\Word;
use App\Parsing\DefinitionParser;
use App\Policies\UserPolicy;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\AssociationService;
use App\Services\CasesService;
use App\Services\GameService;
use App\Services\LanguageService;
use App\Services\TurnService;
use App\Services\WordService;
use App\Testing\Factories\LanguageRepositoryFactory;
use App\Testing\Factories\UserRepositoryFactory;
use App\Testing\Factories\WordRepositoryFactory;
use App\Testing\Mocks\Config\WordConfigMock;
use App\Testing\Mocks\LinkerMock;
use App\Testing\Mocks\Repositories\AssociationRepositoryMock;
use App\Testing\Mocks\Repositories\GameRepositoryMock;
use App\Testing\Mocks\Repositories\TurnRepositoryMock;
use PHPUnit\Framework\TestCase;
use Plasticode\Events\EventDispatcher;
use Plasticode\ObjectProxy;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Util\Cases;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;

class GameServiceTest extends TestCase
{
    private AssociationRepositoryInterface $associationRepository;
    private GameRepositoryInterface $gameRepository;
    private LanguageRepositoryInterface $languageRepository;
    private TurnRepositoryInterface $turnRepository;
    private UserRepositoryInterface $userRepository;
    private WordRepositoryInterface $wordRepository;

    public function setUp() : void
    {
        parent::setUp();

        $this->languageRepository = LanguageRepositoryFactory::make();
        $this->associationRepository = new AssociationRepositoryMock();

        $this->wordRepository = WordRepositoryFactory::make(
            $this->languageRepository
        );

        $this->userRepository = UserRepositoryFactory::make();

        $this->turnRepository = new TurnRepositoryMock(
            new ObjectProxy(
                fn () => new TurnHydrator(
                    $this->associationRepository,
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
                    $this->languageRepository,
                    $this->turnRepository,
                    $this->userRepository,
                    new LinkerMock()
                )
            )
        );
    }

    public function tearDown() : void
    {
        parent::tearDown();

        unset($this->gameRepository);
        unset($this->turnRepository);
        unset($this->userRepository);
        unset($this->wordRepository);
        unset($this->associationRepository);
        unset($this->languageRepository);
    }

    /**
     * The new game must be populated with AI turn on start
     * if there are any approved words in the language.
     */
    public function testNewGame() : void
    {
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

        $languageService = new LanguageService(
            $this->languageRepository,
            $this->wordRepository,
            $settingsProvider,
            $wordService
        );

        $associationService = new AssociationService(
            $this->associationRepository,
            $eventDispatcher
        );

        $turnService = new TurnService(
            $this->gameRepository,
            $this->turnRepository,
            $this->wordRepository,
            $associationService,
            $eventDispatcher
        );

        $gameService = new GameService(
            $this->gameRepository,
            $languageService,
            $turnService,
            $wordService
        );

        // the meat
        $language = $this->languageRepository->get(1);
        $user = $this->userRepository->get(1);

        $user->withPolicy(new UserPolicy());

        // save game count to compare later
        $gameCountFunc = fn () => $this->gameRepository->getCountByLanguage($language);
        $gameCount = $gameCountFunc();

        // save turn count to compare later
        $turnCountFunc = fn () => $this->turnRepository->getCountByLanguage($language);
        $turnCount = $turnCountFunc();

        // ensure that there are approved words
        $this->assertGreaterThan(
            0,
            $this->wordRepository
                ->getAllApproved($language)
                ->any()
        );

        // the test
        $game = $gameService->createGameFor($user, $language);

        // test counts
        $gameCountAfter = $gameCountFunc();
        $turnCountAfter = $turnCountFunc();

        $this->assertEquals(
            $gameCount + 1,
            $gameCountAfter
        );

        $this->assertEquals(
            $turnCount + 1,
            $turnCountAfter
        );

        // test game
        $this->assertNotNull($game);
        $this->assertGreaterThan(0, $game->getId());
        $this->assertCount(1, $game->turns());

        $firstTurn = $game->turns()->first();

        $this->assertNotNull($firstTurn);

        $firstTurnWord = $firstTurn->word();
        $word1 = $this->wordRepository->get(1); // стол (approved)

        $this->assertTrue(
            $firstTurnWord->equals($word1)
        );

        $this->assertTrue(
            $game->containsWord($word1)
        );

        // find answer
        /** @var Word */
        $answer =
            WordCollection::collect(
                $word1,
                $this->wordRepository->get(2),
                $this->wordRepository->get(3)
            )
            ->where(
                fn (Word $w) => !$game->containsWord($w)
            )
            ->random();

        $this->assertFalse(
            $answer->equals($word1)
        );
    }
}
