<?php

namespace App\Hydrators;

use App\Models\News;
use Plasticode\Hydrators\Basic\NewsSourceHydrator;
use Plasticode\Models\Basic\DbModel;

class NewsHydrator extends NewsSourceHydrator
{
    /**
     * @param News $entity
     */
    public function hydrate(DbModel $entity) : News
    {
        $entity = parent::hydrate($entity);

        return $entity
            ->withUrl(
                fn () => $this->linker->news($entity->getId())
            );
    }
}
