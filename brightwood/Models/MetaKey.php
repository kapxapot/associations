<?php

namespace Brightwood\Models;

class MetaKey
{
    const STAGE = 'brightwood_stage';
    const STORY_ID = 'brightwood_story_id';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::STAGE,
            self::STORY_ID,
        ];
    }
}
