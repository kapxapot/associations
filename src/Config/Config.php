<?php

namespace App\Config;

use Plasticode\Contained;

class Config extends Contained
{
    public function wordMinLength()
    {
        return $this->getSettings('view_globals.word_min_length') ?? 1;
    }
    
    public function wordMaxLength()
    {
        return $this->getSettings('view_globals.word_max_length') ?? 250;
    }
}
