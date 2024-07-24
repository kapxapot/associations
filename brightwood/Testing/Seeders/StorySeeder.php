<?php

namespace Brightwood\Testing\Seeders;

use Brightwood\Models\Stories\Core\Story;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class StorySeeder implements ArraySeederInterface
{
    private array $stories;

    public function __construct(Story ...$stories)
    {
        $this->stories = $stories;
    }

    /**
     * @return Story[]
     */
    public function seed(): array
    {
        return $this->stories;
    }
}
