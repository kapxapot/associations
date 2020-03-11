<?php

namespace App\Repositories;

use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class WordRepository extends IdiormRepository implements WordRepositoryInterface
{
    protected $entityClass = Word::class;

    public function save(Word $word) : Word
    {
        return $this->saveEntity($word);
    }
}
