<?php

namespace Brightwood\Parsing;

use App\Models\TelegramUser;
use Brightwood\Models\Data\StoryData;
use Plasticode\Util\Cases;

class StoryParser
{
    public function parse(
        TelegramUser $tgUser,
        string $text,
        ?StoryData $data = null
    ) : string
    {
        return preg_replace_callback(
            "/{(.+)}/Us",
            fn (array $m) => $this->parseMatch($tgUser, $m[1], $data),
            $text
        );
    }

    private function parseMatch(
        TelegramUser $tgUser,
        string $match,
        ?StoryData $data
    ) : string
    {
        if ($data) {
            $parsedVar = $this->parseVar($match, $data);

            if ($parsedVar !== null) {
                return $parsedVar;
            }
        }

        return $this->parseGenders($match, $tgUser);
    }

    private function parseVar(string $var, StoryData $data) : ?string
    {
        return $data[$var];
    }

    private function parseGenders(string $str, TelegramUser $tgUser) : string
    {
        $parts = explode('|', $str);

        if (count($parts) == 1) {
            return $str;
        }

        $mas = $parts[0];
        $fem = $parts[1];

        $gender = $tgUser->gender();

        return $gender == Cases::MAS
            ? $mas
            : $fem;
    }
}
