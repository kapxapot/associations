<?php

namespace App\Repositories\Interfaces;

use App\Collections\AssociationFeedbackCollection;
use App\Models\Association;
use App\Models\AssociationFeedback;

interface AssociationFeedbackRepositoryInterface
{
    function create(array $data) : AssociationFeedback;
    function save(AssociationFeedback $feedback) : AssociationFeedback;

    function getAllByAssociation(
        Association $association
    ) : AssociationFeedbackCollection;
}
