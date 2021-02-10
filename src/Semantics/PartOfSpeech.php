<?php

namespace App\Semantics;

use App\Collections\PartOfSpeechCollection;

class PartOfSpeech
{
    /**
     * Существительное
     */
    public const NOUN = 'noun';

    /**
     * Прилагательное
     */
    public const ADJECTIVE = 'adjective';

    /**
     * Глагол
     */
    public const VERB = 'verb';

    /**
     * Наречие
     */
    public const ADVERB = 'adverb';

    /**
     * Местоимение
     */
    public const PRONOUN = 'pronoun';

    /**
     * Числительное
     */
    public const NUMERAL = 'numeral';

    /**
     * Предлог
     */
    public const PREPOSITION = 'preposition';

    /**
     * Союз
     */
    public const CONJUNCTION = 'conjunction';

    /**
     * Частица
     */
    public const PREDICATIVE = 'predicative';

    /**
     * Междометие
     */
    public const INTERJECTION = 'interjection';

    public const GOOD = 1;
    public const BAD = 2;
    public const UGLY = 3;

    protected static ?PartOfSpeechCollection $known = null;

    private string $name;
    private string $shortName;
    private int $quality;

    protected function __construct(string $name, string $shortName, int $quality)
    {
        $this->name = $name;
        $this->shortName = $shortName;
        $this->quality = $quality;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function shortName(): string
    {
        return $this->shortName;
    }

    public function quality(): int
    {
        return $this->quality;
    }

    public function isGood(): bool
    {
        return $this->quality == self::GOOD;
    }

    public function isBad(): bool
    {
        return $this->quality == self::BAD;
    }

    public function isUgly(): bool
    {
        return $this->quality == self::UGLY;
    }

    public static function known(): PartOfSpeechCollection
    {
        self::$known ??= PartOfSpeechCollection::collect(
            new self(self::NOUN, 'n.', self::GOOD),
            new self(self::ADJECTIVE, 'adj.', self::BAD),
            new self(self::PRONOUN, 'pron.', self::BAD),
            new self(self::NUMERAL, 'num.', self::BAD),
            new self(self::VERB, 'v.', self::UGLY),
            new self(self::ADVERB, 'adv.', self::UGLY),
            new self(self::PREPOSITION, 'prep.', self::UGLY),
            new self(self::CONJUNCTION, 'conj.', self::UGLY),
            new self(self::PREDICATIVE, 'pred.', self::UGLY),
            new self(self::INTERJECTION, 'interj.', self::UGLY)
        );

        return self::$known;
    }

    public static function getByName(?string $name): ?self
    {
        return self::known()->get($name);
    }
}
