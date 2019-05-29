<?php

namespace App\Models;

use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Util\Date;

class AssociationFeedback extends DbModel
{
    use Created;
    
    public static function getByAssociation(Association $association) : Query
    {
        return self::baseQuery()
            ->where('association_id', $association->getId());
    }

    public static function getByAssociationAndUser(Association $association, User $user)
    {
        return self::getByAssociation($association)
            ->where('created_by', $user->getId())
            ->one();
    }
    
    public function association() : Association
    {
        return Association::get($this->associationId);
    }
    
    public function isDisliked() : bool
    {
        return $this->dislike == 1;
    }
    
    public function isMature() : bool
    {
        return $this->mature == 1;
    }
        
    public function updatedAtIso()
    {
        return Date::iso($this->updatedAt);
    }
}
