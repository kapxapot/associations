<?php

namespace App\Models;

use App\Models\Traits\Created;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $dislike
 * @property integer $mature
 */
abstract class Feedback extends DbModel implements CreatedInterface, UpdatedAtInterface
{
    use Created;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return [
            $this->creatorPropertyName,
        ];
    }

    public function isDisliked(): bool
    {
        return self::toBool($this->dislike);
    }

    public function isMature(): bool
    {
        return self::toBool($this->mature);
    }
}
