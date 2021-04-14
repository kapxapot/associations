<?php

namespace App\Hydrators;

use App\Models\AssociationOverride;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class AssociationOverrideHydrator extends Hydrator
{
    private AssociationRepositoryInterface $associationRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->associationRepository = $associationRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param AssociationOverride $entity
     */
    public function hydrate(DbModel $entity): AssociationOverride
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
