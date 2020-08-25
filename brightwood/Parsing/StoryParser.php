<?php

namespace Brightwood\Parsing;

use App\Models\TelegramUser;
use Plasticode\Util\Cases;

class StoryParser
{
    public function parseFor(TelegramUser $tgUser, string $text) : string
    {
        return preg_replace_callback(
            "/{(.+)}/Us",
            fn (array $m) => $this->parseMatch($tgUser, $m[1]),
            $text
        );
    }

    private function parseMatch(TelegramUser $tgUser, string $match) : string
    {
        $parts = explode('|', $match);

        if (count($parts) == 1) {
            return $match;
        }

        $mas = $parts[0];
        $fem = $parts[1];

        $gender = $tgUser->gender();

        return $gender == Cases::MAS
            ? $mas
            : $fem;
    }
}
