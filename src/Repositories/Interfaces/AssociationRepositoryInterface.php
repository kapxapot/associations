<?php

namespace App\Repositories\Interfaces;

use App\Models\Association;
use App\Models\Word;
use Plasticode\Collection;

interface AssociationRepositoryInterface extends LanguageElementRepositoryInterface
{
    function get(?int $id) : ?Association;
    function getAllByWord(Word $word) : Collection;
    function getByPair(Word $first, Word $second) : ?Association;
}
