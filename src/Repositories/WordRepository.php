<?php

namespace App\Repositories;

use App\Collections\WordCollection;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;

class WordRepository extends LanguageElementRepository implements WordRepositoryInterface
{
    protected string $entityClass = Word::class;

    protected string $sortField = 'word';

    public function get(?int $id) : ?Word
    {
        return $this->getEntity($id);
    }

    public function save(Word $word) : Word
    {
        return $this->saveEntity($word);
    }

    public function store(array $data) : Word
    {
        return $this->storeEntity($data);
    }

    public function getAllByLanguage(Language $language) : WordCollection
    {
        return WordCollection::from(
            parent::getAllByLanguage($language)
        );
    }

    /**
     * Finds the word by string in the specified language.
     * 
     * Normalized word string expected.
     */
    public function findInLanguage(Language $language, ?string $wordStr) : ?Word
    {
        return $this
            ->getByLanguageQuery($language)
            ->where('word_bin', $wordStr)
            ->one();
    }

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    public function getAllOutOfDate(
        int $ttlMin,
        int $limit = 0
    ) : WordCollection
    {
        return WordCollection::from(
            parent::getAllOutOfDate($ttlMin, $limit)
        );
    }

    public function getAllApproved(?Language $language = null) : WordCollection
    {
        return WordCollection::from(
            parent::getAllApproved($language)
        );
    }

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = null
    ) : WordCollection
    {
        return WordCollection::from(
            parent::getLastAddedByLanguage($language, $limit)
        );
    }

    /**
     * Returns words without corresponding dict words.
     */
    public function getAllUnchecked(int $limit = 0) : WordCollection
    {
        $dictWordTable = 'yandex_dict_words';
        $dwAlias = 'dw';

        return WordCollection::from(
            $this
                ->query()
                ->select($this->getTable() . '.*')
                ->leftOuterJoin(
                    $dictWordTable,
                    [
                        $this->getTable() . '.' . $this->idField(),
                        '=',
                        $dwAlias . '.word_id'
                    ],
                    $dwAlias)
                ->whereNull($dwAlias . '.id')
                ->limit($limit)
        );
    }
}
