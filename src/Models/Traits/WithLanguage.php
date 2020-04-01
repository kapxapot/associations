<?php

namespace App\Models\Traits;

use App\Models\Language;

/**
 * @property integer $languageId
 */
trait WithLanguage
{
    protected ?Language $language = null;

    public function language() : Language
    {
        return $this->language;
    }

    public function withLanguage(Language $language) : self
    {
        $this->language = $language;
        return $this;
    }
}
