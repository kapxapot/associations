<?php

namespace App\Generators;

use App\Models\AssociationOverride;
use App\Repositories\Interfaces\AssociationOverrideRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\EntityGenerator;

class AssociationOverrideGenerator extends EntityGenerator
{
    private AssociationOverrideRepositoryInterface $associationOverrideRepository;

    public function __construct(
        GeneratorContext $context,
        AssociationOverrideRepositoryInterface $associationOverrideRepository
    )
    {
        parent::__construct($context);

        $this->associationOverrideRepository = $associationOverrideRepository;
    }

    protected function entityClass(): string
    {
        return AssociationOverride::class;
    }

    public function getRepository(): AssociationOverrideRepositoryInterface
    {
        return $this->associationOverrideRepository;
    }
}
