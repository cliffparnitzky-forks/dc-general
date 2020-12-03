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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;

/**
 * Default implementation of a sort element.
 */
class DefaultSortElement extends AbstractElement implements SortElementInterface
{
    /**
     * The selected definition.
     *
     * @var GroupAndSortingDefinitionInterface
     */
    private $selected;

    /**
     * Retrieve the group and sorting definition.
     *
     * @return GroupAndSortingDefinitionCollectionInterface|GroupAndSortingDefinitionInterface[]
     */
    protected function getGroupAndSortingDefinition()
    {
        /** @var Contao2BackendViewDefinitionInterface $view */
        $view = $this->getEnvironment()
            ->getDataDefinition()
            ->getDefinition(Contao2BackendViewDefinitionInterface::NAME);

        return $view
            ->getListingConfig()
            ->getGroupAndSortingDefinition();
    }

    /**
     * Search a definition by it's name.
     *
     * @param string $name The name.
     *
     * @return GroupAndSortingDefinitionInterface|null
     */
    protected function searchDefinitionByName($name)
    {
        foreach ($this->getGroupAndSortingDefinition() as $definition) {
            if ($name === $definition->getName()) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * Retrieve the persistent value from the input provider.
     *
     * @return array
     */
    protected function getPersistent()
    {
        $values = [];
        if ($this->getSessionStorage()->has('sorting')) {
            $values = $this->getSessionStorage()->get('sorting');
        }

        if (\array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $values)) {
            return $values[$this->getEnvironment()->getDataDefinition()->getName()];
        }

        return [];
    }

    /**
     * Store the persistent value in the input provider.
     *
     * @param string $propertyName The name of the property to sort by.
     *
     * @return void
     */
    protected function setPersistent($propertyName)
    {
        $values         = [];
        $definitionName = $this->getEnvironment()->getDataDefinition()->getName();

        if ($this->getSessionStorage()->has('sorting')) {
            $values = $this->getSessionStorage()->get('sorting');
        }

        if ($propertyName) {
            if (!\is_array($values[$definitionName])) {
                $values[$definitionName] = [];
            }

            $values[$definitionName] = $propertyName;
        } else {
            unset($values[$definitionName]);
        }

        $this->getSessionStorage()->set('sorting', $values);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(ConfigInterface $config, PanelElementInterface $panelElement = null)
    {
        if (null === $panelElement) {
            $input = $this->getInputProvider();
            $value = null;

            if ('1' !== $this->getEnvironment()->getInputProvider()->getValue('filter_reset')) {

                if ($input->hasValue('tl_sort') && $this->getPanel()->getContainer()->updateValues()) {
                    $value = $input->getValue('tl_sort');

                    $this->setPersistent($value);
                }

                $persistent = $this->getPersistent();
                if (!$persistent) {
                    if ($this->getGroupAndSortingDefinition()->hasDefault()) {
                        $persistent = $this->getGroupAndSortingDefinition()->getDefault()->getName();
                    }
                    $this->setPersistent($value);
                }

                $this->setSelected($persistent);
            }
        } else {
          $this->setPersistent(null);
        }

        $current = $config->getSorting();

        if (!\is_array($current)) {
            $current = [];
        }

        if ($this->getSelectedDefinition()) {
            foreach ($this->getSelectedDefinition() as $information) {
                $current[$information->getProperty()] = $information->getSortingMode();
            }
        }
        $config->setSorting($current);
    }

    /**
     * {@inheritDoc}
     */
    public function render(ViewTemplateInterface $viewTemplate)
    {
        $options = [];
        foreach ($this->getGroupAndSortingDefinition() as $information) {
            /** @var GroupAndSortingDefinitionInterface $information */
            $name       = $information->getName();
            $properties = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();
            if ($properties->hasProperty($name)) {
                $name = $properties->getProperty($name)->getLabel();
            }

            if (empty($name)) {
                $name = $information->getName();
            }

            $options[] = [
                'value'      => StringUtil::specialchars($information->getName()),
                'attributes' => ($this->getSelected() === $information->getName()) ? ' selected' : '',
                'content'    => $name
            ];
        }

        // Sort by option values.
        \uksort($options, '\strcasecmp');

        $viewTemplate->set('options', $options);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSelected($name)
    {
        $this->selected = $this->searchDefinitionByName($name);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSelected()
    {
        return $this->selected ? $this->selected->getName() : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSelectedDefinition()
    {
        return $this->selected;
    }
}
