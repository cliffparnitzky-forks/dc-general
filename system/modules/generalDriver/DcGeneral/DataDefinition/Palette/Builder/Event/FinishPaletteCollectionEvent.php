<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\Palette\Builder\Event;

use DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;
use DcGeneral\EnvironmentInterface;

class FinishPaletteCollectionEvent extends BuilderEvent
{
	const NAME = 'DcGeneral\DataDefinition\Palette\Builder\Event\FinishPaletteCollection';

	/**
	 * @var PaletteCollectionInterface
	 */
	protected $paletteCollection;

	/**
	 * @param PaletteCollectionInterface $paletteCollection
	 * @param PaletteBuilder $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct(PaletteCollectionInterface $paletteCollection, PaletteBuilder $paletteBuilder)
	{
		$this->setPaletteCollection($paletteCollection);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param PaletteCollectionInterface $paletteCollection
	 */
	public function setPaletteCollection(PaletteCollectionInterface $paletteCollection)
	{
		$this->paletteCollection = $paletteCollection;
		return $this;
	}

	/**
	 * @return PaletteCollectionInterface
	 */
	public function getPaletteCollection()
	{
		return $this->paletteCollection;
	}

}
