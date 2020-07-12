<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\AssociationCollection;
use App\Collections\LanguageElementCollection;
use App\Models\Association;
use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;

class AssociationRepositoryMock implements AssociationRepositoryInterface
{
    private AssociationCollection $associations;

    public function __construct()
    {
        $this->associations = AssociationCollection::empty();
    }

    public function get(?int $id) : ?Association
    {
        return $this->associations->first('id', $id);
    }

    public function save(Association $association) : Association
    {
        if ($this->associations->contains($association)) {
            return $association;
        }

        if (!$association->isPersisted()) {
            $association->id = $this->associations->nextId();
        }

        $this->associations = $this->associations->add($association);

        return $association;
    }

    public function store(array $data) : Association
    {
        $association = Association::create($data);

        return $this->save($association);
    }

    private function getAllByLanguageConditional(?Language $language) : AssociationCollection
    {
        return $language
            ? $this->getAllByLanguage($language)
            : $this->associations;
    }

    public function getAllByLanguage(Language $language) : AssociationCollection
    {
        return $this
            ->associations
            ->where(
                fn (Association $a) => $a->language()->equals($language)
            );
    }

    public function getAllByWord(Word $word) : AssociationCollection
    {
        return $this
            ->associations
            ->where(
                fn (Association $a) =>
                    $a->firstWord()->equals($word)
                    || $a->secondWord()->equals($word)
            );
    }

    public function getByPair(Word $first, Word $second) : ?Association
    {
        return $this
            ->associations
            ->first(
                fn (Association $a) =>
                    $a->firstWord()->equals($first)
                    && $a->secondWord()->equals($second)
            );
    }

    /**
     * Returns out of date language elements.
     *
     * @param integer $ttlMin Time to live in minutes
     */
    public function getAllOutOfDate(
        int $ttlMin,
        int $limit = 0
    ) : AssociationCollection
    {
        // placeholder
        return AssociationCollection::empty();
    }

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = 0
    ) : AssociationCollection
    {
        // placeholder
        return $this
            ->getAllByLanguageConditional($language)
            ->take($limit);
    }

    public function getCountByLanguage(Language $language) : int
    {
        return $this->getAllByLanguage($language)->count();
    }

    public function getAllCreatedByUser(
        User $user,
        ?Language $language = null
    ) : LanguageElementCollection
    {
        // placeholder
        return LanguageElementCollection::empty();
    }

    public function getAllPublic(?Language $language = null) : LanguageElementCollection
    {
        return $this
            ->getAllByLanguageConditional($language)
            ->where(
                fn (Association $a) => !$a->isMature()
            );
    }

    public function getAllApproved(
        ?Language $language = null
    ) : LanguageElementCollection
    {
        return $this
            ->getAllByLanguageConditional($language)
            ->where(
                fn (Association $a) => $a->isApproved()
            );
    }
}
