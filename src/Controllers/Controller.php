<?php

namespace App\Controllers;

use Plasticode\Controllers\Controller as BaseController;

use App\Models\Language;

class Controller extends BaseController
{
    protected $autoOneColumn = false;
    
    protected function buildParams($settings)
    {
        $params = $settings['params'] ?? [];
        
        $game = $params['game'] ?? null;            
        $language = $params['language'] ?? null;

        if ($language === null) {
            $language = ($game !== null)
                ? $game->language()
                : $this->languageService->getDefaultLanguage();
        }
        
        if ($language !== null) {
            $wordCount = $language->words()->count();
            $wordCountStr = $this->cases->caseForNumber('слово', $wordCount);

            $associationCount = $language->associations()->count();
            $associationCountStr = $this->cases->caseForNumber('ассоциация', $associationCount);
            
            $params['language'] = $language;
            
            $params = array_merge($params, [
                'word_count' => $wordCount,
                'word_count_str' => $wordCountStr,
                'word_anniversary' => $this->isAnniversary($wordCount)
                    ? $this->toAnniversaryNumber($wordCount)
                    : null,
                'association_count' => $associationCount,
                'association_count_str' => $associationCountStr,
                'association_anniversary' => $this->isAnniversary($associationCount)
                    ? $this->toAnniversaryNumber($associationCount)
                    : null,
            ]);
        }
        
        return parent::buildParams(['params' => $params]);
    }
    
    private function isAnniversary(int $num) : bool
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
    
    private function toAnniversaryNumber(int $num) : int
    {
        $mult = 1;
        
        while ($num >= 10) {
            $num = intdiv($num, 10);
            $mult *= 10;
        }
        
        return $num * $mult;
    }
}
