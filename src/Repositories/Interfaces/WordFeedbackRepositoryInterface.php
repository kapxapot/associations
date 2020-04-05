<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordFeedbackCollection;
use App\Models\Word;
use App\Models\WordFeedback;

interface WordFeedbackRepositoryInterface
{
    function create(array $data) : WordFeedback;
    function getAllByWord(Word $word) : WordFeedbackCollection;
}
