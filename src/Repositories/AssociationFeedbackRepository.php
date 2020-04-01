<?php

namespace App\Repositories;

use App\Collections\AssociationFeedbackCollection;
use App\Models\Association;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class AssociationFeedbackRepository extends IdiormRepository implements AssociationFeedbackRepositoryInterface
{
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
