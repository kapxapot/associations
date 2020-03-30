<?php

namespace App\Collections;

use App\Models\User;
use App\Models\WordFeedback;
use Plasticode\Interfaces\ArrayableInterface;

class WordFeedbackCollection extends FeedbackCollection
{
    protected string $class = WordFeedback::class;

    public static function from(ArrayableInterface $arrayable) : self
    {
        return new static($arrayable->toArray());
    }

    public function dislikes() : self
    {
        return self::from(
            parent::dislikes()
        );
    }

    public function matures() : self
    {
        return self::from(
            parent::matures()
        );
    }

    public function firstBy(User $user) : ?WordFeedback
    {
        return $this->first(
            fn (WordFeedback $f) => $f->isCreatedBy($user)
        );
    }
}
