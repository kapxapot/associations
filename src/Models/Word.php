<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Util\Cases;

class Word extends Element
{
    protected static $sortField = 'word';
    
    /**
     * Finds the word by string in the specified language.
     * 
     * Normalized word string expected.
     */
    public static function findInLanguage(Language $language, string $wordStr) : ?Word
    {
        return self::getByLanguage($language)
            ->where('word_bin', $wordStr)
            ->one();
    }
    
    public function associations() : Query
    {
        return Association::getByWord($this);
    }

    private function compareByOtherWord() : \Closure
    {
        return function ($assocA, $assocB) {
            return strcmp(
                $assocA->otherWord($this)->word,
                $assocB->otherWord($this)->word
            );
        };
    }
    
    public function approvedAssociations() : Collection
    {
        return $this->lazy(
            function () {
                return Association::filterApproved($this->associations())
                    ->all()
                    ->orderByFunc($this->compareByOtherWord());
            }
        );
    }

    public function approvedVisibleAssociations() : Collection
    {
        $assocs = $this->approvedAssociations();
        return $this->filterVisibleForMe($assocs);
    }

    public function approvedInvisibleAssociations() : Collection
    {
        $assocs = $this->approvedAssociations();
        return $this->filterInvisibleForMe($assocs);
    }

    private function invisibleCountStr(int $count) : ?string
    {
        if ($count <= 0) {
            return null;
        }

        $isPlural = (self::$cases->numberForNumber($count) == Cases::PLURAL);

        $str = $count . ' ' . self::$cases->caseForNumber('ассоциация', $count) . ' ' . ($isPlural ? 'скрыто' : 'скрыта') . '.';

        return $str;
    }

    public function approvedInvisibleAssociationsStr() : ?string
    {
        $count = $this->approvedInvisibleAssociations()->count();
        return $this->invisibleCountStr($count);
    }
    
    public function unapprovedAssociations() : Collection
    {
        return $this->lazy(
            function () {
                return Association::filterUnapproved($this->associations())
                    ->all()
                    ->orderByFunc($this->compareByOtherWord());
            }
        );
    }

    public function unapprovedVisibleAssociations() : Collection
    {
        $assocs = $this->unapprovedAssociations();
        return $this->filterVisibleForMe($assocs);
    }

    public function unapprovedInvisibleAssociations() : Collection
    {
        $assocs = $this->unapprovedAssociations();
        return $this->filterInvisibleForMe($assocs);
    }

    public function unapprovedInvisibleAssociationsStr() : ?string
    {
        $count = $this->unapprovedInvisibleAssociations()->count();
        return $this->invisibleCountStr($count);
    }

    public function associationsForUser(User $user) : Collection
    {
        return $this->lazy(
            function () use ($user) {
                return $this->associations()
                    ->all()
                    ->where(
                        function ($assoc) use ($user) {
                            return $assoc->isPlayableAgainstUser($user);
                        }
                    );
            }
        );
    }

    public function associatedWords(User $user) : Collection
    {
        return $this->associationsForUser($user)
            ->map(
                function ($assoc) {
                    return $assoc->otherWord($this);
                }
            );
    }
    
    public function url() : ?string
    {
        return self::$linker->word($this);
    }
    
    public function turns() : Query
    {
        return Turn::getByWord($this);
    }
    
    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'word' => $this->word,
            'url' => $this->url(),
            'language' => $this->language()->serialize(),
            'creator' => $this->creator()->serialize(),
            'created_at' => $this->createdAtIso(),
        ];
    }
    
    public function feedbacks() : Query
    {
        return WordFeedback::getByWord($this);
    }
    
    public function feedbackByUser(User $user) : ?Feedback
    {
        return WordFeedback::getByWordAndUser($this, $user);
    }
    
    public function proposedTypos() : array
    {
        return $this->feedbacks()
            ->whereNotNull('typo')
            ->all()
            ->group('typo');
    }
    
    public function proposedDuplicates() : array
    {
        return $this->feedbacks()
            ->whereNotNull('duplicate_id')
            ->all()
            ->group('duplicate_id');
    }
    
    /**
     * Maturity check.
     */
    public function isVisibleForUser(User $user = null) : bool
    {
        // 1. non-mature words are visible for everyone
        // 2. mature words are invisible for non-authed users ($user == null)
        // 3. mature words are visible for non-mature users
        //    only if they used the word

        return 
            !$this->isMature() ||
            ($user !== null &&
                ($user->isMature() || $this->isUsedByUser($user))
            );
    }

    public function isPlayableAgainstUser(User $user) : bool
    {
        // word can't be played against user, if
        //
        // 1. word is mature, user is not mature (maturity check)
        // 2. word is not approved, user disliked the word
        
        return $this->isVisibleForUser($user) &&
            ($this->isApproved() ||
                ($this->isUsedByUser($user) && !$this->isDislikedByUser($user))
            );
    }

    /**
     * Returns the typo provided by current user.
     *
     * @return string|null
     */
    public function currentTypo() : ?string
    {
        $feedback = $this->currentFeedback();

        return (!is_null($feedback) && strlen($feedback->typo) > 0)
            ? $feedback->typo
            : null;
    }

    /**
     * Returns word or current typo with '*' (if any).
     *
     * @return string
     */
    public function displayName() : string
    {
        $typo = $this->currentTypo();

        return is_null($typo)
            ? $this->word
            : $typo . '*';
    }

    /**
     * Returns the original word + current typo.
     *
     * @return string
     */
    public function fullDisplayName() : string
    {
        $name = $this->displayName();

        if ($this->currentTypo() !== null) {
            $name .= ' (' . $this->word . ')';
        }

        return $name;
    }
}
