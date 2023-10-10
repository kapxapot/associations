<?php

namespace Brightwood\Models\Data;

use Plasticode\Models\Generic\Model;

class StoryData extends Model
{
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        if (!$data) {
            $this->init();
        }
    }

    protected function init(): void
    {
    }
}
