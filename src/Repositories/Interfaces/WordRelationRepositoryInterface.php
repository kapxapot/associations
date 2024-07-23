<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordRelationCollection;
use App\Models\Word;
use App\Models\WordRelation;
use App\Models\WordRelationType;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;

interface WordRelationRepositoryInterface extends ChangingRepositoryInterface, FilteringRepositoryInterface
{
    public function get(?int $id): ?WordRelation;

    public function save(WordRelation $wordRelation): WordRelation;

    public function getAllByWord(Word $word): WordRelationCollection;

    public function getAllByMainWord(Word $mainWord): WordRelationCollection;

    public function find(Word $word, WordRelationType $type, Word $mainWord, ?int $exceptId = null);
}
