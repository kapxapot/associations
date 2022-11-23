<?php

namespace App\Repositories\Generic;

use App\Repositories\Core\RepositoryContext;
use App\Repositories\Traits\MultilingualSearchRepository;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Repositories\Idiorm\Generic\NewsSourceRepository as BaseNewsSourceRepository;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

abstract class NewsSourceRepository extends BaseNewsSourceRepository
{
    use MultilingualSearchRepository;

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $context,
        TagRepositoryInterface $tagRepository,
        $hydrator = null
    )
    {
        parent::__construct($context, $tagRepository, $hydrator);

        $this->init($context);
    }
}
