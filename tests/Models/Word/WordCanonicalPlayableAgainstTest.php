<?php

namespace App\Tests\Models\Word;

use App\Collections\TurnCollection;
use App\Collections\WordFeedbackCollection;
use App\Models\User;
use App\Models\Word;
use App\Models\WordFeedback;
use App\Policies\UserPolicy;
use App\Semantics\Scope;
use App\Semantics\Severity;
use PHPUnit\Framework\TestCase;

final class WordCanonicalPlayableAgainstTest extends TestCase
{
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = (new User(['id' => 1]))
            ->withPolicy(
                (new UserPolicy())->withCanSeeAllWords(true)
            );
    }

    public function tearDown(): void
    {
        unset($this->user);

        parent::tearDown();
    }

    /**
     * pl1 => pl1
     */
    public function testPl1(): void
    {
        $pl1 = $this->makeWord(1);

        // playable for user and canonical equals to the word
        $this->assertPlayable($pl1, $this->user);
        $this->assertCanonical($pl1, $pl1, $this->user);

        // playable for **any** user and canonical equals to the word
        $this->assertPlayable($pl1);
        $this->assertCanonical($pl1, $pl1);
    }

    /**
     * upl1 => null
     */
    public function testUpl1(): void
    {
        $upl1 = $this->makeDislikedWord(1, $this->user);

        // unplayable for user & canonical is null
        $this->assertUnplayable($upl1, $this->user);
        $this->assertCanonical($upl1, null, $this->user);

        // playable for **any** user and canonical equals to the word
        $this->assertPlayable($upl1);
        $this->assertCanonical($upl1, $upl1);
    }

    /**
     * pl1 -> pl2 => pl2
     */
    public function testPl1Pl2(): void
    {
        $pl2 = $this->makeWord(2);
        $pl1 = $this->makeWord(1)->withMain($pl2);

        // both words are playable for user and canonical is $pl2
        $this->assertPlayable($pl1, $this->user);
        $this->assertPlayable($pl2, $this->user);
        $this->assertCanonical($pl1, $pl2, $this->user);

        // both words are playable for **any** user and canonical is $pl2
        $this->assertPlayable($pl1);
        $this->assertPlayable($pl2);
        $this->assertCanonical($pl1, $pl2);
    }

    /**
     * pl1 -> upl2 => pl1
     */
    public function testPl1Upl2(): void
    {
        $upl2 = $this->makeDislikedWord(2, $this->user);
        $pl1 = $this->makeWord(1)->withMain($upl2);

        // $pl1 is playable, $upl2 is unplayable for user, $pl1 is canonical
        $this->assertPlayable($pl1, $this->user);
        $this->assertUnplayable($upl2, $this->user);
        $this->assertCanonical($pl1, $pl1, $this->user);

        // both words are playable for **any** user and canonical is $upl2
        $this->assertPlayable($pl1);
        $this->assertPlayable($upl2);
        $this->assertCanonical($pl1, $upl2);
    }

    /**
     * pl1 -> pl2 -> pl3 => pl3
     */
    public function testPl1Pl2Pl3(): void
    {
        $pl3 = $this->makeWord(3);
        $pl2 = $this->makeWord(2)->withMain($pl3);
        $pl1 = $this->makeWord(1)->withMain($pl2);

        // all words are playable for user and canonical is $pl3
        $this->assertPlayable($pl1, $this->user);
        $this->assertPlayable($pl2, $this->user);
        $this->assertPlayable($pl3, $this->user);
        $this->assertCanonical($pl1, $pl3, $this->user);

        // all words are playable for **any** user and canonical is $pl3
        $this->assertPlayable($pl1);
        $this->assertPlayable($pl2);
        $this->assertPlayable($pl3);
        $this->assertCanonical($pl1, $pl3);
    }

    /**
     * pl1 -> pl2 -> upl3 => pl2
     */
    public function testPl1Pl2Upl3(): void
    {
        $upl3 = $this->makeDislikedWord(3, $this->user);
        $pl2 = $this->makeWord(2)->withMain($upl3);
        $pl1 = $this->makeWord(1)->withMain($pl2);

        // $pl1 & $pl2 are playable, $upl3 is unplayable for user, canonical is $pl2
        $this->assertPlayable($pl1, $this->user);
        $this->assertPlayable($pl2, $this->user);
        $this->assertUnplayable($upl3, $this->user);
        $this->assertCanonical($pl1, $pl2, $this->user);

        // all words are playable for **any** user and canonical is $upl3
        $this->assertPlayable($pl1);
        $this->assertPlayable($pl2);
        $this->assertPlayable($upl3);
        $this->assertCanonical($pl1, $upl3);
    }

    private function makeDislikedWord(int $id, User $user): Word
    {
        return $this->makeWord($id, $user);
    }

    private function makeWord(int $id, ?User $dislikedByUser = null): Word
    {
        $feedbacks = $dislikedByUser
            ? WordFeedbackCollection::collect(
                new WordFeedback(['created_by' => $dislikedByUser->getId(), 'dislike' => 1])
            )
            : WordFeedbackCollection::empty();

        $word = new Word([
            'id' => $id,
            'scope' => Scope::PUBLIC,
            'severity' => Severity::NEUTRAL
        ]);

        return $word
            ->withMain(null)
            ->withTurns(
                TurnCollection::empty()
            )
            ->withFeedbacks($feedbacks);
    }

    private function assertPlayable(Word $word, ?User $user = null): void
    {
        $this->assertTrue(
            $word->isPlayableAgainst($user)
        );
    }

    private function assertUnplayable(Word $word, ?User $user = null): void
    {
        $this->assertFalse(
            $word->isPlayableAgainst($user)
        );
    }

    private function assertCanonical(Word $source, ?Word $expected = null, ?User $user = null): void
    {
        $actual = $source->canonicalPlayableAgainst($user);

        if ($expected === null) {
            $this->assertNull($actual);
        } else {
            $this->assertTrue(
                $expected->equals($actual)
            );
        }
    }
}
