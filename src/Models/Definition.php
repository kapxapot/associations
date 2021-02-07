<?php

namespace App\Models;

use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property string|null $jsonData
 * @property string $source
 * @property string $url
 * @property int $wordId
 * @method Word word()
 * @method static withWord(Word|callable $word)
 */
class Definition extends DbModel implements CreatedAtInterface, UpdatedAtInterface
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['word'];
    }

    public function isValid(): bool
    {
        return $this->jsonData !== null;
    }
}
