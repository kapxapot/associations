<?php

namespace App\Semantics\Association;

use App\Collections\AggregatedAssociationCollection;
use App\Models\AggregatedAssociation;
use App\Models\Association;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;

/**
 * Aggregates word's associations using related words list.
 */
class CachingAssociationAggregator extends AbstractAssociationAggregator
{
    private AssociationRepositoryInterface $associationRepository;
    private WordRepositoryInterface $wordRepository;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        WordRepositoryInterface $wordRepository,
        AssociationCongregator $congregator
    )
    {
        parent::__construct($congregator);

        $this->associationRepository = $associationRepository;
        $this->wordRepository = $wordRepository;
    }


    public function aggregateAssociations(
        Word $word,
        ?Word $exceptWord = null
    ): AggregatedAssociationCollection
    {
        $relatedWordIds = $word->aggregatedWordIds();

        $relatedWords = $this->wordRepository->getAllByIds($relatedWordIds);

        $associations = $this
            ->associationRepository
            ->getAllByWords($relatedWords)
            ->distinct();

        $getAnchor = function (Association $a) use ($word, $relatedWords): Word {
            if ($a->hasWord($word)) {
                return $word;
            }

            return $relatedWords->first(
                fn (Word $w) => $a->hasWord($w)
            );
        };

        return AggregatedAssociationCollection::from(
            $associations
                ->map(
                    fn (Association $a) => new AggregatedAssociation($a, $getAnchor($a))
                )
        );
    }
}
