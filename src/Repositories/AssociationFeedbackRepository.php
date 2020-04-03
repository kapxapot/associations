<?php

namespace App\Repositories;

use App\Collections\AssociationFeedbackCollection;
use App\Models\Association;
use App\Models\AssociationFeedback;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class AssociationFeedbackRepository extends IdiormRepository implements AssociationFeedbackRepositoryInterface
{
    protected string $entityClass = AssociationFeedback::class;

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
