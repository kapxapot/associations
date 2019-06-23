<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;

class Language extends DbModel
{
    const RUSSIAN = 1;

    // properties
    
    public function words() : Query
    {
        return Word::getByLanguage($this)
            ->orderByAsc('word');
    }
    
    public function lastAddedWords(int $limit = null) : Query
    {
        return Word::getByLanguage($this)
            ->orderByDesc('created_at')
            ->limit($limit ?? 10);
    }
    
    public function associations() : Query
    {
        return Association::getByLanguage($this);
    }
    
    public function lastAddedAssociations(int $limit = null) : Query
    {
        return Association::getByLanguage($this)
            ->orderByDesc('created_at')
            ->limit($limit ?? 10);
    }
    
    /**
     * Heavy.
     */
    public function lastApprovedAssociations(int $limit = null) : Collection
    {
        return $this->lazy(function () use ($limit) {
            return Association::getByLanguage($this)
                ->orderByDesc('created_at')
                ->all()
                ->where(function ($assoc) {
                    return $assoc->isApproved();
                })
                ->take($limit ?? 10);
        });
    }
    
    public function serialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
        ];
    }
}
