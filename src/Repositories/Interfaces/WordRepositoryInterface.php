<?php

namespace App\Repositories\Interfaces;

use App\Models\Word;

interface WordRepositoryInterface
{
    function save(Word $word) : Word;
}
