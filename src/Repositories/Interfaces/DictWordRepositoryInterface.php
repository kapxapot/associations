<?php

namespace App\Repositories\Interfaces;

use App\Collections\DictWordCollection;
use App\Models\Interfaces\DictWordInterface;
use App\Models\Language;
use App\Models\Word;

interface DictWordRepositoryInterface extends WithLanguageRepositoryInterface
{
    public function store(array $data): DictWordInterface;

    public function save(DictWordInterface $dictWord): DictWordInterface;

    public function getByWord(Word $word): ?DictWordInterface;

    public function getByWordStr(Language $language, string $wordStr): ?DictWordInterface;

    /**
     * Returns dict words without associated words that need to be updated.
     *
     * @param integer $ttlMin Update time-to-live in minutes.
     */
    public function getAllDanglingOutOfDate(
        int $ttlMin,
        int $limit = 0
    ): DictWordCollection;
}
