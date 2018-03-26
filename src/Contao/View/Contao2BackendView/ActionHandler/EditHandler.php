<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EditMask;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\BackCommand;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class CreateHandler
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler
 */
class EditHandler
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * EditHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->setScopeDeterminator($scopeDeterminator);
    }

    /**
     * Handle the event to process the action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    public function handleEvent(ActionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $action = $event->getAction();
        // Only handle if we do not have a manual sorting or we know where to insert.
        // Manual sorting is handled by clipboard.
        if ($action->getName() !== 'edit') {
            return;
        }

        if (false === $this->checkPermission($event)) {
            $event->stopPropagation();

            return;
        }

        $response = $this->process($event->getEnvironment());
        if (false !== $response) {
            $event->setResponse($response);
        }
    }

    /**
     * Handle the action.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string|bool
     *
     * @throws DcGeneralRuntimeException When the requested model could not be located in the database.
     */
    protected function process(EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        $modelId       = ModelId::fromSerialized($inputProvider->getParameter('id'));
        $dataProvider  = $environment->getDataProvider($modelId->getDataProviderName());

        $view = $environment->getView();
        if (!$view instanceof BaseView) {
            return false;
        }

        $this->checkRestoreVersion($environment, $modelId);

        $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
        if (!$model) {
            throw new DcGeneralRuntimeException('Could not retrieve model with id ' . $modelId->getSerialized());
        }

        $clone = clone $model;
        $clone->setId($model->getId());

        if ('select' !== $inputProvider->getParameter('act')) {
            $this->handleGlobalCommands($environment);
        }

        $editMask = new EditMask($view, $model, $clone, null, null, $view->breadcrumb());

        return $editMask->execute();
    }

    /**
     * Check permission for edit a model.
     *
     * @param ActionEvent $event The action event.
     *
     * @return bool
     */
    private function checkPermission(ActionEvent $event)
    {
        $environment     = $event->getEnvironment();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        if (true === $basicDefinition->isEditable()) {
            return true;
        }

        $inputProvider = $environment->getInputProvider();

        $modelId = ModelId::fromSerialized($inputProvider->getParameter('id'));

        $event->setResponse(
            \sprintf(
                '<div style="text-align:center; font-weight:bold; padding:40px;">
                    You have no permission for edit model %s.
                </div>',
                $modelId->getSerialized()
            )
        );

        return false;
    }

    /**
     * Check the submitted data if we want to restore a previous version of a model.
     *
     * If so, the model will get loaded and marked as active version in the data provider and the client will perform a
     * reload of the page.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelId              $modelId     The model id.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When the requested version could not be located in the database.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function checkRestoreVersion(EnvironmentInterface $environment, ModelId $modelId)
    {
        $definition    = $environment->getDataDefinition();
        $inputProvider = $environment->getInputProvider();

        $dataProviderDefinition  = $definition->getDataProviderDefinition();
        $dataProvider            = $environment->getDataProvider($modelId->getDataProviderName());
        $dataProviderInformation = $dataProviderDefinition->getInformation($modelId->getDataProviderName());

        if ($dataProviderInformation->isVersioningEnabled()
            && ($inputProvider->getValue('FORM_SUBMIT') === 'tl_version')
            && ($modelVersion = $inputProvider->getValue('version')) !== null
        ) {
            $model = $dataProvider->getVersion($modelId->getId(), $modelVersion);

            if ($model === null) {
                $message = \sprintf(
                    'Could not load version %s of record ID %s from %s',
                    $modelVersion,
                    $modelId->getId(),
                    $modelId->getDataProviderName()
                );

                $environment->getEventDispatcher()->dispatch(
                    ContaoEvents::SYSTEM_LOG,
                    new LogEvent($message, TL_ERROR, 'DC_General - checkRestoreVersion()')
                );

                throw new DcGeneralRuntimeException($message);
            }

            $dataProvider->save($model);
            $dataProvider->setVersionActive($modelId->getId(), $modelVersion);
            $environment->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_RELOAD, new ReloadEvent());
        }
    }

    /**
     * Handle the globals commands
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    protected function handleGlobalCommands(EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        $backendView    = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $globalCommands = $backendView->getGlobalCommands();

        $globalCommands->clearCommands();

        $backCommand = new BackCommand();
        $backCommand->setDisabled(false);
        $globalCommands->addCommand($backCommand);
    }
}