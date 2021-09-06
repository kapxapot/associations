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
     * Is the scope one of the fuzzy private ones.
     */
    public static function isFuzzyPrivate(int $scope): bool
    {
        return in_array($scope, self::allFuzzyPrivate());
    }

    /**
     * Is the scope one of the fuzzy public ones.
     */
    public static function isFuzzyPublic(int $scope): bool
    {
        return in_array($scope, self::allFuzzyPublic());
    }

    /**
     * Returns scopes that are Scope::INACTIVE or Scope::PRIVATE.
     *
     * @return integer[]
     */
    public static function allFuzzyPrivate(): array
    {
        return [self::INACTIVE, self::PRIVATE];
    }

    /**
     * Returns scopes that are Scope::PUBLIC or Scope::COMMON.
     *
     * @return integer[]
     */
    public static function allFuzzyPublic(): array
    {
        return [self::PUBLIC, self::COMMON];
    }

    /**
     * @return array<integer, string>
     */
    public static function allNames(): array
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
        $names = self::allNames();
        $name = $names[$scope] ?? null;

        Assert::notNull($name);

        return $name;
    }
}
