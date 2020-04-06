<?php

namespace App\Services;

class AnniversaryService
{
    public function toAnniversary(int $num) : ?int
    {
        return $this->isAnniversary($num)
            ? $this->toAnniversaryNumber($num)
            : null;
    }

    public function isAnniversary(int $num) : bool
    {
        if ($num < 1000) {
            return false;
        }
        
        while ($num >= 100) {
            $num = intdiv($num, 10);
        }
        
        $rem = $num % 10;
        
        return $rem < 2;
    }

    public function toAnniversaryNumber(int $num) : int
    {
        $mult = 1;
        
        while ($num >= 10) {
            $num = intdiv($num, 10);
            $mult *= 10;
        }
        
        return $num * $mult;
    }
}
