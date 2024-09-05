<?php

namespace Brightwood\Repositories\Interfaces;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\Story;
use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;

interface StoryRepositoryInterface extends GetRepositoryInterface
{
    /**
     * Can return a deleted story.
     */
    public function get(?int $id): ?Story;

    /**
     * Cannot return a deleted story.
     */
    public function getByUuid(string $uuid): ?Story;

    /**
     * Filters out deleted stories.
     */
    public function getAll(): StoryCollection;

    /**
     * Filters out deleted stories.
     */
    public function getAllByLanguage(?string $langCode = null): StoryCollection;

    public function store(array $data): Story;

    public function save(Story $story): Story;
}
