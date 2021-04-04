<?php

namespace App\Policies;

class UserPolicy
{
    private bool $canSeeAllGames = false;
    private bool $canSeeAllWords = false;
    private bool $canSeeAllAssociations = false;

    /**
     * @return $this
     */
    public function withCanSeeAllGames(bool $value): self
    {
        $this->canSeeAllGames = $value;

        return $this;
    }

    public function canSeeAllGames(): bool
    {
        return $this->canSeeAllGames;
    }

    /**
     * @return $this
     */
    public function withCanSeeAllWords(bool $value): self
    {
        $this->canSeeAllWords = $value;

        return $this;
    }

    public function canSeeAllWords(): bool
    {
        return $this->canSeeAllWords;
    }

    /**
     * @return $this
     */
    public function withCanSeeAllAssociations(bool $value): self
    {
        $this->canSeeAllAssociations = $value;

        return $this;
    }

    public function canSeeAllAssociations(): bool
    {
        return $this->canSeeAllAssociations;
    }
}
