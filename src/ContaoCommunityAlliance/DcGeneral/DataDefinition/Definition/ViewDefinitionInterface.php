<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\PanelLayoutInterface;

/**
 * This interface describes the base information about views.
 */
interface ViewDefinitionInterface extends DefinitionInterface
{
    /**
     * Retrieve the listing configuration.
     *
     * @return ListingConfigInterface
     */
    public function getListingConfig();

    /**
     * Retrieve the global commands.
     *
     * @return CommandCollectionInterface
     */
    public function getGlobalCommands();

    /**
     * Retrieve the model command colletion.
     *
     * @return CommandCollectionInterface
     */
    public function getModelCommands();

    /**
     * Retrieve the panel layout.
     *
     * @return PanelLayoutInterface
     */
    public function getPanelLayout();
}
