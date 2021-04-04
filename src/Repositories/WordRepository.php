<?php

namespace App\Repositories;

use App\Collections\WordCollection;
use App\Models\DTO\Search\SearchParams;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Data\Query;

class WordRepository extends LanguageElementRepository implements WordRepositoryInterface
{
    protected string $sortField = 'word';

    protected function entityClass(): string
    {
        return Word::class;
    }

    public function get(?int $id): ?Word
    {
        return $this->getEntity($id);
    }

    public function save(Word $word): Word
    {
        $word['word_bin'] = $word->word;

        if (!$word->isPersisted()) {
            $word['original_word'] = $word->word;
        }

        return $this->saveEntity($word);
    }

    public function store(array $data): Word
    {
        $data['word_bin'] = $data['word'];
        $data['original_word'] = $data['word'];

        return $this->storeEntity($data);
    }

    public function getAllByLanguage(Language $language): WordCollection
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
    public function findInLanguage(
        Language $language,
        ?string $wordStr,
        ?int $exceptId = null
    ): ?Word
    {
        return $this
            ->getByLanguageQuery($language)
            ->whereAnyIs(
                [
                    ['word_bin' => $wordStr],
                    ['original_word' => $wordStr],
                ]
            )
            ->applyIf(
                $exceptId > 0,
                fn (Query $q) => $q->whereNotEqual($this->idField(), $exceptId)
            )
            ->one();
    }

    public function searchAllNonMature(
        SearchParams $searchParams,
        ?Language $language = null
    ): WordCollection
    {
        $query = $this
            ->nonMatureQuery($language)
            ->applyIf(
                $searchParams->hasFilter(),
                fn (Query $q) => $this->filterBySubstr($q, $searchParams->filter())
            )
            ->applyIf(
                $searchParams->hasSort(),
                fn (Query $q) => $q->withSort($searchParams->sort())
            )
            ->applyIf(
                $searchParams->hasOffset(),
                fn (Query $q) => $q->offset($searchParams->offset())
            )
            ->applyIf(
                $searchParams->hasLimit(),
                fn (Query $q) => $q->limit($searchParams->limit())
            );

        return WordCollection::from($query);
    }

    public function getNonMatureCount(?Language $language = null, ?string $substr = null): int
    {
        return $this
            ->nonMatureQuery($language)
            ->applyIf(
                strlen($substr) > 0,
                fn (Query $q) => $this->filterBySubstr($q, $substr)
            )
            ->count();
    }

    public function getAllOutOfDate(
        int $ttlMin,
        int $limit = 0
    ): WordCollection
    {
        return WordCollection::from(
            parent::getAllOutOfDate($ttlMin, $limit)
        );
    }

    public function getAllApproved(?Language $language = null): WordCollection
    {
        return WordCollection::from(
            parent::getAllApproved($language)
        );
    }

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): WordCollection
    {
        return WordCollection::from(
            parent::getLastAddedByLanguage($language, $limit)
        );
    }

    public function getAllUnchecked(int $limit = 0): WordCollection
    {
        // todo: this needs to be refactored (get table name from other injected repo?)
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
                    $dwAlias
                )
                ->whereNull($dwAlias . '.id')
                ->limit($limit)
        );
    }

    public function getAllUndefined(int $limit = 0): WordCollection
    {
        // todo: this needs to be refactored (get table name from other injected repo?)
        $definitionTable = 'definitions';
        $defAlias = 'def';

        return WordCollection::from(
            $this
                ->query()
                ->select($this->getTable() . '.*')
                ->leftOuterJoin(
                    $definitionTable,
                    [
                        $this->getTable() . '.' . $this->idField(),
                        '=',
                        $defAlias . '.word_id'
                    ],
                    $defAlias
                )
                ->whereNull($defAlias . '.id')
                ->limit($limit)
        );
    }
    // queries

    /**
     * Filters non-mature & enabled words.
     */
    protected function nonMatureQuery(?Language $language = null): Query
    {
        return parent::nonMatureQuery($language)->apply(
            fn (Query $q) => $this->filterEnabled($q)
        );
    }

    // filters

    protected function filterBySubstr(Query $query, string $substr): Query
    {
        return $query->search(
            mb_strtolower($substr),
            '(word_bin like ?)'
        );
    }

    protected function filterEnabled(Query $query): Query
    {
        return $query->whereNotEqual('disabled', 1);
    }
}
