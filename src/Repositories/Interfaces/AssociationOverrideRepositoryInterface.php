<?php

namespace App\Repositories\Interfaces;

use App\Collections\AssociationOverrideCollection;
use App\Models\Association;
use App\Models\AssociationOverride;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface AssociationOverrideRepositoryInterface extends FilteringRepositoryInterface, GetRepositoryInterface
{
    public function get(?int $id): ?AssociationOverride;

    public function create(array $data): AssociationOverride;

    public function save(AssociationOverride $associationOverride): AssociationOverride;

    public function getLatestByAssociation(Association $association): ?AssociationOverride;

    public function getAllByAssociation(
        Association $association
    ): AssociationOverrideCollection;
}
