<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Error\Messages\Message;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Package\PackageManager;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Service\CRUD\LibraryCRUDService;
use Sandstorm\NeosH5P\Domain\Service\H5PIntegrationService;
use Sandstorm\NeosH5P\Domain\Service\UriGenerationService;

class LibraryController extends AbstractModuleController
{

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
     * @Flow\Inject(lazy=false)
     * @var \H5PCore
     */
    protected $h5pCore;

    /**
     * @Flow\Inject
     * @var PackageManager
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var UriGenerationService
     */
    protected $uriGenerationService;

    public function indexAction()
    {
        $libraries = $this->libraryRepository->findAll();
        $unusedLibraries = $this->libraryRepository->findUnused();
        $this->view->assign('libraries', $libraries);
        $this->view->assign('unusedLibraries', $unusedLibraries);
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
     * @throws StopActionException
     * @return bool
     */
    public function deleteUnusedAction()
    {
        $unusedLibraries = $this->libraryRepository->findUnused();
        foreach ($unusedLibraries as $library) {
            $this->libraryCRUDService->handleDelete($library);
        }

        $this->addFlashMessage('%s unused libraries have been deleted.', 'Libraries deleted', Message::SEVERITY_OK, [count($unusedLibraries)]);
        $this->redirect('index', null, null);
        return false;
    }

    /**
     * @param Library $library
     * @throws StopActionException
     */
    public function upgradeAction(Library $library)
    {
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
        $numberOfContentsString = $numberOfContentsUsingLibrary == 1 ? '1 content' : "$numberOfContentsUsingLibrary contents";

        $scriptBaseUrl = $this->h5pPublicFolderUrl . $this->h5pCorePublicFolderName . '/js';

        $libraryInfoUri = $this->uriGenerationService->buildUriWithMainRequest(
            $this->controllerContext,
            'libraryInfo',
            null,
            'Backend\ContentUpgradeAjax',
            'Sandstorm.NeosH5P'
        );

        $migrateContentUri = $this->uriGenerationService->buildUriWithMainRequest(
            $this->controllerContext,
            'migrateContent',
            ['oldLibraryId' => $library->getLibraryId()],
            'Backend\ContentUpgradeAjax',
            'Sandstorm.NeosH5P'
        );

        $availableVersions = [];
        foreach ($libsWithNewerVersion as $library) {
            $availableVersions[$library->getLibraryId()] = $library->getVersionString();
        }

        $settings = array(
            'containerSelector' => '#h5p-admin-container',
            'libraryInfo' => array(
                'message' => "You are about to upgrade $numberOfContentsString to a new library version. Please select the upgrade version.",
                'inProgress' => 'Upgrading to %ver...',
                'error' => 'An error occurred while processing parameters:',
                'errorData' => 'Could not load data for library %lib.',
                'errorContent' => 'Could not upgrade content %id:',
                'errorScript' => 'Could not load upgrades script for %lib.',
                'errorParamsBroken' => 'Parameters are broken.',
                'done' => "You have successfully upgraded $numberOfContentsString",
                'library' => [
                    'name' => $library->getName(),
                    'version' => $library->getMajorVersion() . '.' . $library->getMinorVersion()
                ],
                'libraryBaseUrl' => $libraryInfoUri,
                'scriptBaseUrl' => $scriptBaseUrl,
                'buster' => '?ver=' . $installedH5pVersion,
                'versions' => $availableVersions,
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
