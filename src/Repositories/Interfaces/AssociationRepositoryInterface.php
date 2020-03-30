<?php

namespace App\Repositories\Interfaces;

use App\Collections\AssociationCollection;
use App\Models\Association;
use App\Models\Word;

interface AssociationRepositoryInterface extends LanguageElementRepositoryInterface
{
    function get(?int $id): ?Association;
    function getAllByWord(Word $word): AssociationCollection;
    function getByPair(Word $first, Word $second): ?Association;
}
