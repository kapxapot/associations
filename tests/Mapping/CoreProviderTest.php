<?php

namespace App\Tests\Mapping;

use App\Auth\Auth;
use App\Auth\Interfaces\AuthInterface;
use App\Config\CaptchaConfig;
use App\Config\Config;
use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\NewsConfigInterface;
use App\Config\Interfaces\UserConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use App\Config\LocalizationConfig;
use App\Core\Interfaces\LinkerInterface;
use App\Core\Linker;
use App\Core\Serializer;
use App\External\YandexDict;
use App\Mapping\Providers\CoreProvider;
use App\Models\Validation\AgeValidation;
use Plasticode\Auth\Interfaces as AuthCore;
use Plasticode\Config\Interfaces as ConfigCore;
use Plasticode\Core\Interfaces as Core;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Repositories\Interfaces as CoreRepositories;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Testing\AbstractProviderTest;
use Slim\Interfaces\RouterInterface;

final class CoreProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            RouterInterface::class,
            Core\SessionInterface::class,
            SettingsProviderInterface::class,

            CoreRepositories\UserRepositoryInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new CoreProvider();
    }

    public function testWiring(): void
    {
        $this->check(
            ConfigCore\CaptchaConfigInterface::class,
            CaptchaConfig::class
        );

        $this->check(
            ConfigCore\LocalizationConfigInterface::class,
            LocalizationConfig::class
        );

        $this->check(
            ConfigCore\TagsConfigInterface::class,
            \Plasticode\Config\TagsConfig::class
        );

        $this->check(AuthInterface::class, Auth::class);
        $this->check(Config::class);
        $this->check(LinkerInterface::class, Linker::class);
        $this->check(Serializer::class);

        // aliases

        $this->check(AuthCore\AuthInterface::class, Auth::class);
        $this->check(Core\LinkerInterface::class, Linker::class);

        $this->check(\Plasticode\Config\Config::class, Config::class);
        $this->check(AssociationConfigInterface::class, Config::class);
        $this->check(NewsConfigInterface::class, Config::class);
        $this->check(UserConfigInterface::class, Config::class);
        $this->check(WordConfigInterface::class, Config::class);

        // external

        $this->check(YandexDict::class);

        // validation

        $this->check(AgeValidation::class);
        $this->check(UserValidation::class);
    }
}
