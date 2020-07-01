<?php

namespace App\Semantics;

use Webmozart\Assert\Assert;

class PartOfSpeech
{
    /**
     * Существительное
     */
    const NOUN = 'noun';

    /**
     * Прилагательное
     */
    const ADJECTIVE = 'adjective';

    /**
     * Глагол
     */
    const VERB = 'verb';

    /**
     * Наречие
     */
    const ADVERB = 'adverb';

    /**
     * Местоимение
     */
    const PRONOUN = 'pronoun';

    /**
     * Числительное
     */
    const NUMERAL = 'numeral';

    /**
     * Предлог
     */
    const PREPOSITION = 'preposition';

    /**
     * Союз
     */
    const CONJUNCTION = 'conjunction';

    /**
     * Частица
     */
    const PREDICATIVE = 'predicative';

    const GOOD = 1;
    const BAD = 2;
    const UGLY = 3;

    /**
     * @var array<string, integer> Known parts of speech and their quality.
     */
    protected static array $known = [
        self::NOUN => self::GOOD,
        self::ADJECTIVE => self::BAD,
        self::VERB => self::UGLY,
        self::ADVERB => self::UGLY,
        self::PRONOUN => self::BAD,
        self::NUMERAL => self::BAD,
        self::PREPOSITION => self::UGLY,
        self::CONJUNCTION => self::UGLY,
        self::PREDICATIVE => self::UGLY,
    ];

    private string $name;
    private int $quality;

    protected function __construct(string $name, int $quality)
    {
        $this->name = $name;
        $this->quality = $quality;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function quality() : int
    {
        return $this->quality;
    }

    public function isGood() : bool
    {
        return $this->quality == self::GOOD;
    }

    public function isBad() : bool
    {
        return $this->quality == self::BAD;
    }

    public function isUgly() : bool
    {
        return $this->quality == self::UGLY;
    }

    protected static function isKnown(?string $name) : bool
    {
        return in_array($name, array_keys(self::$known));
    }

    protected static function getQualityByName(string $name) : ?int
    {
        return self::$known[$name] ?? null;
    }

    public static function getByName(?string $name) : ?self
    {
        if (!self::isKnown($name)) {
            return null;
        }

        $quality = self::getQualityByName($name);

        Assert::notNull($quality);

        return new self($name, $quality);
    }
}
