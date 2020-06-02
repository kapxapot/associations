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
 * @method Word|null linkedWord()
 * @method static withLanguage(Language|callable $language)
 * @method static withLinkedWord(Word|callable|null $linkedWord)
 */
class YandexDictWord extends DbModel implements DictWordInterface
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return [
            'language',
            'linkedWord'
        ];
    }

    public function getLanguage() : Language
    {
        return $this->language();
    }

    public function getWord() : string
    {
        return $this->word;
    }

    public function getLinkedWord(): ?Word
    {
        return $this->linkedWord();
    }

    /**
     * @return static
     */
    public function linkWord(Word $word) : self
    {
        $this->wordId = $word->getId();

        return $this->withLinkedWord($word);
    }

    /**
     * @return static
     */
    public function unlinkWord() : self
    {
        $this->wordId = null;

        return $this->withLinkedWord(null);
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
