<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\LanguageElementCollection;
use App\Collections\WordCollection;
use App\Models\DTO\Search\SearchParams;
use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class WordRepositoryMock implements WordRepositoryInterface
{
    private WordCollection $words;

    public function __construct(
        ArraySeederInterface $seeder
    )
    {
        $this->words = WordCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?Word
    {
        return $this->words->first('id', $id);
    }

    public function save(Word $word) : Word
    {
        if ($this->words->contains($word)) {
            return $word;
        }

        if (!$word->isPersisted()) {
            $word->id = $this->words->nextId();
        }

        $this->words = $this->words->add($word);

        return $word;
    }

    public function store(array $data) : Word
    {
        $word = Word::create($data);

        return $this->save($word);
    }

    private function getAllByLanguageConditional(?Language $language) : WordCollection
    {
        return $language
            ? $this->getAllByLanguage($language)
            : $this->words;
    }

    public function getAllByLanguage(Language $language) : WordCollection
    {
        return $this
            ->words
            ->where(
                fn (Word $w) => $w->language()->equals($language)
            );
    }

    public function findInLanguage(
        Language $language,
        ?string $wordStr,
        ?int $exceptId = null
    ) : ?Word
    {
        // todo: add originalWord + exceptId management

        return $this
            ->getAllByLanguage($language)
            ->first(
                fn (Word $w) => $w->word == $wordStr
            );
    }

    public function searchAllNonMature(
        SearchParams $searchParams,
        ?Language $language = null
    ): WordCollection
    {
        $public = $this->getAllNonMature($language);

        // todo: add filter & sort

        return ($searchParams->hasOffset() && $searchParams->hasLimit())
            ? $public->slice($searchParams->offset(), $searchParams->limit())
            : $public;
    }

    public function getNonMatureCount(?Language $language = null, ?string $substr = null): int
    {
        // todo: add filtering by substr
        return $this->getAllNonMature($language)->count();
    }

    public function getAllOutOfDate(
        int $ttlMin,
        int $limit = 0
    ) : WordCollection
    {
        // placeholder
        return WordCollection::empty();
    }

    public function getAllApproved(?Language $language = null) : WordCollection
    {
        return $this
            ->getAllByLanguageConditional($language)
            ->where(
                fn (Word $w) => $w->isApproved()
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

    public function getAllNonMature(?Language $language = null): WordCollection
    {
        return $this
            ->getAllByLanguageConditional($language)
            ->where(
                fn (Word $w) => !$w->isMature()
            );
    }
}
