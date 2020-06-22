<?php

namespace App\Models;

use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Models\Interfaces\TaggedInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tagged;

/**
 * @property integer $id
 * @property integer|null $parentId
 * @property string $slug
 * @property string $title
 * @property string|null $text
 */
class Page extends DbModel implements PageInterface, TaggedInterface
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

    public function getSulg() : string
    {
        return $this->slug;
    }
}
