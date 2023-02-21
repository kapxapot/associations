<?php

namespace App\Models;

/**
 * @property string $sberId
 * @property string $state
 */
class SberUser extends AbstractBotUser
{
    // NamedInterface

    public function name(): string
    {
        return 'Сбер ' . $this->getId();
    }
}
