<?php

namespace App\Repositories\Interfaces;

use App\Collections\AssociationFeedbackCollection;
use App\Models\Association;

interface AssociationFeedbackRepositoryInterface
{
    function getAllByAssociation(
        Association $association
    ) : AssociationFeedbackCollection;
}
