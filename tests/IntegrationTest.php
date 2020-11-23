<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Core\Env;
use Plasticode\Core\Settings;
use Respect\Validation\Validator;
use Webmozart\Assert\Assert;

abstract class IntegrationTest extends TestCase
{
    protected array $settings;

    public function setUp() : void
    {
        parent::setUp();

        $root = __DIR__ . '/..';

        Env::load($root);

        $this->settings = $settings = Settings::load($root . '/settings');

        Assert::notEmpty($settings);

        foreach ($settings['validation_namespaces'] as $namespace) {
            Validator::with($namespace);
        }
    }

    public function tearDown() : void
    {
        unset($this->settings);

        parent::tearDown();
    }
}
