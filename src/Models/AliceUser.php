<?php

namespace App\Models;

/**
 * @property string $aliceId
 */
class AliceUser extends AbstractBotUser
{
    // NamedInterface

    public function name(): string
    {
        return 'Алиса ' . $this->getId();
    }
}
