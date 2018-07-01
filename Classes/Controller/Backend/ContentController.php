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

        $this->view->assign('settings' , json_encode($coreSettings));
        $this->view->assign('scripts' , $coreSettings['core']['scripts']);
        $this->view->assign('styles' , $coreSettings['core']['styles']);
    }
}
