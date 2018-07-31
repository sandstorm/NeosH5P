<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Service\H5PIntegrationService;

class LibraryController extends AbstractModuleController {

    /**
     * @Flow\Inject
     * @var H5PIntegrationService
     */
    protected $h5pIntegrationService;

    /**
     * @Flow\Inject
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * @Flow\Inject
     * @var ContentRepository
     */
    protected $contentRepository;

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
        $view->getTemplatePaths()->setPartialRootPaths(array_merge(
            ['resource://Neos.Neos/Private/Partials', 'resource://Neos.Neos/Private/Partials'],
            $view->getTemplatePaths()->getPartialRootPaths()
        ));
    }

    public function indexAction()
    {
        $libraries = $this->libraryRepository->findAll();
        $this->view->assign('libraries', $libraries);
    }

    /**
     * @param Library $library
     */
    public function displayAction(Library $library)
    {
        $h5pIntegrationSettings = $this->h5pIntegrationService->getSettings($this->controllerContext, [$library->getLibraryId()]);
        $contentsUsingThisLibrary = $this->contentRepository->findByLibrary($library);

        $this->view->assign('library', $library);
        $this->view->assign('contentsUsingThisLibrary', $contentsUsingThisLibrary);
        $this->view->assign('settings', json_encode($h5pIntegrationSettings));
        $this->view->assign('scripts', $this->h5pIntegrationService->getMergedScripts($h5pIntegrationSettings));
        $this->view->assign('styles', $this->h5pIntegrationService->getMergedStyles($h5pIntegrationSettings));
    }

}
