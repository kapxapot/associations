<?php

namespace App\Repositories\Interfaces;

use App\Collections\WordOverrideCollection;
use App\Models\Word;
use App\Models\WordOverride;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface WordOverrideRepositoryInterface extends GetRepositoryInterface
{
    public function get(?int $id): ?WordOverride;

    public function create(array $data): WordOverride;

    public function save(WordOverride $wordOverride): WordOverride;

    public function getLatestByWord(Word $word): ?WordOverride;

    public function getAllByWord(Word $word): WordOverrideCollection;
}
