<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;

class Word extends DbModel
{
    use Created;
    
    protected static $sortField = 'word';
    
    // queries
    
    public static function getByLanguage(Language $language) : Query
    {
        return self::baseQuery()
            ->where('language_id', $language->getId());
    }
    
    public static function findInLanguage(Language $language, string $word)
    {
        return self::getByLanguage($language)
            ->where('word_bin', $word)
            ->one();
    }
    
    public static function getCreatedByUser(User $user) : Query
    {
        return self::query()
            ->where('created_by', $user->getId());
    }

    // getters - many
    
    public static function getApproved(Language $language) : Collection
    {
        return Association::getApproved($language)
	        ->map(function ($assoc) {
	            return $assoc->words();
	        })
	        ->flatten()
	        ->distinct();
    }
    
    // properties
    
    public function language() : Language
    {
        return Language::get($this->languageId);
    }
    
    public function associations() : Query
    {
        return Association::getByWord($this);
    }
    
    public function approvedAssociations() : Collection
    {
        return $this->lazy(function () {
            return $this->associations()
                ->all()
                ->where(function ($assoc) {
                    return $assoc->isApproved();
                });
        });
    }
    
    public function unapprovedAssociations() : Collection
    {
        return $this->lazy(function () {
            return $this->associations()
                ->all()
                ->where(function ($assoc) {
                    return !$assoc->isApproved();
                });
        });
    }
    
    public function associationsForUser(User $user) : Collection
    {
        return $this->lazy(function () use ($user) {
            return $this->associations()
                ->all()
                ->where(function ($assoc) use ($user) {
                    return $assoc->isApproved() || $user->getId() === $assoc->creator()->getId();
                });
        });
    }
    
    public function score()
    {
        $approvedAssocs = $this->approvedAssociations();
        $approvedAssocsCount = count($approvedAssocs);
        
        $dislikeCount = $this->dislikes()->count();
        
        $assocCoeff = self::getSettings('words.coeffs.approved_association');
        $dislikeCoeff = self::getSettings('words.coeffs.dislike');
        
        return $approvedAssocsCount * $assocCoeff - $dislikeCount * $dislikeCoeff;
    }
    
    public function isApproved() : bool
    {
        $threshold = self::getSettings('words.approval_threshold');
        
        return $this->score() >= $threshold;
    }

    public function associatedWords(User $user) : Collection
    {
        return $this->associationsForUser($user)
            ->map(function ($assoc) {
                return $assoc->firstWord->getId() === $this->getId()
                    ? $assoc->secondWord()
                    : $assoc->firstWord();
            });
    }
    
    public function url()
    {
        return self::$linker->word($this);
    }
    
    public function turns() : Query
    {
        return Turn::getByWord($this);
    }
    
    public function turnsByUsers()
    {
        return $this
            ->turns()
            ->whereNotNull('user_id')
            ->all()
            ->group('user_id');
    }
    
    public function isApprovedByUsage() : bool
    {
        $threshold = self::getSettings('words.approval_threshold');
        $turnsByUsers = $this->turnsByUsers();
        
        return count($turnsByUsers) >= $threshold;
    }
        
    public function serialize()
    {
        return [
            'id' => $this->getId(),
            'word' => $this->word,
            'url' => self::$linker->abs($this->url()),
            'language' => $this->language()->serialize(),
            'creator' => $this->creator()->serialize(),
            'created_at' => $this->createdAtIso(),
        ];
    }
    
    public function feedbacks() : Query
    {
        return WordFeedback::getByWord($this);
    }
    
    public function feedbackByUser(User $user)
    {
        return WordFeedback::getByWordAndUser($this, $user);
    }
    
    public function currentFeedback()
    {
        $user = self::getCurrentUser();
        
        return $user !== null
            ? $this->feedbackByUser($user)
            : null;
    }
    
    public function proposedTypos()
    {
        return $this->feedbacks()
            ->whereNotNull('typo')
            ->all()
            ->group('typo');
    }
    
    public function proposedDuplicates()
    {
        return $this->feedbacks()
            ->whereNotNull('duplicate_id')
            ->all()
            ->group('duplicate_id');
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
        $threshold = self::getSettings('words.mature_threshold');
        
        return $this->matures()->count() >= $threshold;
    }
}
