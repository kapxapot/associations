<?php

namespace App\Repositories\Traits;

use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Interfaces\ArrayableInterface;

trait CollectingRepository
{
    abstract protected function collect(ArrayableInterface $arrayable): DbModelCollection;
}
