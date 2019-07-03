<?php

namespace App\Models;

use Plasticode\Query;

class AssociationFeedback extends Feedback
{
    public static function getByAssociation(Association $association) : Query
    {
        return self::baseQuery()
            ->where('association_id', $association->getId());
    }

    public static function getByAssociationAndUser(Association $association, User $user) : ?AssociationFeedback
    {
        $byAssoc = self::getByAssociation($association);
        return self::filterByCreator($byAssoc, $user)->one();
    }
    
    public function association() : Association
    {
        return Association::get($this->associationId);
    }
}
