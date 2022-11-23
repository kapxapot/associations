<?php

namespace App\Repositories\Traits;

use App\Auth\Interfaces\AuthInterface;
use App\Config\Config;
use App\Data\MultilingualSearcher;
use App\Repositories\Core\RepositoryContext;
use Plasticode\Data\Query;

trait MultilingualSearchRepository
{
    /**
     * Override.
     */
    private AuthInterface $auth;

    private Config $config;
    private MultilingualSearcher $searcher;

    private function init(RepositoryContext $context): void
    {
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

    protected function multiSearch(
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
