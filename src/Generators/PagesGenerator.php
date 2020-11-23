<?php

namespace App\Generators;

use App\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator;

class PagesGenerator extends TaggableEntityGenerator
{
    use Publishable
    {
        beforeSave as protected publishableBeforeSave;
    }

    private PageRepositoryInterface $pageRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->pageRepository = $container->pageRepository;
    }

    public function getRules(array $data, $id = null) : array
    {
        return array_merge(
            parent::getRules($data, $id),
            [
                'title' => $this->rule('text'),
                'parent_id' => Validator::nonRecursiveParent($this->pageRepository, $id),
                'slug' => $this
                    ->rule('extendedAlias')
                    ->pageSlugAvailable(
                        $this->pageRepository,
                        $id
                    ),
            ]
        );
    }

    public function getOptions() : array
    {
        $options = parent::getOptions();

        $options['exclude'] = ['text'];
        $options['admin_template'] = 'pages';

        return $options;
    }

    public function beforeSave(array $data, $id = null) : array
    {
        $data = $this->publishableBeforeSave($data, $id);

        return $data;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField];

        $page = $this->pageRepository->get($id);

        $parts = [];

        $parent = $page->parent();

        if ($parent && !$parent->isSkippedInBreadcrumbs()) {
            $parentParent = $parent->parent();

            if ($parentParent && !$parentParent->isSkippedInBreadcrumbs()) {
                $parts[] = '...';
            }

            $parts[] = $parent->title;
        }

        $parts[] = $page->title;
        $partsStr = implode(' » ', $parts);

        $item['select_title'] = '[' . $page->getId() . '] ' . $partsStr;
        $item['tokens'] = $page->title;
        $item['url'] = $page->url();

        return $item;
    }
}
