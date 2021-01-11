<?php

namespace App\Repositories\Interfaces;

use App\Collections\AssociationFeedbackCollection;
use App\Models\Association;
use App\Models\AssociationFeedback;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface AssociationFeedbackRepositoryInterface extends ChangingRepositoryInterface
{
    function get(?int $id): ?AssociationFeedback;
    function create(array $data): AssociationFeedback;
    function save(AssociationFeedback $feedback): AssociationFeedback;
    function getAllByAssociation(Association $association): AssociationFeedbackCollection;
}
