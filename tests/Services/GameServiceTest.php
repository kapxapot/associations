<?php

namespace App\Tests\Services;

use App\Hydrators\GameHydrator;
use App\Hydrators\TurnHydrator;
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
use App\Testing\Mocks\Config\WordConfigMock;
use App\Testing\Mocks\LinkerMock;
use App\Testing\Mocks\Repositories\AssociationRepositoryMock;
use App\Testing\Mocks\Repositories\GameRepositoryMock;
use App\Testing\Mocks\Repositories\LanguageRepositoryMock;
use App\Testing\Mocks\Repositories\TurnRepositoryMock;
use App\Testing\Mocks\Repositories\UserRepositoryMock;
use App\Testing\Mocks\Repositories\WordRepositoryMock;
use App\Testing\Mocks\SettingsProviderMock;
use App\Testing\Seeders\LanguageSeeder;
use App\Testing\Seeders\UserSeeder;
use App\Testing\Seeders\WordSeeder;
use PHPUnit\Framework\TestCase;
use Plasticode\Core\Translator;
use Plasticode\Events\EventDispatcher;
use Plasticode\ObjectProxy;
use Plasticode\Util\Cases;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;
use Slim\Container;

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

        $this->languageRepository = new LanguageRepositoryMock(
            new LanguageSeeder()
        );

        $this->associationRepository = new AssociationRepositoryMock();

        $this->wordRepository = new WordRepositoryMock(
            new WordSeeder(
                $this->languageRepository
            )
        );

        $this->userRepository = new UserRepositoryMock(
            new UserSeeder()
        );

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

        $validator = new Validator(
            new Container(),
            new Translator([])
        );

        $settingsProvider = new SettingsProviderMock();
        $eventDispatcher = new EventDispatcher();

        $wordService = new WordService(
            $this->turnRepository,
            $this->wordRepository,
            $casesService,
            $validator,
            new ValidationRules($settingsProvider),
            new WordConfigMock(),
            $eventDispatcher
        );

        $languageService = new LanguageService(
            $this->languageRepository,
            $this->wordRepository,
            $settingsProvider,
            $wordService
        );

        $associationService = new AssociationService(
            $this->associationRepository
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

        $this->assertTrue(
            $firstTurn->word()->equals(
                $this->wordRepository->get(1) // стол (approved)
            )
        );
    }
}
