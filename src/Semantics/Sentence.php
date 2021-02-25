<?php

namespace App\Semantics;

use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Util\Arrays;

class Sentence
{
    /**
     * Joins parts into a sentence such as "a, b, c".
     *
     * @param array|ArrayableInterface $array
     */
    public static function join(
        $array,
        ?string $commaDelimiter = null
    ) : string
    {
        return implode(
            $commaDelimiter ?? ', ',
            Arrays::adopt($array)
        );
    }

    /**
     * Joins homogeneous parts into a sentence such as "a, b и c".
     *
     * @param array|ArrayableInterface $array
     */
    public static function homogeneousJoin(
        $array,
        ?string $commaDelimiter = null,
        ?string $andDelimiter = null
    ) : string
    {
        $chunks = Arrays::adopt($array);

        $commaDelimiter ??= ', ';
        $andDelimiter ??= ' и ';

        // a
        // a и b
        // a, b и c

        $result = '';
        $count = count($chunks);

        for ($index = 1; $index <= $count; $index++) {
            $chunk = $chunks[$count - $index];

            switch ($index) {
                case 1:
                    $result = $chunk;
                    break;

                case 2:
                    $result = $chunk . $andDelimiter . $result;
                    break;

                default:
                    $result = $chunk . $commaDelimiter . $result;
            }
        }

        return $result;
    }

    /**
     * Ensures that the string ends with one (and only one) period ('.').
     */
    public static function tailPeriod(string $str): string
    {
        return trim($str, ".") . '.';
    }
}
