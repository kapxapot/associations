<?php

namespace Brightwood\Repositories;

use App\Models\TelegramUser;
use App\Repositories\Core\RepositoryContext;
use Brightwood\Collections\StoryCollection;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Repositories\Interfaces\StoryRepositoryInterface;
use Brightwood\Services\TelegramUserService;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\CreatedRepository;

class StoryRepository extends IdiormRepository implements StoryRepositoryInterface
{
    use CreatedRepository;

    private TelegramUserService $telegramUserService;

    protected string $sortField = 'id';

    /**
     * @param HydratorInterface|ObjectProxy $hydrator
     */
    public function __construct(
        RepositoryContext $context,
        $hydrator,
        TelegramUserService $telegramUserService
    )
    {
        parent::__construct($context, $hydrator);

        $this->telegramUserService = $telegramUserService;
    }

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

    public function getAllEditableBy(TelegramUser $tgUser): StoryCollection
    {
        $query = $this
            ->query()
            ->whereNotNull('uuid');

        $user = $tgUser->user();

        if ($this->telegramUserService->isAdmin($tgUser)) {
            $query = $query->whereRaw(
                'created_by is null or created_by = ?',
                [$user->getId()]
            );
        } else {
            $query = $this->filterByCreator($query, $user);
        }

        return StoryCollection::from($query);
    }
}
