<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;

abstract class Element extends DbModel
{
    use Created;
    
    // queries
    
    public static function getByLanguage(Language $language) : Query
    {
        return self::baseQuery()
            ->where('language_id', $language->getId());
    }
    
    public static function getCreatedByUser(User $user, Language $language = null) : Query
    {
        $query = ($language !== null)
            ? self::getByLanguage($language)
            : self::query();
        
        return $query->where('created_by', $user->getId());
    }

    public static function filterApproved(Query $query) : Query
    {
        return $query->where('approved', 1);
    }

    public static function filterMature(Query $query) : Query
    {
        return $query->where('mature', 1);
    }
    
    public static function getApproved(Language $language = null) : Query
    {
        $query = ($language !== null)
            ? self::getByLanguage($language)
            : self::query();
        
        return self::filterApproved($query);
    }
    
    // properties
    
    public function language() : Language
    {
        return Language::get($this->languageId);
    }
    
    public abstract function feedbacks() : Query;
    
    public function feedbackByUser(User $user) : ?Feedback
    {
        return WordFeedback::getByWordAndUser($this, $user);
    }
    
    public function currentFeedback() : ?WordFeedback
    {
        $user = self::getCurrentUser();
        
        return $user !== null
            ? $this->feedbackByUser($user)
            : null;
    }
    
    public function dislikes() : Query
    {
        return WordFeedback::filterDisliked($this->feedbacks());
    }
    
    public function matures() : Query
    {
        return WordFeedback::filterMature($this->feedbacks());
    }
    
    public function isApproved() : bool
    {
        return $this->approved === 1;
    }

    public function isMature() : bool
    {
        return $this->mature === 1;
    }
    
    public function isUsedByUser(User $user) : bool
    {
        return $this
            ->turns()
            ->where('user_id', $user->getId())
            ->any();
    }

    public function isDislikedByUser(User $user) : bool
    {
        return
            WordFeedback::filterByCreator(
                $this->dislikes(),
                $user
            )
            ->any();
    }
}
