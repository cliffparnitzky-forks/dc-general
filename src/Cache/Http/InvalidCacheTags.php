<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Cache\Http;

use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\InvalidHttpCacheTagsEvent;
use FOS\HttpCache\CacheInvalidator;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * This is for purge the invalid http cache tags.
 */
class InvalidCacheTags implements InvalidCacheTagsInterface
{
    /**
     * The http cache namespace.
     *
     * @var string
     */
    private $namespace;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The http cache manager.
     *
     * @var CacheInvalidator|null
     */
    private $cacheManager;

    /**
     * The dc general environment.
     *
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * The cache tags.
     *
     * @var string[]
     */
    private $tags;

    /**
     * The constructor.
     *
     * @param string                   $namespace    The http cache namespace.
     * @param EventDispatcherInterface $dispatcher   The event dispatcher.
     * @param CacheInvalidator|null    $cacheManager The http cache manager.
     */
    public function __construct(
        string $namespace,
        EventDispatcherInterface $dispatcher,
        CacheInvalidator $cacheManager = null
    ) {
        $this->namespace    = $namespace;
        $this->dispatcher   = $dispatcher;
        $this->cacheManager = $cacheManager;
    }

    /**
     * {@inheritDoc}
     */
    public function setEnvironment(EnvironmentInterface $environment): InvalidCacheTags
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function purgeCacheTags(ModelInterface $model): void
    {
        if (null === $this->cacheManager) {
            return;
        }

        $this->clearCacheTags();
        $this->addCurrentModelTag($model);
        $this->addRelatedModelsTag($model);

        $event = new InvalidHttpCacheTagsEvent($this->environment);
        $event->setNamespace($this->namespace)->setTags($this->tags);
        $this->dispatcher->dispatch($event);

        $this->cacheManager->invalidateTags($this->cleanUpTags($event->getTags()));
    }

    /**
     * Add the cache tag for the current model.
     *
     * @param ModelInterface $model The current model.
     *
     * @return void
     */
    private function addCurrentModelTag(ModelInterface $model): void
    {
        $this->addModelTag($model);
    }

    /**
     * Add the cache tag for the related models, if current model has relations.
     *
     * @param ModelInterface $model The current model.
     *
     * @return void
     */
    private function addRelatedModelsTag(ModelInterface $model): void
    {
        if (null === ($parentDefinition = $this->environment->getParentDataDefinition())) {
            return;
        }
        /** @var ModelRelationshipDefinitionInterface $relationships */
        $relationships   = $this->environment->getDataDefinition()->getDefinition('model-relationships');
        $parentCondition = $relationships->getChildCondition($parentDefinition->getName(), $model->getProviderName());
        $dataProvider    = $this->environment->getDataProvider($parentDefinition->getName());

        /** @var DefaultCollection $parentModels */
        $parentModels = $dataProvider
            ->fetchAll($dataProvider->getEmptyConfig()->setFilter($parentCondition->getInverseFilterFor($model)));
        foreach ($parentModels as $parentModel) {
            $this->addModelTag($parentModel);
        }
    }

    /**
     * Add the model tag.
     *
     * @param ModelInterface $model The model.
     *
     * @return void
     */
    private function addModelTag(ModelInterface$model): void
    {
        $modelNamespace = $this->namespace . $model->getProviderName();
        $this->tags[]   = $modelNamespace;
        $this->tags[]   = $modelNamespace . '.' . $model->getId();
    }

    /**
     * Clean up the tags. To be sure that there are no empty and double entries.
     *
     * @param array $tags The tags the should be cleaned up.
     *
     * @return string[]
     */
    private function cleanUpTags(array $tags): array
    {
        return \array_values(\array_filter(\array_unique($tags)));
    }

    /**
     * The cache tags are initially cleared. To avoid that already used tags are used again.
     *
     * @return void
     */
    private function clearCacheTags(): void
    {
        $this->tags = [];
    }
}
