<?php

namespace Brightwood\Models\Data;

use Plasticode\Models\Generic\Model;

abstract class StoryData extends Model
{
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        if (!$data) {
            $this->init();
        }
    }

    abstract protected function init(): void;
}
