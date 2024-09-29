<?php

namespace Brightwood\Models;

use Brightwood\Collections\LanguageCollection;
use Brightwood\Models\Interfaces\CommandProviderInterface;
use Plasticode\Collections\Generic\StringCollection;

class Language implements CommandProviderInterface
{
    public string $code;
    public string $name;
    public string $emoji;

    const BE = 'be';
    const DE = 'de';
    const EN = 'en';
    const ES = 'es';
    const FR = 'fr';
    const HI = 'hi';
    const HY = 'hy';
    const IT = 'it';
    const JA = 'ja';
    const KA = 'ka';
    const KO = 'ko';
    const PT = 'pt';
    const RU = 'ru';
    const UK = 'uk';
    const ZH = 'zh';

    const UNKNOWN = 'xx';

    public function __construct(string $code, string $name, string $emoji)
    {
        $this->code = $code;
        $this->name = $name;
        $this->emoji = $emoji;
    }

    public static function all(): LanguageCollection
    {
        return LanguageCollection::collect(
            new Language(self::BE, 'Беларуская', '🇧🇾'),
            new Language(self::DE, 'Deutsch', '🇩🇪'),
            new Language(self::EN, 'English', '🇬🇧'),
            new Language(self::ES, 'Español', '🇪🇸'),
            new Language(self::FR, 'Français', '🇫🇷'),
            new Language(self::HI, 'हिन्दी', '🇮🇳'),
            new Language(self::HY, 'Հայերեն', '🇦🇲'),
            new Language(self::IT, 'Italiano', '🇮🇹'),
            new Language(self::JA, '日本語', '🇯🇵'),
            new Language(self::KA, 'ქართული', '🇬🇪'),
            new Language(self::KO, '한국어', '🇰🇷'),
            new Language(self::PT, 'Português', '🇵🇹'),
            new Language(self::RU, 'Русский', '🇷🇺'),
            new Language(self::UK, 'Українська', '🇺🇦'),
            new Language(self::ZH, '中文', '🇨🇳'),
        );
    }

    public static function allCodes(): StringCollection
    {
        return self::all()->stringize(
            fn (Language $l) => $l->code
        );
    }

    public static function fromCode(string $code): Language
    {
        $language = self::all()->first(
            fn (Language $l) => $l->code === $code
        );

        if ($language) {
            return $language;
        }

        return new Language(
            self::UNKNOWN,
            'Unknown language',
            '🌐'
        );
    }

    public static function purifyCode(?string $code): string
    {
        return self::isKnown($code)
            ? $code
            : self::UNKNOWN;
    }

    public static function isKnown(?string $code): bool
    {
        return self::allCodes()->contains($code);
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return "{$this->emoji} {$this->name}";
    }

    // CommandProviderInterface

    public function toCommand(): Command
    {
        return new Command(
            'story_lang_' . $this->code,
            $this->toString()
        );
    }
}
