<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordRelationCollection;
use App\Models\Word;
use App\Models\WordRelation;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface WordRelationRepositoryInterface extends ChangingRepositoryInterface
{
    public function get(?int $id): ?WordRelation;

    public function create(array $data): WordRelation;

    public function save(WordRelation $wordRelation): WordRelation;

    public function getAllByWord(Word $word): WordRelationCollection;

    public function getAllByMainWord(Word $mainWord): WordRelationCollection;
}
