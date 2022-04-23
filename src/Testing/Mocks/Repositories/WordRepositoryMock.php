<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\WordCollection;
use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Collections\Generic\NumericCollection;
use Plasticode\Search\SearchParams;
use Plasticode\Search\SearchResult;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class WordRepositoryMock extends RepositoryMock implements WordRepositoryInterface
{
    private WordCollection $words;

    public function __construct(
        ?ArraySeederInterface $seeder = null
    )
    {
        $this->words = WordCollection::make(
            $seeder ? $seeder->seed() : []
        );
    }

    public function get(?int $id): ?Word
    {
        return $this->words->first('id', $id);
    }

    public function save(Word $word): Word
    {
        if (!$this->words->contains($word)) {
            if (!$word->isPersisted()) {
                $word->id = $this->words->nextId();
            }

            $this->words = $this->words->add($word);
        }

        return $word;
    }

    public function store(array $data): Word
    {
        $word = Word::create($data);

        return $this->save($word);
    }

    private function getAllByLanguageConditional(?Language $language): WordCollection
    {
        return $language
            ? $this->getAllByLanguage($language)
            : $this->words;
    }

    public function getAllByLanguage(Language $language): WordCollection
    {
        return $this
            ->words
            ->where(
                fn (Word $w) => $w->language()->equals($language)
            );
    }

    public function findInLanguageStrict(
        Language $language,
        ?string $wordStr,
        ?int $exceptId = null
    ): ?Word
    {
        return $this->findInLanguage($language, $wordStr, $exceptId);
    }

    public function findInLanguage(
        Language $language,
        ?string $wordStr,
        ?int $exceptId = null,
        bool $strict = false
    ): ?Word
    {
        // todo: add originalWord + exceptId + strict management

        return $this
            ->getAllByLanguage($language)
            ->first(
                fn (Word $w) => $w->word == $wordStr
            );
    }

    public function getAllByIds(NumericCollection $ids): WordCollection
    {
        return $this->words->whereIn('id', $ids);
    }

    public function searchAllPublic(
        SearchParams $searchParams,
        ?Language $language = null
    ): WordCollection
    {
        $public = $this->getAllPublic($language);

        // todo: add filter & sort

        return ($searchParams->hasOffset() && $searchParams->hasLimit())
            ? $public->slice($searchParams->offset(), $searchParams->limit())
            : $public;
    }

    public function getPublicCount(
        ?Language $language = null,
        ?string $substr = null
    ): int
    {
        // todo: add filtering by substr
        return $this->getAllPublic($language)->count();
    }

    public function getAllOutOfDate(
        int $ttlMin,
        int $limit = 0
    ): WordCollection
    {
        // placeholder
        return WordCollection::empty();
    }

    public function getAllByScope(
        int $scope,
        ?Language $language = null
    ): WordCollection
    {
        return $this
            ->getAllByLanguageConditional($language)
            ->where(
                fn (Word $w) => $w->scope == $scope
            );
    }

    public function getAllApproved(?Language $language = null): WordCollection
    {
        return $this
            ->getAllByLanguageConditional($language)
            ->where(
                fn (Word $w) => $w->isFuzzyPublic()
            );
    }

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ): WordCollection
    {
        // placeholder
        return $this
            ->getAllByLanguageConditional($language)
            ->take($limit);
    }

    public function getAllUnchecked(int $limit = 0): WordCollection
    {
        // placeholder
        return $this->words;
    }

    public function getAllUndefined(int $limit = 0): WordCollection
    {
        // placeholder
        return $this->words->where(
            fn (Word $w) => $w->definition() === null
        );
    }

    public function getAllByMain(Word $word): WordCollection
    {
        return $this->words->where(
            fn (Word $w) => $w->equals($word)
        );
    }

    public function getCountByLanguage(Language $language): int
    {
        return $this->getAllByLanguage($language)->count();
    }

    public function getAllCreatedByUser(
        User $user,
        ?Language $language = null
    ): WordCollection
    {
        // placeholder
        return WordCollection::empty();
    }

    /**
     * Returns all words not mature & enabled.
     */
    private function getAllPublic(?Language $language = null): WordCollection
    {
        return $this
            ->getAllByLanguageConditional($language)
            ->where(
                fn (Word $w) => !$w->isMature() && !$w->isFuzzyDisabled()
            );
    }

    public function getSearchResult(SearchParams $searchParams): SearchResult
    {
        // placeholder
        return new SearchResult(
            $this->words,
            $this->words->count(),
            $this->words->count()
        );
    }
}
