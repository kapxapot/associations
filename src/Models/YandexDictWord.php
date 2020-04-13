<?php

namespace App\Models;

use App\Models\Interfaces\DictWordInterface;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property string $word
 * @property integer|null $wordId
 * @property integer $languageId
 * @property string|null $response
 * @property string|null $pos
 * @method Language language()
 * @method Word wordEntity()
 * @method self withLanguage(Language|callable $language)
 * @method self withWordEntity(Word|callable|null $wordEntity)
 */
class YandexDictWord extends DbModel implements DictWordInterface
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['language', 'wordEntity'];
    }

    public function isValid() : bool
    {
        return !is_null($this->pos);
    }
}
