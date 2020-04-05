<?php

namespace App\Hydrators;

use App\Models\AssociationFeedback;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;

class AssociationFeedbackHydrator implements HydratorInterface
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
                $this->associationRepository->get($entity->associationId)
            )
            ->withCreator(
                $this->userRepository->get($entity->createdBy)
            );
    }
}
