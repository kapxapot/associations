<?php

namespace App\Repositories;

use App\Collections\AssociationFeedbackCollection;
use App\Models\Association;
use App\Models\AssociationFeedback;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class AssociationFeedbackRepository extends IdiormRepository implements AssociationFeedbackRepositoryInterface
{
    protected function entityClass() : string
    {
        return AssociationFeedback::class;
    }

    public function get(?int $id) : ?AssociationFeedback
    {
        return $this->getEntity($id);
    }

    public function create(array $data) : AssociationFeedback
    {
        return $this->createEntity($data);
    }

    public function save(AssociationFeedback $feedback) : AssociationFeedback
    {
        return $this->saveEntity($feedback);
    }

    public function getAllByAssociation(
        Association $association
    ) : AssociationFeedbackCollection
    {
        return AssociationFeedbackCollection::from(
            $this
                ->query()
                ->where('association_id', $association->getId())
        );
    }
}
