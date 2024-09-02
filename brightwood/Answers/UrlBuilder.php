<?php

namespace Brightwood\Answers;

use App\Core\Interfaces\LinkerInterface;
use Brightwood\Models\Stories\Core\Story;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

class UrlBuilder
{
    private SettingsProviderInterface $settingsProvider;
    private LinkerInterface $linker;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        LinkerInterface $linker
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->linker = $linker;
    }

    public function buildStoryEditUrl(Story $story, string $langCode): string
    {
        return sprintf(
            '%s?edit=%s&lng=%s',
            $this->getBuilderUrl(),
            $this->linker->abs(
                $this->linker->story($story)
            ),
            $langCode
        );
    }

    public function buildStoryCreationUrl(string $langCode): string
    {
        return sprintf(
            '%s?new&lng=%s',
            $this->getBuilderUrl(),
            $langCode
        );
    }

    private function getBuilderUrl(): string
    {
        return $this->settingsProvider->get(
            'brightwood.builder_url',
            'https://brightwood-builder.onrender.com'
        );
    }
}
