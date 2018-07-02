<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Neos\Controller\Module\AbstractModuleController;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Service\H5PIntegrationService;

class ContentController extends AbstractModuleController
{
    /**
     * @Flow\Inject
     * @var H5PIntegrationService
     */
    protected $h5pIntegrationService;

    public function newAction()
    {
        $coreSettings = $this->h5pIntegrationService->getCoreSettings();
        $coreSettings['editor'] = $this->h5pIntegrationService->getEditorSettings();

        $this->view->assign('settings', json_encode($coreSettings));
        $this->view->assign('scripts', $coreSettings['core']['scripts']);
        $this->view->assign('styles', $coreSettings['core']['styles']);
    }

    /**
     * @param string $title
     * @param string $action
     * @param string $library
     */
    public function createAction(string $title, string $action, string $library, string $parameters)
    {
        \Neos\Flow\var_dump($title);
        \Neos\Flow\var_dump($action);
        \Neos\Flow\var_dump($library);
        \Neos\Flow\var_dump($parameters);
    }
}
