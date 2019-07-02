<?php

namespace App\Models;

use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Created;
use Plasticode\Util\Date;

abstract class Feedback extends DbModel
{
    use Created;
    
    public static function filterDisliked(Query $query) : Query
    {
        return $query->where('dislike', 1);
    }
    
    public static function filterMature(Query $query) : Query
    {
        return $query->where('mature', 1);
    }

    public static function filterByCreator(Query $query, User $user) : Query
    {
        return $query->where('created_by', $user->getId());
    }
    
    public function isDisliked() : bool
    {
        return $this->dislike === 1;
    }
    
    public function isMature() : bool
    {
        return $this->mature === 1;
    }

    public function updatedAtIso()
    {
        return Date::iso($this->updatedAt);
    }
}
