<?php

namespace Brightwood\Models\Cards\Moves\Actions;

use Brightwood\Models\Cards\Moves\Actions\Interfaces\SkipActionInterface;

class SkipGiftAction extends GiftAction implements SkipActionInterface
{
    public function getMessage() : string
    {
        return 'Следующий игрок пропускает ход';
    }
}
