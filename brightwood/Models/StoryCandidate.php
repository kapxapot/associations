<?php

namespace Brightwood\Models;

use App\Models\Traits\Created;
use App\Models\User;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\CreatedInterface;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $id
 * @property string $jsonData
 * @property string $uuid
 * @method User creator()
 * @method static withCreator(User|callable $creator)
 */
class StoryCandidate extends DbModel implements CreatedInterface, UpdatedAtInterface
{
    use Created;
    use UpdatedAt;

    private ?array $storyData = null;

    /**
     * @return $this
     */
    public function withJsonData(string $jsonData): self
    {
        $this->jsonData = $jsonData;
        $this->storyData = null;

        return $this;
    }

    public function data(): array
    {
        $this->storyData ??= json_decode($this->jsonData, true);

        return $this->storyData;
    }

    public function language(): ?string
    {
        return $this->data()['language'] ?? null;
    }
}
