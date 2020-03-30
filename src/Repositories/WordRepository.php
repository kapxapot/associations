<?php

namespace App\Repositories;

use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;

class WordRepository extends LanguageElementRepository implements WordRepositoryInterface
{
    protected string $entityClass = Word::class;

    protected string $sortField = 'word';

    public function get(?int $id): ?Word
    {
        return $this->getEntity($id);
    }

    public function save(Word $word): Word
    {
        return $this->saveEntity($word);
    }

    /**
     * Finds the word by string in the specified language.
     * 
     * Normalized word string expected.
     */
    public function findInLanguage(Language $language, ?string $wordStr): ?Word
    {
        return $this
            ->getByLanguageQuery($language)
            ->where('word_bin', $wordStr)
            ->one();
    }
}
