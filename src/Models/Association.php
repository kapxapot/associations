<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;

class Association extends DbModel
{
    use Created;
    
    // queries
    
    public static function getByWord(Word $word) : Query
    {
        return self::baseQuery()
		    ->whereAnyIs([
                [ 'first_word_id' => $word->getId() ],
                [ 'second_word_id' => $word->getId() ],
            ]);
    }
    
    public static function getByLanguage(Language $language) : Query
    {
        return self::baseQuery()
		    ->where('language_id', $language->getId());
    }
    
    // getters - many
    
    public static function getApproved(Language $language = null) : Collection
    {
        return self::staticLazy(function () use ($language) {
            $query = ($language !== null)
                ? self::getByLanguage($language)
                : self::query();
            
            return $query
                ->all()
                ->where(function ($assoc) {
                    return $assoc->isApproved();
                });
        });
    }
    
    // getters - one
    
    public static function getByPair(Word $first, Word $second, Language $language = null)
    {
        $service = self::service();
        
        $service->checkPair($first, $second, $language);
        
        list($first, $second) = $service->orderPair($first, $second);
        
        return self::baseQuery()
            ->where('first_word_id', $first->getId())
            ->where('second_word_id', $second->getId())
            ->one();
    }

    // properties
    
    private static function service()
    {
        return self::$container->associationService;
    }
        
    public function language() : Language
    {
        return Language::get($this->languageId);
    }
    
    public function words() : Collection
    {
        return Collection::make([$this->firstWord(), $this->secondWord()]);
    }

    public function firstWord()
    {
        return Word::get($this->firstWordId);
    }
    
    public function secondWord()
    {
        return Word::get($this->secondWordId);
    }
    
    public function url()
    {
        return self::$linker->association($this);
    }
    
    public function turns() : Query
    {
        return Turn::getByAssociation($this);
    }
    
    public function turnsByUsers()
    {
        return $this
            ->turns()
            ->whereNotNull('user_id')
            ->all()
            ->group('user_id');
    }
    
    public function score()
    {
        $turnsByUsers = $this->turnsByUsers();
        $turnCount = count($turnsByUsers);
        
        $dislikeCount = $this->dislikes()->count();
        
        $usageCoeff = self::getSettings('associations.coeffs.usage');
        $dislikeCoeff = self::getSettings('associations.coeffs.dislike');
        
        return $turnCount * $usageCoeff - $dislikeCount * $dislikeCoeff;
    }
    
    public function isApproved() : bool
    {
        $threshold = self::getSettings('associations.approval_threshold');
        
        return $this->score() >= $threshold;
    }
    
    public function feedbacks() : Query
    {
        return AssociationFeedback::getByAssociation($this);
    }
    
    public function feedbackByUser(User $user)
    {
        return AssociationFeedback::getByAssociationAndUser($this, $user);
    }
    
    public function currentFeedback()
    {
        $user = self::getCurrentUser();
        
        return $user !== null
            ? $this->feedbackByUser($user)
            : null;
    }
    
    public function dislikes() : Query
    {
        return $this->feedbacks()
            ->where('dislike', 1);
    }
    
    public function matures() : Query
    {
        return $this->feedbacks()
            ->where('mature', 1);
    }

    public function isMature() : bool
    {
        $threshold = self::getSettings('associations.mature_threshold');
        
        return $this->matures()->count() >= $threshold;
    }
}
