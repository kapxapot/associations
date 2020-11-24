<?php

namespace App\Models;

use App\Models\Traits\Created;
use Plasticode\Models\Basic\DbModel;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $dislike
 * @property integer $mature
 */
abstract class Feedback extends DbModel
{
    use Created;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return [
            $this->creatorPropertyName,
        ];
    }

    public function isDisliked() : bool
    {
        return self::toBool($this->dislike);
    }

    public function isMature() : bool
    {
        return self::toBool($this->mature);
    }
}
