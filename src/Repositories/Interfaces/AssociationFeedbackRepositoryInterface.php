<?php

namespace App\Repositories\Interfaces;

use App\Collections\AssociationFeedbackCollection;
use App\Models\Association;
use App\Models\AssociationFeedback;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;

interface AssociationFeedbackRepositoryInterface extends ChangingRepositoryInterface, FilteringRepositoryInterface
{
    public function get(?int $id): ?AssociationFeedback;

    public function create(array $data): AssociationFeedback;

    public function save(AssociationFeedback $feedback): AssociationFeedback;

    public function getAllByAssociation(Association $association): AssociationFeedbackCollection;
}
