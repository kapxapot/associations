<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Models\Traits\UpdatedAt;
use Plasticode\Util\Convert;

/**
 * @property integer $dislike
 * @property integer $mature
 */
abstract class Feedback extends DbModel
{
    use Created, UpdatedAt;
    
    public function isDisliked() : bool
    {
        return Convert::fromBit($this->dislike);
    }

    public function isMature() : bool
    {
        return Convert::fromBit($this->mature);
    }
}
