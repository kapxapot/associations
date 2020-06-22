<?php

namespace App\Models;

use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\TaggedInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tagged;

/**
 * @property integer $id
 * @property string $title
 * @property string|null $text
*/
class News extends DbModel implements TaggedInterface
{
    use FullPublished;
    use Stamps;
    use Tagged;

    protected function requiredWiths(): array
    {
        return [
            $this->tagLinksPropertyName
        ];
    }
}
