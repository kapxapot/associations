<?php

namespace App\Models;

use Plasticode\Util\Strings;

class News extends NewsSource
{
    // SearchableInterface

    public function code() : string
    {
        return Strings::doubleBracketsTag(
            'news',
            $this->getId(),
            $this->displayTitle()
        );
    }

    // SerializableInterface

    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->displayTitle(),
            'tags' => Strings::toTags($this->tags),
        ];
    }

    // NewsSourceInterface

    public function displayTitle() : string
    {
        return $this->title;
    }

    public function rawText() : ?string
    {
        return $this->text;
    }
}
