<?php

namespace App\Services;

use Plasticode\Util\Cases;

class CasesService
{
    private Cases $cases;

    public function __construct(
        Cases $cases
    )
    {
        $this->cases = $cases;
    }

    /**
     * 123 ассоциации.
     */
    public function associationCount(int $count) : string
    {
        return $count . ' ' . $this->cases->caseForNumber('ассоциация', $count);
    }

    /**
     * 123 ассоциации скрыто.
     */
    public function invisibleAssociationCountStr(int $count) : ?string
    {
        if ($count <= 0) {
            return null;
        }

        $isPlural = ($this->cases->numberForNumber($count) == Cases::PLURAL);

        return
            $this->associationCount($count) .
            ' ' . ($isPlural ? 'скрыто' : 'скрыта');
    }

    /**
     * 123 хода.
     */
    public function turnCount(int $count) : string
    {
        return $count . ' ' . $this->cases->caseForNumber('ход', $count);
    }

    /**
     * 123 слова.
     */
    public function wordCount(int $count) : string
    {
        return $count . ' ' . $this->cases->caseForNumber('слово', $count);
    }
}
