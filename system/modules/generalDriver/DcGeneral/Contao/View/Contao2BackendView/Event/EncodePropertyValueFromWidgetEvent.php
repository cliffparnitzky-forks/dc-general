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

namespace DcGeneral\Contao\View\Contao2BackendView\Event;

use DcGeneral\Event\AbstractEnvironmentAwareEvent;

class EncodePropertyValueFromWidgetEvent
	extends AbstractEnvironmentAwareEvent
{
	const NAME = 'dc-general.view.contao2backend.encode-property-value-from-widget';

	/**
	 * @var string
	 */
	protected $property;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @param string $property
	 *
	 * @return DecodePropertyValueForWidgetEvent
	 */
	public function setProperty($property)
	{
		$this->property = $property;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * @param mixed $value
	 *
	 * @return DecodePropertyValueForWidgetEvent
	 */
	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}
}