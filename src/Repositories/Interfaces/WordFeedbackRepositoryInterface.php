<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordFeedbackCollection;
use App\Models\Word;
use App\Models\WordFeedback;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;

interface WordFeedbackRepositoryInterface extends ChangingRepositoryInterface, FilteringRepositoryInterface
{
    function get(?int $id): ?WordFeedback;

    function create(array $data): WordFeedback;

    function save(WordFeedback $feedback): WordFeedback;

    function getAllByWord(Word $word): WordFeedbackCollection;
}
