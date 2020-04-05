<?php

namespace App\Models;

use App\Models\Interfaces\DictWordInterface;
use App\Models\Traits\WithLanguage;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;
use Webmozart\Assert\Assert;

/**
 * @property string $word
 * @property integer|null $wordId
 * @property integer $languageId
 * @property string|null $response
 * @property string|null $pos
 */
class YandexDictWord extends DbModel implements DictWordInterface
{
    use CreatedAt, UpdatedAt, WithLanguage;

    protected ?Word $wordEntity = null;

    private bool $wordEntityInitialized = false;

    public function wordEntity() : ?Word
    {
        Assert::true($this->wordEntityInitialized);

        return $this->wordEntity;
    }

    public function withWordEntity(?Word $wordEntity) : self
    {
        $this->wordEntity = $wordEntity;
        $this->wordEntityInitialized = true;

        return $this;
    }

    public function isValid() : bool
    {
        return !is_null($this->pos);
    }
}
