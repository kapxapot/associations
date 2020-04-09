<?php

namespace App\Models\Traits;

use App\Models\Language;
use Webmozart\Assert\Assert;

/**
 * @property integer $languageId
 */
trait WithLanguage
{
    protected ?Language $language = null;

    private bool $languageInitialized = false;

    public function language() : Language
    {
        Assert::true($this->languageInitialized);

        return $this->language;
    }

    public function withLanguage(Language $language) : self
    {
        $this->language = $language;
        $this->languageInitialized = true;

        return $this;
    }
}
