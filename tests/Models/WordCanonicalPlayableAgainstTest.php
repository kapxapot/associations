<?php

namespace App\Tests\Models;

use App\Collections\TurnCollection;
use App\Collections\WordFeedbackCollection;
use App\Models\User;
use App\Models\Word;
use App\Models\WordFeedback;
use App\Policies\UserPolicy;
use PHPUnit\Framework\TestCase;

final class WordCanonicalPlayableAgainstTest extends TestCase
{
    // test cases:
    //
    // - pl1 -> pl2 => pl2
    // - pl1 -> upl2 => pl1
    // - pl1 -> pl2 -> pl3 => pl3
    // - pl1 -> pl2 -> upl3 => pl2

    /**
     * upl1 => null
     */
    public function testUpl1(): void
    {
        $user = (new User(['id' => 1]))
            ->withPolicy(
                (new UserPolicy())->withCanSeeAllWords(true)
            );

        $upl1 = (new Word(['id' => 1, 'approved' => 1]))
            ->withMain(null)
            ->withTurns(
                TurnCollection::empty()
            )
            ->withFeedbacks(
                WordFeedbackCollection::collect(
                    new WordFeedback(['created_by' => $user->getId(), 'dislike' => 1])
                )
            );

        $this->assertFalse(
            $upl1->isPlayableAgainst($user)
        );

        $canonical = $upl1->canonicalPlayableAgainst($user);

        $this->assertNull($canonical);
    }

    /**
     * pl1 => pl1
     */
    public function testPl1(): void
    {
        $user = (new User(['id' => 1]))
            ->withPolicy(
                (new UserPolicy())->withCanSeeAllWords(true)
            );

        $pl1 = (new Word(['id' => 1, 'approved' => 1]))
            ->withMain(null)
            ->withTurns(
                TurnCollection::empty()
            )
            ->withFeedbacks(
                WordFeedbackCollection::empty()
            );

        $this->assertTrue(
            $pl1->isPlayableAgainst($user)
        );

        $canonical = $pl1->canonicalPlayableAgainst($user);

        $this->assertTrue(
            $pl1->equals($canonical)
        );
    }
}
