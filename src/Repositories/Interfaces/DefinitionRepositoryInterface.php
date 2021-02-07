<?php

namespace App\Repositories\Interfaces;

use App\Models\Definition;
use App\Models\Word;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface DefinitionRepositoryInterface extends GetRepositoryInterface
{
    function save(Definition $definition): Definition;

    function store(array $data): Definition;

    function getByWord(Word $word): ?Definition;
}
