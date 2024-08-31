<?php

namespace Brightwood\Repositories;

use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;

class StoryRepository extends IdiormRepository implements StoryRepositoryInterface
{
    use CreatedRepository;

    protected string $sortField = 'id';

    protected function entityClass(): string
    {
        return Story::class;
    }

    public function get(?int $id): ?Story
    {
        return $this->getEntity($id);
    }

    public function getByUuid(string $uuid): ?Story
    {
        return $this
            ->query()
            ->where('uuid', $uuid)
            ->one();
    }

    public function getAll(): StoryCollection
    {
        return StoryCollection::from(
            $this->query()
        );
    }

    public function getAllByLanguage(?string $langCode = null): StoryCollection
    {
        if (!$langCode) {
            return $this->getAll();
        }

        return StoryCollection::from(
            $this->query()->where('lang_code', $langCode)
        );
    }

    public function store(array $data): Story
    {
        return $this->storeEntity($data);
    }
}
