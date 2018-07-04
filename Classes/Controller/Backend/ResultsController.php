<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Neos\Controller\Module\AbstractModuleController;

class ResultsController extends AbstractModuleController {
    /**
     * We add the Neos default partials and layouts here, so we can use them
     * in our backend modules
     *
     * @param ViewInterface $view
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
        $view->getTemplatePaths()->setLayoutRootPath('resource://Neos.Neos/Private/Layouts');
        $view->getTemplatePaths()->setPartialRootPath('resource://Neos.Neos/Private/Partials');
    }

}
