<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Util\Date;

abstract class Element extends DbModel
{
    use Created;
    
    public static function getByLanguage(Language $language) : Query
    {
        return self::query()
            ->where('language_id', $language->getId());
    }
    
    public static function getCreatedByUser(User $user, Language $language = null) : Query
    {
        $query = ($language !== null)
            ? self::getByLanguage($language)
            : self::query();
        
        return self::filterByCreator($query, $user);
    }

    public static function getPublic(Language $language = null) : Query
    {
        $query = ($language !== null)
            ? self::getByLanguage($language)
            : self::query();
        
        return self::filterNonmature($query);
    }

    /**
     * Returns elements out of date.
     *
     * @param integer $ttlMin Time to live in minutes
     * @return Query
     */
    public static function getOutOfDate(int $ttlMin) : Query
    {
        return self::query()
            ->whereRaw('(updated_at < date_sub(now(), interval ' . $ttlMin . ' minute))')
            ->orderByAsc('updated_at');
    }
    
    public static function getApproved(Language $language = null) : Query
    {
        $query = ($language !== null)
            ? self::getByLanguage($language)
            : self::query();
        
        return self::filterApproved($query)
            ->orderByDesc('approved_updated_at');
    }

    // filters

    public static function filterApproved(Query $query) : Query
    {
        return $query->where('approved', 1);
    }

    public static function filterUnapproved(Query $query) : Query
    {
        return $query->where('approved', 0);
    }

    public static function filterMature(Query $query) : Query
    {
        return $query->where('mature', 1);
    }

    public static function filterNonmature(Query $query) : Query
    {
        return $query->where('mature', 0);
    }

    // filtered collections
    
    protected function filterVisibleForMe(Collection $elements) : Collection
    {
        return $elements
            ->where(
                function ($element) {
                    return $element->isVisibleForMe();
                }
            );
    }

    protected function filterInvisibleForMe(Collection $elements) : Collection
    {
        return $elements
            ->where(
                function ($element) {
                    return !$element->isVisibleForMe();
                }
            );
    }
    
    public function language() : Language
    {
        return Language::get($this->languageId);
    }
    
    public abstract function feedbacks() : Query;
    
    public abstract function feedbackByUser(User $user) : ?Feedback;
    
    public function currentFeedback() : ?Feedback
    {
        $user = self::$container->auth->getUser();
        
        return !is_null($user)
            ? $this->feedbackByUser($user)
            : null;
    }
    
    public function dislikes() : Query
    {
        return Feedback::filterDisliked($this->feedbacks());
    }
    
    public function matures() : Query
    {
        return Feedback::filterMature($this->feedbacks());
    }
    
    public function isApproved() : bool
    {
        return $this->approved == 1;
    }

    public function isMature() : bool
    {
        return $this->mature == 1;
    }

    public function isDislikedByUser(User $user) : bool
    {
        return
            Feedback::filterByCreator(
                $this->dislikes(),
                $user
            )
            ->any();
    }

    public abstract function turns() : Query;
    
    public function turnsByUsers() : array
    {
        return Turn::groupByUsers($this->turns());
    }
    
    public function isUsedByUser(User $user) : bool
    {
        return
            Turn::filterByUser(
                $this->turns(),
                $user
            )
            ->any();
    }

    public abstract function isVisibleForUser(User $user = null) : bool;

    public abstract function isPlayableAgainstUser(User $user) : bool;

    public function isVisibleForMe() : bool
    {
        $me = self::getCurrentUser();
        return $this->isVisibleForUser($me);
    }

    public function isPlayableAgainstMe() : bool
    {
        $me = self::getCurrentUser();
        return $this->isPlayableAgainstUser($me);
    }

    public function updatedAtIso() : string
    {
        return Date::iso($this->updatedAt);
    }

    public function approvedUpdatedAtIso() : ?string
    {
        return !is_null($this->approvedUpdatedAt)
            ? Date::iso($this->approvedUpdatedAt)
            : null;
    }

    public function matureUpdatedAtIso() : ?string
    {
        return !is_null($this->matureUpdatedAt)
            ? Date::iso($this->matureUpdatedAt)
            : null;
    }
}
