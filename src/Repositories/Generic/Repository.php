<?php

namespace App\Repositories\Generic;

use App\Repositories\Core\RepositoryContext;
use App\Repositories\Traits\MultilingualSearchRepository;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\ObjectProxy;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

abstract class Repository extends IdiormRepository
{
    use MultilingualSearchRepository;

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
}
