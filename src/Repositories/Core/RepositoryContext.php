<?php

namespace App\Repositories\Core;

use App\Auth\Interfaces\AuthInterface;
use App\Config\Config;
use App\Data\MultilingualSearcher;
use Plasticode\Auth\Access;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Data\DbMetadata;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext as BaseRepositoryContext;

class RepositoryContext extends BaseRepositoryContext
{
    private AuthInterface $auth;
    private Config $config;
    private MultilingualSearcher $searcher;

    public function __construct(
        Access $access,
        AuthInterface $auth,
        CacheInterface $cache,
        DbMetadata $dbMetadata,
        Config $config,
        MultilingualSearcher $searcher
    )
    {
        parent::__construct($access, $auth, $cache, $dbMetadata);

        $this->auth = $auth;
        $this->config = $config;
        $this->searcher = $searcher;
    }

    /**
     * Override that returns {@see App\Auth\Interfaces\AuthInterface}.
     */
    public function auth(): AuthInterface
    {
        return $this->auth;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function searcher(): MultilingualSearcher
    {
        return $this->searcher;
    }
}
