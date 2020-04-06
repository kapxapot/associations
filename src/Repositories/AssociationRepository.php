<?php

namespace App\Repositories;

use App\Collections\AssociationCollection;
use App\Models\Association;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;

class AssociationRepository extends LanguageElementRepository implements AssociationRepositoryInterface
{
    protected string $entityClass = Association::class;

    public function get(?int $id) : ?Association
    {
        return $this->getEntity($id);
    }

    public function getAllByLanguage(Language $language) : AssociationCollection
    {
        return AssociationCollection::from(
            parent::getAllByLanguage($language)
        );
    }

    public function getAllByWord(Word $word) : AssociationCollection
    {
        return AssociationCollection::from(
            $this
                ->query()
                ->whereAnyIs(
                    [
                        ['first_word_id' => $word->getId()],
                        ['second_word_id' => $word->getId()],
                    ]
                )
        );
    }

    public function getByPair(Word $first, Word $second) : ?Association
    {
        return $this
            ->query()
            ->where('first_word_id', $first->getId())
            ->where('second_word_id', $second->getId())
            ->one();
    }

    public function getLastAddedByLanguage(
        ?Language $language = null,
        int $limit = null
    ) : AssociationCollection
    {
        return AssociationCollection::from(
            parent::getLastAddedByLanguage($language, $limit)
        );
    }
}
