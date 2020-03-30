<?php

namespace App\Collections;

use App\Models\User;
use App\Models\Word;
use Plasticode\Interfaces\ArrayableInterface;

class WordCollection extends LanguageElementCollection
{
    protected string $class = Word::class;

    public static function from(ArrayableInterface $arrayable) : self
    {
        return new static($arrayable->toArray());
    }

    public function approved(): self
    {
        return self::from(
            parent::approved()
        );
    }

    public function notApproved(): self
    {
        return self::from(
            parent::notApproved()
        );
    }

    public function mature(): self
    {
        return self::from(
            parent::mature()
        );
    }

    public function notMature(): self
    {
        return self::from(
            parent::notMature()
        );
    }

    public function visibleFor(User $user) : self
    {
        return self::from(
            parent::visibleFor($user)
        );
    }

    public function invisibleFor(User $user) : self
    {
        return self::from(
            parent::invisibleFor($user)
        );
    }
}
