<?php

namespace App\Validation\Rules;

use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Respect\Validation\Rules\AbstractRule;

class AssociationExists extends AbstractRule
{
    private AssociationRepositoryInterface $associationRepository;

    public function __construct(
        AssociationRepositoryInterface $associationRepository
    )
    {
        $this->associationRepository = $associationRepository;
    }

    public function validate($input)
    {
        $association = $this->associationRepository->get($input);

        return !is_null($association);
    }
}
