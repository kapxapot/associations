<?php

namespace App\Generators;

use App\Models\AssociationFeedback;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\ChangingEntityGenerator;

class AssociationFeedbackGenerator extends ChangingEntityGenerator
{
    private AssociationFeedbackRepositoryInterface $associationFeedbackRepository;

    public function __construct(
        GeneratorContext $context,
        AssociationFeedbackRepositoryInterface $associationFeedbackRepository
    )
    {
        parent::__construct($context);

        $this->associationFeedbackRepository = $associationFeedbackRepository;
    }

    protected function entityClass(): string
    {
        return AssociationFeedback::class;
    }

    protected function getRepository(): AssociationFeedbackRepositoryInterface
    {
        return $this->associationFeedbackRepository;
    }

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $associationFeedback = $this->associationFeedbackRepository->get($id);

        if ($associationFeedback) {
            $association = $associationFeedback->association();

            $item['association'] = [
                'name' => $association->fullName(),
                'url' => $association->url()
            ];
        }

        return $item;
    }
}
