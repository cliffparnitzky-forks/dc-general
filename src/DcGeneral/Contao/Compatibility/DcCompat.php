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

namespace DcGeneral\Contao\Compatibility;

use DcGeneral\Data\ModelInterface;
use DcGeneral\DC_General;
use DcGeneral\Exception\DcGeneralException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * Class DcCompat
 *
 * Small compatibility layer for callbacks, that expect a "full featured" DC instance.
 */
class DcCompat extends DC_General
{
	/**
	 * The underlying DC_General instance.
	 *
	 * @var DC_General
	 */
	protected $dcGeneral;

	/**
	 * The current model.
	 *
	 * @var ModelInterface
	 */
	protected $model;

	/**
	 * Name of the property currently working on.
	 *
	 * @var string
	 */
	protected $propertyName;

	/**
	 * Create a new instance.
	 *
	 * @param DC_General     $dcGeneral    The Dc instance to use for delegating.
	 *
	 * @param ModelInterface $model        The model within scope.
	 *
	 * @param null           $propertyName The name of the property within scope.
	 */
	public function __construct(DC_General $dcGeneral, ModelInterface $model, $propertyName = null)
	{
		$this->dcGeneral    = $dcGeneral;
		$this->model        = $model;
		$this->propertyName = $propertyName;
	}

	/**
	 * Retrieve the DC_General instance.
	 *
	 * @return DC_General
	 */
	public function getDcGeneral()
	{
		return $this->dcGeneral;
	}

	/**
	 * Retrieve the current model.
	 *
	 * @return ModelInterface
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Retrieve the current property.
	 *
	 * @return string
	 */
	public function getPropertyName()
	{
		return $this->propertyName;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralException This method is for internal use only.
	 */
	public function handlePopulateEnvironment(PopulateEnvironmentEvent $event)
	{
		throw new DcGeneralException(__CLASS__ . '::handlePopulateEnvironment() is internal use only and must not be called');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralException This method is for internal use only.
	 */
	protected function getTablenameCallback($strTable)
	{
		throw new DcGeneralException(__CLASS__ . '::getTablenameCallback() is internal use only and must not be called');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralRuntimeException The magic setter is unsupported and has been deactivated.
	 */
	public function __set($strKey, $varValue)
	{
		throw new DcGeneralRuntimeException('The magic setter is not supported anymore!');
	}

	/**
	 * {@inheritdoc}
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'id':
				return $this->model->getId();

			case 'parentTable':
				throw new DcGeneralRuntimeException('The magic property $dc->parentTable is not supported yet!');

			case 'childTable':
				throw new DcGeneralRuntimeException('The magic property $dc->childTable is not supported yet!');

			case 'rootIds':
				throw new DcGeneralRuntimeException('The magic property $dc->rootIds is not supported yet!');

			case 'createNewVersion':
				throw new DcGeneralRuntimeException('The magic property $dc->createNewVersion is not supported yet!');

			case 'table':
				throw new DcGeneralRuntimeException('The magic property $dc->table is not supported yet!');

			case 'value':
				if ($this->propertyName) {

				}
				return null;

			case 'field':
				return $this->propertyName;

			case 'inputName':
				throw new DcGeneralRuntimeException('The magic property $dc->inputName is not supported yet!');

			case 'palette':
				throw new DcGeneralRuntimeException('The magic property $dc->palette is not supported yet!');

			case 'activeRecord':
				return new ActiveRecord($this->model);

			default:
		}

		return $this->dcGeneral->__get($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDCA()
	{
		return $this->dcGeneral->getDCA();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->dcGeneral->getName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEnvironment()
	{
		return $this->dcGeneral->getEnvironment();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getViewHandler()
	{
		return $this->dcGeneral->getViewHandler();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getControllerHandler()
	{
		return $this->dcGeneral->getControllerHandler();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootSetter($strTable)
	{
		return $this->dcGeneral->getRootSetter($strTable);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isRootItem(ModelInterface $objParentModel, $strTable)
	{
		return $this->dcGeneral->isRootItem($objParentModel, $strTable);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSameParent(ModelInterface $objDestination, ModelInterface $objCopyFrom, $strParentTable)
	{
		return $this->dcGeneral->setSameParent($objDestination, $objCopyFrom, $strParentTable);
	}

	/**
	 * {@inheritdoc}
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->getViewHandler(), $name), $arguments);
	}

	/**
	 * {@inheritdoc}
	 */
	public function copy()
	{
		return $this->dcGeneral->copy();
	}

	/**
	 * {@inheritdoc}
	 */
	public function create()
	{
		return $this->dcGeneral->create();
	}

	/**
	 * {@inheritdoc}
	 */
	public function cut()
	{
		return $this->dcGeneral->cut();
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete()
	{
		return $this->dcGeneral->delete();
	}

	/**
	 * {@inheritdoc}
	 */
	public function edit()
	{
		return $this->dcGeneral->edit();
	}

	/**
	 * {@inheritdoc}
	 */
	public function move()
	{
		return $this->dcGeneral->move();
	}

	/**
	 * {@inheritdoc}
	 */
	public function show()
	{
		return $this->dcGeneral->show();
	}

	/**
	 * {@inheritdoc}
	 */
	public function showAll()
	{
		return $this->dcGeneral->showAll();
	}

	/**
	 * {@inheritdoc}
	 */
	public function undo()
	{
		return $this->dcGeneral->undo();
	}
}
