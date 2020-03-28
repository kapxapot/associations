<?php

namespace App\Repositories;

use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;

class WordRepository extends LanguageElementRepository implements WordRepositoryInterface
{
    protected string $entityClass = Word::class;

    public function get(?int $id) : ?Word
    {
        return $this->getEntity($id);
    }

    public function save(Word $word) : Word
    {
        return $this->saveEntity($word);
    }
}
