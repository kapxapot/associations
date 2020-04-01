<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordFeedbackCollection;
use App\Models\Word;

interface WordFeedbackRepositoryInterface
{
    function getAllByWord(Word $word) : WordFeedbackCollection;
}
