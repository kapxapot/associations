<?php

namespace Brightwood\Serialization\Factories;

use Brightwood\Models\Cards\Players\Human;

class HumanSerializer extends Serializer
{
    /**
     * @param Human $obj
     */
    public static function deserialize(object $obj, array $data) : Human
    {
        return $obj;
        // "id": "e9529ed13767cb2422b5",
        // "icon": "👦",
        // "hand": {
        //   "type": "Brightwood\\Models\\Cards\\Sets\\Hand",
        //   "data": {
        //     "cards": [
              
        //     ]
        //   }
        // },
        // "is_inspector": false,
        // "telegram_user_id": 1
    }
}
