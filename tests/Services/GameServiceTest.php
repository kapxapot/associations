<?php

namespace App\Tests\Services;

use App\Collections\WordCollection;
use App\Models\Word;
use App\Policies\UserPolicy;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\GameService;
use App\Testing\Seeders\LanguageSeeder;
use App\Tests\WiredTest;

final class GameServiceTest extends WiredTest
{
    /**
     * The new game must be populated with AI turn on start
     * if there are any approved words in the language.
     */
    public function testNewGame(): void
    {
        /** @var GameRepositoryInterface $gameRepository */
        $gameRepository = $this->get(GameRepositoryInterface::class);

        /** @var LanguageRepositoryInterface $languageRepository */
        $languageRepository = $this->get(LanguageRepositoryInterface::class);

        /** @var TurnRepositoryInterface $turnRepository */
        $turnRepository = $this->get(TurnRepositoryInterface::class);

        /** @var UserRepositoryInterface $userRepository */
        $userRepository = $this->get(UserRepositoryInterface::class);

        /** @var WordRepositoryInterface $wordRepository */
        $wordRepository = $this->get(WordRepositoryInterface::class);

        /** @var GameService $gameService */
        $gameService = $this->get(GameService::class);

        // meat

        $language = $languageRepository->get(LanguageSeeder::RUSSIAN);

        $user = $userRepository->get(1);

        $user->withPolicy(new UserPolicy());

        // save game count to compare later
        $gameCountFunc = fn () => $gameRepository->getCountByLanguage($language);
        $gameCount = $gameCountFunc();

        // save turn count to compare later
        $turnCountFunc = fn () => $turnRepository->getCountByLanguage($language);
        $turnCount = $turnCountFunc();

        // ensure that there are approved words
        $this->assertTrue(
            $wordRepository->getAllApproved($language)->anyFirst()
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
        $word1 = $wordRepository->get(1); // стол (common)

        $this->assertTrue(
            $firstTurnWord->equals($word1)
        );

        $this->assertTrue(
            $game->containsWord($word1)
        );

        // find answer
        /** @var Word $answer */
        $answer =
            WordCollection::collect(
                $word1,
                $wordRepository->get(2),
                $wordRepository->get(3)
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
