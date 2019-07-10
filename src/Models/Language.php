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
        $query = Word::getApproved($this);

        return Word::filterNonmature($query)
            ->limit($limit ?? 10);
    }
    
    public function associations() : Query
    {
        return Association::getByLanguage($this);
    }
    
    public function lastAddedAssociations(int $limit = null) : Query
    {
        $query = Association::getApproved($this);

        return Association::filterNonmature($query)
            ->limit($limit ?? 10);
    }
    
    public function serialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
        ];
    }
}
