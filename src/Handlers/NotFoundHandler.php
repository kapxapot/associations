<?php

namespace App\Handlers;

use Plasticode\Handlers\Traits\NotFound;

use App\Controllers\Controller;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;

class NotFoundHandler extends Controller implements NotFoundHandlerInterface
{
    use NotFound;
}
