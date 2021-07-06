<?php

namespace App\Generators;

use App\Models\Association;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\ChangingEntityGenerator;

class AssociationGenerator extends ChangingEntityGenerator
{
    private AssociationRepositoryInterface $associationRepository;

    public function __construct(
        GeneratorContext $context,
        AssociationRepositoryInterface $associationRepository
    )
    {
        parent::__construct($context);

        $this->associationRepository = $associationRepository;
    }

    protected function entityClass(): string
    {
        return Association::class;
    }

    public function getRepository(): AssociationRepositoryInterface
    {
        return $this->associationRepository;
    }
}
