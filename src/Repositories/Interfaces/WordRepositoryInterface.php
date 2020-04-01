<?php

namespace App\Repositories\Interfaces;

use App\Models\Word;

interface WordRepositoryInterface extends LanguageElementRepositoryInterface
{
    function get(?int $id) : ?Word;
    function save(Word $word) : Word;
}
