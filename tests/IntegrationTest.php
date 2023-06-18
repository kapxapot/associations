<?php

namespace App\Tests;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Testing\Factories\UserRepositoryFactory;
use App\Testing\Seeders\UserSeeder;
use PHPUnit\Framework\TestCase;
use Plasticode\Core\Env;
use Plasticode\Settings\SettingsFactory;
use Respect\Validation\Validator;
use Webmozart\Assert\Assert;

abstract class IntegrationTest extends TestCase
{
    protected array $settings;
    protected UserRepositoryInterface $userRepository;

    public function setUp(): void
    {
        parent::setUp();

        $root = __DIR__ . '/..';

        Env::load($root);

        $this->settings = $settings = SettingsFactory::make($root);

        Assert::notEmpty($settings);

        foreach ($settings['validation_namespaces'] as $namespace) {
            Validator::with($namespace);
        }

        $this->userRepository = UserRepositoryFactory::make();
    }

    public function tearDown(): void
    {
        unset($this->userRepository);
        unset($this->settings);

        parent::tearDown();
    }

    protected function getDefaultUser(): User
    {
        return $this->userRepository->get(UserSeeder::DEFAULT_USER_ID);
    }
}
