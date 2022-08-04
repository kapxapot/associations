<?php

namespace App\Models;

use App\Models\Interfaces\TurnInterface;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;

/**
 * @property integer|null $associationId
 * @property integer $gameId
 * @property integer $languageId
 * @property string|null $originalUtterance
 * @property integer|null $prevTurnId
 * @property integer|null $userId
 * @property integer $wordId
 * @method Game game()
 * @method static|null prev()
 * @method User|null user()
 * @method static withAssociation(Association|callable|null $association)
 * @method static withGame(Game|callable $game)
 * @method static withPrev(static|callable|null $prev)
 * @method static withUser(User|callable|null $user)
 * @method static withWord(Word|callable $word)
 */
class Turn extends DbModel implements CreatedAtInterface, TurnInterface
{
    use CreatedAt;

    protected string $associationPropertyName = 'association';
    protected string $wordPropertyName = 'word';

    protected function requiredWiths(): array
    {
        return [
            $this->associationPropertyName,
            'game',
            'prev',
            'user',
            $this->wordPropertyName
        ];
    }

    public function association(): ?Association
    {
        return $this->getWithProperty(
            $this->associationPropertyName
        );
    }

    public function word(): Word
    {
        return $this->getWithProperty(
            $this->wordPropertyName
        );
    }

    public function isBy(?User $user): bool
    {
        return $this->user()
            ? $this->user()->equals($user)
            : is_null($user);
    }

    public function isPlayerTurn(): bool
    {
        return $this->user() !== null;
    }

    public function isAiTurn(): bool
    {
        return !$this->isPlayerTurn();
    }

    public function isFinished(): bool
    {
        return $this->finishedAt !== null;
    }

    /**
     * Checks if the turn has an association between its word and the previous one.
     *
     * It can be otherwise if the association is aggregated and is not organic.
     */
    public function isOrganic(): bool
    {
        $prevTurn = $this->prev();

        if ($prevTurn === null) {
            return true;
        }

        $prevWord = $prevTurn->word();

        return $this->association()->hasWord($prevWord);
    }
}
