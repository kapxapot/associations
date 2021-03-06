<?php

namespace App\Services;

use App\Collections\WordCollection;
use App\Events\Association\AssociationCreatedEvent;
use App\Models\Association;
use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
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

    private EventDispatcher $eventDispatcher;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        EventDispatcher $eventDispatcher
    )
    {
        $this->associationRepository = $associationRepository;

        $this->eventDispatcher = $eventDispatcher;
    }

    public function getOrCreate(
        Word $first,
        Word $second,
        User $user = null,
        Language $language = null
    ): Association
    {
        $association =
            $this->getByPair($first, $second, $language)
            ?? $this->create($first, $second, $user, $language);

        if (is_null($association)) {
            throw new InvalidResultException(
                'Association can\'t be found or added.'
            );
        }

        return $association;
    }

    /**
     * Creates association
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
            ->store(
                [
                    'first_word_id' => $first->getId(),
                    'second_word_id' => $second->getId(),
                    'created_by' => $user->getId(),
                    'language_id' => $language ? $language->getId() : null
                ]
            );

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

    private function getByPair(
        Word $first,
        Word $second,
        Language $language = null
    ): ?Association
    {
        $this->checkPair($first, $second, $language);

        [$first, $second] = $this->orderPair($first, $second);

        return $this->associationRepository->getByPair($first, $second);
    }
}
