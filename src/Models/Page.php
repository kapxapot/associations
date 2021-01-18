<?php

namespace App\Models;

use App\Collections\PageCollection;
use Plasticode\Collections\Generic\ArrayCollection;
use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Models\Interfaces\ParentedInterface;
use Plasticode\Models\Traits\Parented;
use Plasticode\Util\Strings;

/**
 * @property integer|null $parentId
 * @property string $slug
 * @property integer $showInFeed
 * @property integer $skipInBreadcrumbs
 * @method PageCollection children()
 * @method static withChildren(PageCollection|callable $children)
 */
class Page extends NewsSource implements PageInterface, ParentedInterface
{
    use Parented;

    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            $this->childrenPropertyName,
            $this->parentPropertyName,
        ];
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Returns published sub-pages.
     */
    public function subPages(): PageCollection
    {
        return $this
            ->children()
            ->where(
                fn (self $p) => $p->isPublished()
            )
            ->ascStr('title');
    }

    public function breadcrumbs(): ArrayCollection
    {
        $breadcrumbs = [];

        $page = $this->parent();

        while ($page) {
            if (!$page->isSkippedInBreadcrumbs()) {
                $breadcrumbs[] = $page;
            }

            $page = $page->parent();
        }

        $bcArrays = PageCollection::make($breadcrumbs)
            ->reverse()
            ->map(
                fn (Page $p) =>
                [
                    'url' => $p->url(),
                    'text' => $p->title,
                    'title' => null,
                ]
            );

        return ArrayCollection::from($bcArrays);
    }

    public function isShownInFeed(): bool
    {
        return self::toBool($this->showInFeed);
    }

    public function isSkippedInBreadcrumbs(): bool
    {
        return self::toBool($this->skipInBreadcrumbs);
    }

    // SearchableInterface

    public function code(): string
    {
        $parts[] = $this->slug;

        if ($this->title !== $this->slug) {
            $parts[] = $this->title;
        }

        return Strings::doubleBracketsTag(null, ...$parts);
    }

    // SerializableInterface

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->title,
            'slug' => $this->slug,
            'tags' => Strings::toTags($this->tags),
        ];
    }

    // NewsSourceInterface

    public function displayTitle(): string
    {
        return $this->title;
    }

    public function rawText(): ?string
    {
        return $this->text;
    }
}
