<?php

namespace App\Hydrators;

use App\Models\AssociationFeedback;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\Basic\DbModel;

class AssociationFeedbackHydrator extends Hydrator
{
    protected AssociationRepositoryInterface $associationRepository;
    protected UserRepositoryInterface $userRepository;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->associationRepository = $associationRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param AssociationFeedback $entity
     */
    public function hydrate(DbModel $entity) : AssociationFeedback
    {
        return $entity
            ->withAssociation(
                fn () => $this->associationRepository->get($entity->associationId)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
