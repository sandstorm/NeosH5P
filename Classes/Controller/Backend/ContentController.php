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
        \Neos\Flow\var_dump($coreSettings['core']);
    }
}
