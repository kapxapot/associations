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

    protected function getRepository(): AssociationOverrideRepositoryInterface
    {
        return $this->associationOverrideRepository;
    }

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $override = $this->associationOverrideRepository->get($id);

        if ($override) {
            $association = $override->association();

            $item['association'] = [
                'name' => $association->fullName(),
                'url' => $association->url(),
            ];
        }

        return $item;
    }
}
