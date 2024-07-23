<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordFeedbackCollection;
use App\Models\Word;
use App\Models\WordFeedback;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;

interface WordFeedbackRepositoryInterface extends ChangingRepositoryInterface, FilteringRepositoryInterface
{
    public function get(?int $id): ?WordFeedback;

    public function store(array $data): WordFeedback;

    public function save(WordFeedback $feedback): WordFeedback;

    public function getAllByWord(Word $word): WordFeedbackCollection;
}
