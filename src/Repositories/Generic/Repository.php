<?php

namespace App\Repositories\Generic;

use App\Auth\Interfaces\AuthInterface;
use App\Config\Config;
use App\Data\MultilingualSearcher;
use App\Repositories\Core\RepositoryContext;
use Plasticode\Data\Query;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

abstract class Repository extends IdiormRepository
{
    /**
     * Override.
     */
    private AuthInterface $auth;

    private Config $config;
    private MultilingualSearcher $searcher;

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $context,
        $hydrator = null
    )
    {
        parent::__construct($context, $hydrator);

        $this->auth = $context->auth();
        $this->config = $context->config();
        $this->searcher = $context->searcher();
    }

    protected function auth(): AuthInterface
    {
        return $this->auth;
    }

    protected function config(): Config
    {
        return $this->config;
    }

    protected function searcher(): MultilingualSearcher
    {
        return $this->searcher;
    }

    protected function search(
        Query $query,
        string $filter,
        string $where,
        int $paramCount = 1
    ): Query
    {
        return $this->searcher()->search(
            $this->config()->langCode(),
            $query,
            mb_strtolower($filter),
            $where,
            $paramCount
        );
    }
}
