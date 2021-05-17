<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordRelationTypeCollection;
use App\Models\WordRelationType;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface WordRelationTypeRepositoryInterface extends GetRepositoryInterface
{
    public function get(?int $id): ?WordRelationType;

    public function getAll(): WordRelationTypeCollection;
}
