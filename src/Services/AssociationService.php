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
    public function getOrCreate(Word $first, Word $second, User $user = null, Language $language = null) : Association
    {
        $association =
            $this->getByPair($first, $second, $language)
            ??
            $this->create($first, $second, $user, $language);

        if ($association === null) {
            throw new ApplicationException('Association can\'t be found or added.');
        }

        return $association;
    }

    /**
     * Creates association.
     * 
     * !!!!!!!!!!!!!!!!!!!!!!!
     * Potential problem here:
     *  association can be created by another user
     *  at the same time.
     * !!!!!!!!!!!!!!!!!!!!!!!
     */
    public function create(Word $first, Word $second, User $user, Language $language = null) : Association
    {
        if ($this->getByPair($first, $second, $language) !== null) {
            throw new ApplicationException('Association already exists.');
        }
        
        self::checkPair($first, $second);
        
        list($first, $second) = self::orderPair($first, $second);

        $association = Association::create();
        
        $association->firstWordId = $first->getId();
        $association->secondWordId = $second->getId();
        $association->createdBy = $user->getId();
        
        if ($language !== null) {
            $association->languageId = $language->getId();
        }

        return $association->save();
    }

    public function checkPair(Word $first, Word $second, Language $language = null) : void
    {
        if ($first === null || $second === null) {
            throw new \InvalidArgumentException('Both word must be non-null.');
        }
        
        if ($first->getId() == $second->getId()) {
            throw new \InvalidArgumentException('Words can\'t be the same.');
        }

        $firstLanguage = $first->language();
        $secondLanguage = $second->language();

        if (!$firstLanguage->equals($secondLanguage)) {
            throw new \InvalidArgumentException('Words must be of the same language.');
        }
        
        if ($language !== null && !$firstLanguage->equals($language)) {
            throw new \InvalidArgumentException('Words must be of the specified language.');
        }
    }
    
    public function orderPair(Word $first, Word $second) : array
    {
        return $first->getId() < $second->getId()
            ? [ $first, $second ]
            : [ $second, $first ];
    }
    
    public function getByPair(Word $first, Word $second, Language $language = null) : ?Association
    {
        $this->checkPair($first, $second, $language);
        
        list($first, $second) = $this->orderPair($first, $second);
        
        return Association::getByPair($first, $second);
    }
}
