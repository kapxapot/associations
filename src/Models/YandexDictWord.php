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
 * @method static withLanguage(Language|callable $language)
 */
class YandexDictWord extends DbModel implements DictWordInterface
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['language'];
    }

    public function isValid() : bool
    {
        return !is_null($this->pos);
    }

    public function partOfSpeech() : string
    {
        return $this->pos;
    }

    public function isNoun() : bool
    {
        return $this->pos == 'noun';
    }
}
