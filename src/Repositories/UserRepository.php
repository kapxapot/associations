<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Core\RepositoryContext;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Traits\MultilingualSearchRepository;
use Plasticode\Data\Query;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Repositories\Idiorm\Traits\SearchRepository;
use Plasticode\Repositories\Idiorm\UserRepository as BaseUserRepository;

class UserRepository extends BaseUserRepository implements UserRepositoryInterface
{
    use MultilingualSearchRepository;
    use SearchRepository;

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $context,
        $hydrator = null
    )
    {
        parent::__construct($context, $hydrator);

        $this->init($context);
    }

    protected function entityClass(): string
    {
        return User::class;
    }

    public function get(?int $id): ?User
    {
        return $this->getEntity($id);
    }

    public function store(array $data): User
    {
        return $this->storeEntity($data);
    }

    // SearchRepository

    public function applyFilter(Query $query, string $filter): Query
    {
        return $this->multiSearch(
            $query,
            $filter,
            '(login like ? or name like ?)',
            2
        );
    }
}
