<?php

namespace App\Repositories\Interfaces;

use App\Models\Definition;
use App\Models\Word;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface DefinitionRepositoryInterface extends GetRepositoryInterface
{
    public function get(?int $id): ?Definition;

    public function store(array $data): Definition;

    public function save(Definition $definition): Definition;

    public function delete(Definition $definition): bool;

    public function getByWord(Word $word): ?Definition;
}
