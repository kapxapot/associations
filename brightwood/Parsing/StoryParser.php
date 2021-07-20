<?php

namespace Brightwood\Parsing;

use App\Models\Interfaces\GenderedInterface;
use Brightwood\Models\Data\StoryData;
use Plasticode\Util\Cases;

class StoryParser
{
    public function parse(
        GenderedInterface $gendered,
        string $text,
        ?StoryData $data = null
    ): string
    {
        return preg_replace_callback(
            "/{(.+)}/Us",
            fn (array $m) => $this->parseMatch($gendered, $m[1], $data),
            $text
        );
    }

    private function parseMatch(
        GenderedInterface $gendered,
        string $match,
        ?StoryData $data
    ): string
    {
        if ($data) {
            $parsedVar = $this->parseVar($match, $data);

            if ($parsedVar !== null) {
                return $parsedVar;
            }
        }

        return $this->parseGenders($match, $gendered);
    }

    private function parseVar(string $var, StoryData $data): ?string
    {
        return $data[$var] ?? null;
    }

    private function parseGenders(string $str, GenderedInterface $gendered): string
    {
        $parts = explode('|', $str);

        if (count($parts) == 1) {
            return $str;
        }

        $mas = $parts[0];
        $fem = $parts[1];

        $gender = $gendered->gender() ?? Cases::MAS;

        return $gender == Cases::MAS
            ? $mas
            : $fem;
    }
}
