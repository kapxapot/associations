<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordFeedbackCollection;
use App\Models\Word;
use App\Models\WordFeedback;
use Plasticode\Repositories\Interfaces\Basic\ChangingRepositoryInterface;

interface WordFeedbackRepositoryInterface extends ChangingRepositoryInterface
{
    function get(?int $id) : ?WordFeedback;
    function create(array $data) : WordFeedback;
    function save(WordFeedback $feedback) : WordFeedback;
    function getAllByWord(Word $word) : WordFeedbackCollection;
}
