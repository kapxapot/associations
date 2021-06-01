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

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $association = $this->associationRepository->get($id);

        if ($association) {
            $item['name'] = $association->fullName();
            $item['url'] = $association->url();
            $item['language'] = $association->language()->name;
        }

        return $item;
    }
}
