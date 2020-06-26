<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
use App\Models\Traits\Stamps;
use Plasticode\Models\NewsSource as BaseNewsSource;

/**
 * @property integer $id
 * @property string $tags
 * @property string $title
 * @property string|null $text
 */
abstract class NewsSource extends BaseNewsSource implements NewsSourceInterface
{
    use Stamps;

    // NewsSourceInterface

    public function creator() : ?User
    {
        return parent::creator();
    }
}
