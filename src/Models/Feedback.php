<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $dislike
 * @property integer $mature
 */
abstract class Feedback extends DbModel
{
    use Created, UpdatedAt;

    public function isDisliked() : bool
    {
        return self::toBool($this->dislike);
    }

    public function isMature() : bool
    {
        return self::toBool($this->mature);
    }
}
