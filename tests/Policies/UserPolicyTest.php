<?php

namespace App\Tests\Policies;

use App\Policies\UserPolicy;
use PHPUnit\Framework\TestCase;

final class UserPolicyTest extends TestCase
{
    public function testDefaultPolicy(): void
    {
        $policy = new UserPolicy();

        $this->assertEquals(false, $policy->canSeeAllGames());
        $this->assertEquals(false, $policy->canSeeAllWords());
        $this->assertEquals(false, $policy->canSeeAllAssociations());
    }
}
