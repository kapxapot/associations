<?php

namespace App\Services;

use App\Collections\AggregatedAssociationCollection;
use App\Collections\WordCollection;
use App\Events\Association\AssociationCreatedEvent;
use App\Models\AggregatedAssociation;
use App\Models\Association;
use App\Models\DTO\AggregatedAssociationDto;
use App\Models\DTO\EtherealAssociation;
use App\Models\Interfaces\AssociationInterface;
use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Semantics\Interfaces\AssociationAggregatorInterface;
use Plasticode\Collections\Generic\Collection;
use Plasticode\Collections\Generic\NumericCollection;
use Plasticode\Events\EventDispatcher;
use Plasticode\Exceptions\InvalidOperationException;
use Plasticode\Exceptions\InvalidResultException;
use Webmozart\Assert\Assert;

/**
 * @emits AssociationCreatedEvent
 */
class AssociationService
{
    private AssociationRepositoryInterface $associationRepository;
    private WordRepositoryInterface $wordRepository;

    private AssociationAggregatorInterface $associationAggregator;

    private EventDispatcher $eventDispatcher;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        WordRepositoryInterface $wordRepository,
        AssociationAggregatorInterface $associationAggregator,
        EventDispatcher $eventDispatcher
    )
    {
        $this->associationRepository = $associationRepository;
        $this->wordRepository = $wordRepository;

        $this->associationAggregator = $associationAggregator;

        $this->eventDispatcher = $eventDispatcher;
    }

    public function getOrCreate(
        Word $first,
        Word $second,
        ?User $user = null,
        ?Language $language = null
    ): AssociationInterface
    {
        $association = $this->getByPair($first, $second, $language)
            ?? ($user !== null
                ? $this->create($first, $second, $user, $language)
                : new EtherealAssociation($first, $second)
            );

        if ($association === null) {
            throw new InvalidResultException(
                'Association can\'t be found or created.'
            );
        }

        return $association;
    }

    /**
     * Creates association.
     *
     * !!!!!!!!!!!!!!!!!!!!!!!
     * Potential problem here:
     *  association can be created by another user
     *  at the same time.
     * !!!!!!!!!!!!!!!!!!!!!!!
     */
    public function create(
        Word $first,
        Word $second,
        User $user,
        Language $language = null
    ): Association
    {
        $association = $this->getByPair($first, $second, $language);

        if ($association) {
            throw new InvalidOperationException('Association already exists.');
        }

        $this->checkPair($first, $second);

        [$first, $second] = $this->orderPair($first, $second);

        $association = $this
            ->associationRepository
            ->store([
                'first_word_id' => $first->getId(),
                'second_word_id' => $second->getId(),
                'created_by' => $user->getId(),
                'language_id' => $language ? $language->getId() : null
            ]);

        $this->eventDispatcher->dispatch(
            new AssociationCreatedEvent($association)
        );

        return $association;
    }

    public function checkPair(
        Word $first,
        Word $second,
        Language $language = null
    ): void
    {
        Assert::allNotNull(
            [$first, $second],
            'Both word must be non-null.'
        );

        Assert::false(
            $first->equals($second),
            'Words can\'t be the same.'
        );

        $firstLanguage = $first->language();
        $secondLanguage = $second->language();

        Assert::true(
            $firstLanguage->equals($secondLanguage),
            'Words must be of the same language.'
        );
        
        Assert::false(
            $language !== null && !$firstLanguage->equals($language),
            'Words must be of the specified language.'
        );
    }

    public function orderPair(Word $first, Word $second): WordCollection
    {
        return WordCollection::collect($first, $second)->order();
    }

    public function getByPair(
        Word $first,
        Word $second,
        Language $language = null
    ): ?Association
    {
        $this->checkPair($first, $second, $language);

        [$first, $second] = $this->orderPair($first, $second);

        return $this->associationRepository->getByOrderedPair($first, $second);
    }

    public function getCanonical(Association $association): AssociationInterface
    {
        if ($association->isCanonical()) {
            return $association;
        }

        [$w1, $w2] = $association->words()->canonical()->order();

        return $this->associationRepository->getByOrderedPair($w1, $w2)
            ?? new EtherealAssociation($w1, $w2);
    }

    public function getCanonicalPlayableAgainst(
        Association $association,
        ?User $user = null
    ): ?AssociationInterface
    {
        $w1 = $association->firstWord()->canonicalPlayableAgainst($user);
        $w2 = $association->secondWord()->canonicalPlayableAgainst($user);

        if ($w1 === null || $w2 === null) {
            return null;
        }

        if ($w1->equals($w2)) {
            return new EtherealAssociation($w1, $w2);
        }

        $canonical = $this->getByPair($w1, $w2);

        if ($canonical) {
            return $canonical;
        }

        [$first, $second] = $this->orderPair($w1, $w2);

        return new EtherealAssociation($first, $second);
    }

    public function getAggregatedAssociationsFor(
        Word $word,
        bool $suppressMeta = false
    ): AggregatedAssociationCollection
    {
        $data = $word->aggregatedAssociationsData();

        if ($data !== null && !$suppressMeta) {
            return $this->deserializeAggregatedAssociations($data);
        }

        // no data or need to recount
        return $this->associationAggregator->aggregateFor($word);
    }

    public function deserializeAggregatedAssociations(
        Collection $data
    ): AggregatedAssociationCollection
    {
        $dtos = $data
            ->map(
                fn (array $item) => AggregatedAssociationDto::fromArray($item)
            )
            ->group(
                fn (AggregatedAssociationDto $dto) => $dto->associationId()
            );

        // get all association ids
        $associationIds = NumericCollection::make(
            array_keys($dtos)
        );

        // load all associations by ids
        $associations = $this->associationRepository->getAllByIds($associationIds);

        // get all word ids
        $wordIds = $associations
            ->flatMap(
                fn (Association $association) => $association->wordIds()
            )
            ->numerize()
            ->distinct();

        // load all words by ids
        $this->wordRepository->getAllByIds($wordIds);

        // create aggregated associations
        return AggregatedAssociationCollection::from(
            $associations->map(
                function (Association $association) use ($dtos) {
                    $id = $association->getId();

                    /** @var AggregatedAssociationDto */
                    $dto = $dtos[$id]->first();

                    $anchor = $this->wordRepository->get($dto->anchorId());
                    $junky = $dto->junky();
                    $log = $dto->log();

                    $aa = new AggregatedAssociation($association, $anchor);
                    $aa = $aa->withJunky($junky);

                    if ($log) {
                        $aa->addToLog($log);
                    }

                    return $aa;
                }
            )
        );
    }
}
