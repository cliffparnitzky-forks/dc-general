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

namespace DcGeneral\DataDefinition\Palette\Condition\Property;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;

/**
 * Condition checking that the value of a property is false.
 */
class PropertyFalseCondition implements PropertyConditionInterface
{
	/**
	 * The property name.
	 *
	 * @var string
	 */
	protected $propertyName;

	/**
	 * Use strict compare mode.
	 *
	 * @var bool
	 */
	protected $strict;

	/**
	 * Create a new instance.
	 *
	 * @param string $propertyName The name of the property.
	 *
	 * @param bool   $strict       Flag if the comparison shall be strict (type safe).
	 */
	public function __construct($propertyName, $strict = false)
	{
		$this->propertyName = (string)$propertyName;
		$this->strict       = (bool)$strict;
	}

	/**
	 * Set the property name.
	 *
	 * @param string $propertyName The property name.
	 *
	 * @return PropertyTrueCondition
	 */
	public function setPropertyName($propertyName)
	{
		$this->propertyName = (string)$propertyName;

		return $this;
	}

	/**
	 * Retrieve the property name.
	 *
	 * @return string
	 */
	public function getPropertyName()
	{
		return $this->propertyName;
	}

	/**
	 * Set the flag if the comparison shall be strict (type safe).
	 *
	 * @param boolean $strict The flag.
	 *
	 * @return PropertyTrueCondition
	 */
	public function setStrict($strict)
	{
		$this->strict = (bool)$strict;

		return $this;
	}

	/**
	 * Retrieve the flag if the comparison shall be strict (type safe).
	 *
	 * @return boolean
	 */
	public function getStrict()
	{
		return $this->strict;
	}

	/**
	 * {@inheritdoc}
	 */
	public function match(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if ($input && $input->hasPropertyValue($this->propertyName))
		{
			$value = $input->getPropertyValue($this->propertyName);
		}
		elseif ($model)
		{
			$value = $model->getProperty($this->propertyName);
		}
		elseif ($this->strict)
		{
			return false;
		}
		else
		{
			return true;
		}

		return $this->strict ? ($value === false) : !$value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
	}
}
