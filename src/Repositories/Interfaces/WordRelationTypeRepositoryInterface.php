<?php

namespace App\Repositories\Interfaces;

use App\Models\WordRelationType;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface WordRelationTypeRepositoryInterface extends GetRepositoryInterface
{
    public function get(?int $id): ?WordRelationType;
}
