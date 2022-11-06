<?php

namespace App\Repositories;

use App\Collections\WordCollection;
use App\Config\Config;
use App\Data\MultilingualSearcher;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Semantics\Scope;
use Plasticode\Collections\Generic\NumericCollection;
use Plasticode\Data\Query;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext;
use Plasticode\Repositories\Idiorm\Traits\SearchRepository;
use Plasticode\Search\SearchParams;

class WordRepository extends LanguageElementRepository implements WordRepositoryInterface
{
    use SearchRepository;

    private Config $config;
    private MultilingualSearcher $searcher;

    protected string $sortField = 'word';

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $context,
        $hydrator,
        Config $config,
        MultilingualSearcher $searcher
    )
    {
        parent::__construct($context, $hydrator);

        $this->config = $config;
        $this->searcher = $searcher;
    }

    protected function entityClass(): string
    {
        return Word::class;
    }

    protected function collect(ArrayableInterface $arrayable): WordCollection
    {
        return WordCollection::from($arrayable);
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

        $word->meta = $word->encodeMeta();

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
        return parent::getAllByLanguage($language);
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
        return parent::getAllByIds($ids);
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

        return $this->collect($query);
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
        return parent::getAllOutOfDate($ttlMin, $limit);
    }

    public function getAllByScope(int $scope, ?Language $language = null): WordCollection
    {
        return parent::getAllByScope($scope, $language);
    }

    public function getAllApproved(?Language $language = null): WordCollection
    {
        return parent::getAllApproved($language);
    }

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): WordCollection
    {
        return parent::getLastAddedByLanguage($language, $limit);
    }

    public function getAllUnchecked(int $limit = 0): WordCollection
    {
        // todo: this needs to be refactored (get table name from other injected repo?)
        $dictWordTable = 'yandex_dict_words';
        $dwAlias = 'dw';

        $query = $this
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
            ->limit($limit);

        return $this->collect($query);
    }

    public function getAllUndefined(int $limit = 0): WordCollection
    {
        // todo: this needs to be refactored (get table name from other injected repo?)
        $definitionTable = 'definitions';
        $defAlias = 'def';

        $query = $this
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
            ->limit($limit);

        return $this->collect($query);
    }

    public function getAllByMain(Word $word): WordCollection
    {
        return $this->collect(
            $this
                ->query()
                ->where('main_id', $word->getId())
        );
    }

    // SearchRepository

    public function applyFilter(Query $query, string $filter): Query
    {
        $query = $query
            ->select($this->getTable() . '.*')
            ->join(
                'users',
                [
                    $this->getTable() . '.created_by',
                    '=',
                    'user.id'
                ],
                'user'
            );

        return $this->searcher->search(
            $this->config->langCode(),
            $query,
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
