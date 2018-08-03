<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Error\Messages\Message;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Service\CRUD\LibraryCRUDService;
use Sandstorm\NeosH5P\Domain\Service\H5PIntegrationService;
use Sandstorm\NeosH5P\Domain\Service\UriGenerationService;

class LibraryController extends AbstractModuleController {

    /**
     * @Flow\InjectConfiguration(path="h5pPublicFolder.url")
     * @var string
     */
    protected $h5pPublicFolderUrl;

    /**
     * @Flow\InjectConfiguration(path="h5pPublicFolder.subfolders.core")
     * @var string
     */
    protected $h5pCorePublicFolderName;

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
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var UriGenerationService
     */
    protected $uriGenerationService;

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

    /**
     * @throws StopActionException
     * @return bool
     */
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
        return false;
    }

    /**
     * @param Library $library
     * @throws StopActionException
     * @return bool
     */
    public function upgradeAction(Library $library) {
        // (render progress bar)

        // Hat eigenes template -> in dem template lade ich javascript, das (bei workpress abgucken):
        // 1. alle zu upgradenden contenttypes runterlädt
        // 2. upgraded
        // 3. wieder hochlädt

        // in wordpress sind die ajax endpoints: ajax_upgrade_progress (übernimmt das Umschreiben es Contents), ajax_upgrade_library
        // display_content_upgrades -> hier wird das settings-array gebaut für den client und die JS-Files geladen

        $packageName = $this->packageManager->getPackageKeyFromComposerName('h5p/h5p-core');
        $installedH5pVersion = $this->packageManager->getPackage($packageName)->getInstalledVersion();

        $libsWithNewerVersion = $this->libraryRepository->findLibrariesWithNewerVersion($library)->toArray();

        if (empty($libsWithNewerVersion)) {
            $this->addFlashMessage(
                'There are no available upgrades for this library.',
                '',
                Message::SEVERITY_ERROR
            );
            $this->redirect('index');
        }

        $numberOfContentsUsingLibrary = $library->getContents()->count();
        if ($numberOfContentsUsingLibrary == 0) {
            $this->addFlashMessage(
                'There\'s no content instances to upgrade.',
                '',
                Message::SEVERITY_ERROR
            );
            $this->redirect('index');
        }

        $scriptBaseUrl = $this->h5pPublicFolderUrl . $this->h5pCorePublicFolderName . '/js';

        $migrateContentUri = $this->uriGenerationService->buildUriWithMainRequest(
            $this->controllerContext,
            'migrateContent',
            ['oldLibraryId' => $library->getLibraryId()],
            'Backend\ContentUpgradeAjax',
            'Sandstorm.NeosH5P'
        );

        $libraryInfoUri = $this->uriGenerationService->buildUriWithMainRequest(
            $this->controllerContext,
            'libraryInfo',
            null,
            'Backend\ContentUpgradeAjax',
            'Sandstorm.NeosH5P'
        );

        $settings = array(
            'containerSelector' => '#h5p-admin-container',
            'libraryInfo' => array(
                'message' => sprintf('You are about to upgrade %s. Please select upgrade version.', '[TODO: see wp plugin]'),
                'inProgress' => 'Upgrading to %ver...',
                'error' => 'An error occurred while processing parameters:',
                'errorData' => 'Could not load data for library %lib.',
                'errorContent' => 'Could not upgrade content %id:',
                'errorScript' => 'Could not load upgrades script for %lib.',
                'errorParamsBroken' => 'Parameters are broken.',
                'done' => vsprintf('You have successfully upgraded %s. <br/><a href=" %s"> Return </a>', ['[TODO: see wp plugin]', 'www.foo.de']),
                'library' => [
                    'name' => $library->getName(),
                    'version' => $library->getMajorVersion() . '.' . $library->getMinorVersion()
                ],
                'libraryBaseUrl' => $libraryInfoUri . '/',
                'scriptBaseUrl' => $scriptBaseUrl,
                'buster' => '?ver=' . $installedH5pVersion,
                'versions' => array_map(function ($libraryVersion) {return $libraryVersion->getVersionString();}, $libsWithNewerVersion),
                'contents' => $numberOfContentsUsingLibrary,
                'buttonLabel' => 'Upgrade',
                'infoUrl' => $migrateContentUri,
                'total' => $numberOfContentsUsingLibrary,
                'token' => 'dummy'
            )
        );

        $coreSettings = $this->h5pIntegrationService->getSettings($this->controllerContext);

        $this->view->assign('coreSettings', json_encode($coreSettings));
        $this->view->assign('coreScripts', $coreSettings['core']['scripts']);
        $this->view->assign('library', $library);
        $this->view->assign('adminSettings', json_encode($settings));
        $this->view->assign('h5pVersionScriptUrl', $scriptBaseUrl . '/h5p-version.js');
        $this->view->assign('h5pContentUpgradeScriptUrl', $scriptBaseUrl . '/h5p-content-upgrade.js');
        $this->view->assign('h5pUtilsScriptUrl', $scriptBaseUrl . '/h5p-utils.js');
    }

}
