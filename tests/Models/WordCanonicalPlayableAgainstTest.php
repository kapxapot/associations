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
     * upl1 => null
     */
    public function testUpl1(): void
    {
        $upl1 = $this->makeDislikedWord(1);

        $this->assertUnplayable($upl1);

        $canonical = $upl1->canonicalPlayableAgainst($this->user);

        $this->assertNull($canonical);
    }

    /**
     * pl1 => pl1
     */
    public function testPl1(): void
    {
        $pl1 = $this->makeWord(1);

        $this->assertPlayable($pl1);
        $this->assertCanonical($pl1, $pl1);
    }

    /**
     * pl1 -> pl2 => pl2
     */
    public function testPl1Pl2(): void
    {
        $pl2 = $this->makeWord(2);
        $pl1 = $this->makeWord(1)->withMain($pl2);

        $this->assertPlayable($pl1);
        $this->assertPlayable($pl2);
        $this->assertCanonical($pl1, $pl2);
    }

    /**
     * pl1 -> upl2 => pl1
     */
    public function testPl1Upl2(): void
    {
        $upl2 = $this->makeDislikedWord(2);
        $pl1 = $this->makeWord(1)->withMain($upl2);

        $this->assertPlayable($pl1);
        $this->assertUnplayable($upl2);
        $this->assertCanonical($pl1, $pl1);
    }

    /**
     * pl1 -> pl2 -> pl3 => pl3
     */
    public function testPl1Pl2Pl3(): void
    {
        $pl3 = $this->makeWord(3);
        $pl2 = $this->makeWord(2)->withMain($pl3);
        $pl1 = $this->makeWord(1)->withMain($pl2);

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
        $pl3 = $this->makeDislikedWord(3);
        $pl2 = $this->makeWord(2)->withMain($pl3);
        $pl1 = $this->makeWord(1)->withMain($pl2);

        $this->assertPlayable($pl1);
        $this->assertPlayable($pl2);
        $this->assertUnplayable($pl3);
        $this->assertCanonical($pl1, $pl2);
    }

    private function makeDislikedWord(int $id): Word
    {
        return $this->makeWord($id, true);
    }

    private function makeWord(int $id, bool $isDisliked = false): Word
    {
        $feedbacks = $isDisliked
            ? WordFeedbackCollection::collect(
                new WordFeedback(['created_by' => $this->user->getId(), 'dislike' => 1])
            )
            : WordFeedbackCollection::empty();

        return (new Word(['id' => $id, 'approved' => 1]))
            ->withMain(null)
            ->withTurns(
                TurnCollection::empty()
            )
            ->withFeedbacks($feedbacks);
    }

    private function assertPlayable(Word $word): void
    {
        $this->assertTrue(
            $word->isPlayableAgainst($this->user)
        );
    }

    private function assertUnplayable(Word $word): void
    {
        $this->assertFalse(
            $word->isPlayableAgainst($this->user)
        );
    }

    private function assertCanonical(Word $source, Word $expected): void
    {
        $actual = $source->canonicalPlayableAgainst($this->user);

        $this->assertTrue(
            $expected->equals($actual)
        );
    }
}
