<?php

namespace App\Services;

use Plasticode\Contained;
use Plasticode\Exceptions\ApplicationException;

use App\Models\Association;
use App\Models\Language;
use App\Models\User;
use App\Models\Turn;
use App\Models\Word;

class AssociationService extends Contained
{
    public function create(Word $first, Word $second, User $user = null, Language $language = null)
    {
        if (Association::getByPair($first, $second) !== null) {
            throw new ApplicationException('Association already exists.');
        }
        
        self::checkPair($first, $second);
        
        list($first, $second) = self::orderPair($first, $second);

        $association = Association::create();
        
        $association->firstWordId = $first->getId();
        $association->secondWordId = $second->getId();
        
        if ($user !== null) {
            $association->createdBy = $user->getId();
        }
        
        if ($language !== null) {
            $association->languageId = $language->getId();
        }

        return $association->save();
    }

    public function checkPair(Word $first, Word $second, Language $language = null)
    {
        if ($first === null || $second === null) {
            throw new \InvalidArgumentException('Both word must be non-null.');
        }
        
        if ($first->getId() == $second->getId()) {
            throw new \InvalidArgumentException('Words can\'t be the same.');
        }
        
        if ($language !== null) {
            $firstLanguage = $first->language();
            $secondLanguage = $second->language();
            
            if ($firstLanguage->getId() != $language->getId() || $secondLanguage->getId() != $language->getId()) {
                throw new \InvalidArgumentException('Both words must be of the specified language.');
            }
        }
    }
    
    public function orderPair(Word $first, Word $second)
    {
        return $first->getId() < $second->getId()
            ? [ $first, $second ]
            : [ $second, $first ];
    }

    public function findAnswer(Turn $turn)
    {
        return $turn
            ->word()
            ->associatedWords($turn->user())
            ->whereNotIn('id', $turn->game()->words()->ids())
            ->random();
    }
}
