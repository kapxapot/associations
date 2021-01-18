<?php

namespace App\Hydrators;

use App\Models\Language;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class LanguageHydrator extends Hydrator
{
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param Language $entity
     */
    public function hydrate(DbModel $entity): Language
    {
        return $entity
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
