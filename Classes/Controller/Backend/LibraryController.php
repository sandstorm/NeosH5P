<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Error\Messages\Message;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Service\CRUD\LibraryCRUDService;
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
     * @Flow\Inject
     * @var LibraryCRUDService
     */
    protected $libraryCRUDService;

    /**
     * @Flow\Inject
     * @var \H5PCore
     */
    protected $h5pCore;

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

    /**
     * @param Library $library
     * @throws StopActionException
     * @return bool
     */
    public function deleteAction(Library $library)
    {
        $this->libraryCRUDService->handleDelete($library);

        $this->addFlashMessage('The library "%s" has been deleted.', 'Library deleted', Message::SEVERITY_OK, [$library->getTitle()]);
        $this->redirect('index', null, null);
        return false;
    }

    public function refreshContentTypeCacheAction()
    {
        if ($this->h5pCore->updateContentTypeCache() === false) {
            $this->addFlashMessage(
                'The cache could not be refreshed because the H5P Hub did not respond.',
                '',
                Message::SEVERITY_ERROR
            );
        } else {
            $this->addFlashMessage('The content type cache was refreshed successfully.');
        }
        $this->redirect('index');
    }

    public function upgradeAction() {
        // (render progress bar)

        // Hat eigenes template -> in dem template lade ich javascript, das (bei workpress abgucken):
        // 1. alle zu upgradenden contenttypes runterlädt
        // 2. upgraded
        // 3. wieder hochlädt

        // in wordpress sind die ajax endpoints: ajax_upgrade_progress (übernimmt das Umschreiben es Contents), ajax_upgrade_library
        // display_content_upgrades -> hier wird das settings-array gebaut für den client und die JS-Files geladen
    }

}
