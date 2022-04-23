<?php

namespace App\Repositories;

use App\Collections\WordCollection;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Semantics\Scope;
use Plasticode\Collections\Generic\NumericCollection;
use Plasticode\Data\Query;
use Plasticode\Repositories\Idiorm\Traits\SearchRepository;
use Plasticode\Search\SearchParams;

class WordRepository extends LanguageElementRepository implements WordRepositoryInterface
{
    use SearchRepository;

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
            $word->originalWord = $word->word;
        }

        $word->meta = empty($word->metaData())
            ? null
            : json_encode($word->metaData());

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

    public function findInLanguageStrict(
        Language $language,
        ?string $wordStr,
        ?int $exceptId = null
    ): ?Word
    {
        return $this->findInLanguage($language, $wordStr, $exceptId, true);
    }

    public function findInLanguage(
        Language $language,
        ?string $wordStr,
        ?int $exceptId = null,
        bool $strict = false
    ): ?Word
    {
        return $this
            ->byLanguageQuery($language)
            ->applyIfElse(
                $strict,
                fn (Query $q) => $q->where('word_bin', $wordStr),
                fn (Query $q) => $q->whereAnyIs([
                    ['word_bin' => $wordStr],
                    ['original_word' => $wordStr],
                ])
            )
            ->applyIf(
                $exceptId > 0,
                fn (Query $q) => $q->whereNotEqual($this->idField(), $exceptId)
            )
            ->one();
    }

    // public function findMany(Language $language, string ...$wordStrs): WordCollection
    // {

    // }

    public function getAllByIds(NumericCollection $ids): WordCollection
    {
        return WordCollection::from(
            $this
                ->query()
                ->whereIn($this->idField(), $ids)
        );
    }

    public function searchAllPublic(
        SearchParams $searchParams,
        ?Language $language = null
    ): WordCollection
    {
        $query = $this
            ->publicQuery($language)
            ->apply(
                fn (Query $q) => $this->applySearchParams($q, $searchParams)
            );

        return WordCollection::from($query);
    }

    public function getPublicCount(
        ?Language $language = null,
        ?string $filter = null
    ): int
    {
        return $this
            ->publicQuery($language)
            ->applyIf(
                strlen($filter) > 0,
                fn (Query $q) => $this->applyFilter($q, $filter)
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

    public function getAllByScope(int $scope, ?Language $language = null): WordCollection
    {
        return WordCollection::from(
            parent::getAllByScope($scope, $language)
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

    public function getAllByMain(Word $word): WordCollection
    {
        return WordCollection::from(
            $this->query()->where('main_id', $word->getId())
        );
    }

    // SearchRepository

    public function applyFilter(Query $query, string $filter): Query
    {
        return $query
            ->select($this->getTable() . '.*')
            ->join(
                'users',
                [
                    $this->getTable() . '.created_by',
                    '=',
                    'user.id'
                ],
                'user'
            )
            ->search(
                mb_strtolower($filter),
                '(word like ? or user.login like ? or user.name like ?)',
                3
            );
    }

    // queries

    /**
     * Filters not mature & enabled words.
     */
    protected function publicQuery(?Language $language = null): Query
    {
        return $this
            ->byLanguageQuery($language)
            ->apply(
                fn (Query $q) => $this->filterNotMature($q)
            )
            ->apply(
                fn (Query $q) => $this->filterByScopeNot($q, ...Scope::allFuzzyDisabled())
            );
    }
}
