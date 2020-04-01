<?php

namespace App\Collections;

use App\Models\User;
use App\Models\WordFeedback;

class WordFeedbackCollection extends FeedbackCollection
{
    protected string $class = WordFeedback::class;

    public function firstBy(User $user) : ?WordFeedback
    {
        return $this->first(
            fn (WordFeedback $f) => $f->isCreatedBy($user)
        );
    }

    public function typos() : self
    {
        return $this->where(
            fn (WordFeedback $f) => $f->hasTypo()
        );
    }

    public function duplicates() : self
    {
        return $this->where(
            fn (WordFeedback $f) => $f->hasDuplicate()
        );
    }
}
