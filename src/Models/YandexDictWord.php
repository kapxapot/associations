<?php

namespace App\Models;

use App\Collections\PartOfSpeechCollection;
use App\Models\Interfaces\DictWordInterface;
use App\Semantics\PartOfSpeech;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedAtInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $languageId
 * @property string|null $pos
 * @property string|null $response
 * @property string $word
 * @property integer|null $wordId
 * @method Language language()
 * @method Word|null linkedWord()
 * @method static withLanguage(Language|callable $language)
 * @method static withLinkedWord(Word|callable|null $linkedWord)
 */
class YandexDictWord extends DbModel implements CreatedAtInterface, DictWordInterface, UpdatedAtInterface
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['language', 'linkedWord'];
    }

    public function getLanguage(): Language
    {
        return $this->language();
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function matchesWord(Word $word): bool
    {
        return $word->languageId === $this->languageId
            && $word->word === $this->word;
    }

    public function getLinkedWord(): ?Word
    {
        return $this->linkedWord();
    }

    public function linkWord(Word $word): self
    {
        $this->wordId = $word->getId();

        return $this->withLinkedWord($word);
    }

    public function unlinkWord(): self
    {
        $this->wordId = null;

        return $this->withLinkedWord(null);
    }

    public function isValid(): bool
    {
        return $this->pos !== null;
    }

    public function partsOfSpeech(): PartOfSpeechCollection
    {
        return $this->partOfSpeech() !== null
            ? PartOfSpeechCollection::collect($this->partOfSpeech())
            : PartOfSpeechCollection::empty();
    }

    public function partOfSpeech(): ?PartOfSpeech
    {
        return PartOfSpeech::getByName($this->pos);
    }

    public function isGood(): bool
    {
        $partOfSpeech = $this->partOfSpeech();

        return $partOfSpeech && $partOfSpeech->isGood();
    }

    public function isBad(): bool
    {
        $partOfSpeech = $this->partOfSpeech();

        return $partOfSpeech && $partOfSpeech->isBad();
    }

    public function isUgly(): bool
    {
        $partOfSpeech = $this->partOfSpeech();

        return $partOfSpeech === null || $partOfSpeech->isUgly();
    }
}
