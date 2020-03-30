<?php

namespace App\Models;

use App\Collections\AssociationCollection;
use App\Collections\WordCollection;
use Plasticode\Collection;
use Plasticode\Models\Traits\WithUrl;
use Plasticode\Query;
use Plasticode\Util\Cases;
use Webmozart\Assert\Assert;

/**
 * @property string $word
 */
class Word extends LanguageElement
{
    use WithUrl;

    protected ?AssociationCollection $associations = null;

    private bool $associationsInitialized = false;

    public function associations(): AssociationCollection
    {
        Assert::true($this->associationsInitialized);

        return $this->associations;
    }

    public function withAssociations(AssociationCollection $associations): self
    {
        $this->associations = $associations;
        $this->associationsInitialized = true;

        return $this;
    }

    private function compareByOtherWord() : \Closure
    {
        return fn (Association $assocA, Association $assocB): int =>
            strcmp(
                $assocA->otherWord($this)->word,
                $assocB->otherWord($this)->word
            );
    }
    
    public function approvedAssociations() : AssociationCollection
    {
        return $this
            ->associations()
            ->approved()
            ->orderByFunc($this->compareByOtherWord());
    }

    public function approvedVisibleAssociations() : AssociationCollection
    {
        return $this
            ->approvedAssociations()
            ->visibleFor($this->me());
    }

    public function approvedInvisibleAssociations() : AssociationCollection
    {
        return $this
            ->approvedAssociations()
            ->invisibleFor($this->me());
    }

    private function invisibleCountStr(int $count) : ?string
    {
        if ($count <= 0) {
            return null;
        }

        $cases = self::$container->cases;

        $isPlural = ($cases->numberForNumber($count) == Cases::PLURAL);

        $str = $count . ' ' . $cases->caseForNumber('ассоциация', $count) . ' ' . ($isPlural ? 'скрыто' : 'скрыта') . '.';

        return $str;
    }

    public function approvedInvisibleAssociationsStr() : ?string
    {
        $count = $this->approvedInvisibleAssociations()->count();
        return $this->invisibleCountStr($count);
    }
    
    public function unapprovedAssociations() : AssociationCollection
    {
        return $this
            ->associations()
            ->notApproved()
            ->orderByFunc($this->compareByOtherWord());
    }

    public function unapprovedVisibleAssociations() : AssociationCollection
    {
        return $this
            ->unapprovedAssociations()
            ->visibleFor($this->me());
    }

    public function unapprovedInvisibleAssociations() : Collection
    {
        return $this
            ->unapprovedAssociations()
            ->invisibleFor($this->me());
    }

    public function unapprovedInvisibleAssociationsStr() : ?string
    {
        $count = $this->unapprovedInvisibleAssociations()->count();
        return $this->invisibleCountStr($count);
    }

    public function associationsForUser(User $user) : AssociationCollection
    {
        return $this->associations()
            ->all()
            ->where(
                function ($assoc) use ($user) {
                    return $assoc->isPlayableAgainstUser($user);
                }
            );
    }

    public function associatedWords(User $user) : WordCollection
    {
        return $this->associationsForUser($user)
            ->map(
                function ($assoc) {
                    return $assoc->otherWord($this);
                }
            );
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
    public function isVisibleFor(User $user = null) : bool
    {
        // 1. non-mature words are visible for everyone
        // 2. mature words are invisible for non-authed users ($user == null)
        // 3. mature words are visible for non-mature users
        //    only if they used the word

        return 
            !$this->isMature() ||
            ($user !== null &&
                ($user->isMature() || $this->isUsedBy($user))
            );
    }

    public function isPlayableAgainst(User $user) : bool
    {
        // word can't be played against user, if
        //
        // 1. word is mature, user is not mature (maturity check)
        // 2. word is not approved, user disliked the word
        
        return $this->isVisibleFor($user) &&
            ($this->isApproved() ||
                ($this->isUsedBy($user) && !$this->isDislikedBy($user))
            );
    }

    /**
     * Returns the typo provided by the current user.
     */
    public function typoByMe() : ?string
    {
        /** @var WordFeedback */
        $feedback = $this->feedbackByMe();

        return (!is_null($feedback) && strlen($feedback->typo) > 0)
            ? $feedback->typo
            : null;
    }

    /**
     * Returns word or typo by the current user with '*' (if any).
     */
    public function displayName() : string
    {
        $typo = $this->typoByMe();

        return is_null($typo)
            ? $this->word
            : $typo . '*';
    }

    /**
     * Returns the original word + typo by the current user.
     */
    public function fullDisplayName() : string
    {
        $name = $this->displayName();

        if ($this->typoByMe() !== null) {
            $name .= ' (' . $this->word . ')';
        }

        return $name;
    }
}
