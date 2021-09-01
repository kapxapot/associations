<?php

namespace App\Semantics;

use Webmozart\Assert\Assert;

class Scope
{
    const DISABLED = 1;
    const INACTIVE = 2;
    const PRIVATE = 3;
    const PUBLIC = 4;
    const COMMON = 5;

    /**
     * @return integer[]
     */
    public static function publicScopes(): array
    {
        return [self::PUBLIC, self::COMMON];
    }

    public static function isDisabled(int $scope): bool
    {
        return $scope == self::DISABLED;
    }

    /**
     * Public scopes are PUBLIC & COMMON!
     */
    public static function isPublic(int $scope): bool
    {
        return in_array($scope, self::publicScopes());
    }

    /**
     * @return array<integer, string>
     */
    public static function all(): array
    {
        return [
            self::DISABLED => 'disabled',
            self::INACTIVE => 'inactive',
            self::PRIVATE => 'private',
            self::PUBLIC => 'public',
            self::COMMON => 'common',
        ];
    }

    public static function getName(int $scope): string
    {
        $name = self::all()[$scope] ?? null;

        Assert::notNull($name);

        return $name;
    }
}
