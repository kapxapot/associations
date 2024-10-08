<?php

namespace Brightwood\Models\Cards\Players;

use Plasticode\Collections\Generic\Collection;
use Plasticode\Semantics\Gender;

class Bot extends Player
{
    protected string $name;
    protected int $gender;

    public function __construct(
        ?string $name = null,
        ?int $gender = null
    )
    {
        $this->name = $name ?? 'Bot';

        $this->icon =
            Collection::collect(
                '🤖', '👽', '🐵', '🐶', '🐱',
                '🦊', '🐭', '🐹', '🐰', '🐻',
                '🐷', '🐯', '🐺', '🐸', '🦁',
                '🐮', '🐨', '🐼'
            )
            ->random();

        $this->gender = $gender ?? Gender::MAS;
    }

    public function isBot(): bool
    {
        return true;
    }

    /**
     * @return $this
     */
    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return $this
     */
    public function withGender(int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    // NamedInterface

    public function name(): string
    {
        return $this->name;
    }

    // ActorInterface

    public function gender(): ?int
    {
        return $this->gender;
    }

    public function languageCode(): ?string
    {
        return null;
    }

    // SerializableInterface

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data): array
    {
        return parent::serialize([
            'name' => $this->name,
            'gender' => $this->gender
        ]);
    }
}
