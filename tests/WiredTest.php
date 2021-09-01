<?php

namespace App\Tests;

use App\Config\Interfaces\WordConfigInterface;
use App\Core\Interfaces\LinkerInterface;
use App\Hydrators\GameHydrator;
use App\Hydrators\TurnHydrator;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Testing\Factories\LanguageRepositoryFactory;
use App\Testing\Factories\UserRepositoryFactory;
use App\Testing\Factories\WordRepositoryFactory;
use App\Testing\Mocks\Config\WordConfigMock;
use App\Testing\Mocks\LinkerMock;
use App\Testing\Mocks\Repositories\AssociationRepositoryMock;
use App\Testing\Mocks\Repositories\GameRepositoryMock;
use App\Testing\Mocks\Repositories\TurnRepositoryMock;
use PHPUnit\Framework\TestCase;
use Plasticode\DI\Autowirer;
use Plasticode\DI\Containers\AutowiringContainer;
use Plasticode\ObjectProxy;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\Validator;
use Psr\Container\ContainerInterface;

abstract class WiredTest extends TestCase
{
    private AutowiringContainer $container;

    public function setUp(): void
    {
        parent::setUp();

        $map = [
            // core

            LinkerInterface::class => LinkerMock::class,
            SettingsProviderInterface::class => SettingsProvider::class,
            ValidatorInterface::class => Validator::class,

            // config

            WordConfigInterface::class => WordConfigMock::class,

            // repositories

            AssociationRepositoryInterface::class => AssociationRepositoryMock::class,

            GameRepositoryInterface::class =>
                fn (ContainerInterface $c) => new GameRepositoryMock(
                    new ObjectProxy(
                        fn () => $c->get(GameHydrator::class)
                    )
                ),

            LanguageRepositoryInterface::class => LanguageRepositoryFactory::class,

            TurnRepositoryInterface::class =>
                fn (ContainerInterface $c) => new TurnRepositoryMock(
                    new ObjectProxy(
                        fn () => $c->get(TurnHydrator::class)
                    )
                ),

            UserRepositoryInterface::class => UserRepositoryFactory::class,

            WordRepositoryInterface::class => WordRepositoryFactory::class,
        ];

        $this->container = new AutowiringContainer(new Autowirer(), $map);
    }

    public function tearDown(): void
    {
        unset($this->container);

        parent::tearDown();
    }

    /**
     * @return mixed
     */
    protected function get(string $key)
    {
        return $this->container->get($key);
    }
}
