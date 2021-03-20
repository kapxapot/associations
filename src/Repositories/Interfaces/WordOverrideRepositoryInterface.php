<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordOverrideCollection;
use App\Models\Word;
use App\Models\WordOverride;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface WordOverrideRepositoryInterface extends GetRepositoryInterface
{
    function get(?int $id): ?WordOverride;

    function create(array $data): WordOverride;

    function getLatestByWord(Word $word): ?WordOverride;

    function getAllByWord(Word $word): WordOverrideCollection;
}
