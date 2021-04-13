<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Aspect\PersistenceMagicInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;
use Sandstorm\NeosH5P\Domain\Service\CRUD\ContentCRUDService;
use Sandstorm\NeosH5P\H5PAdapter\Core\H5PFramework;

class ContentUpgradeAjaxController extends ActionController
{
    /**
     * @Flow\Inject
     * @var H5PFramework
     */
    protected $h5pFramework;

    /**
     * @Flow\Inject(lazy=false)
     * @var \H5PCore
     */
    protected $h5pCore;

    /**
     * @Flow\InjectConfiguration(path="h5pPublicFolder.url")
     * @var string
     */
    protected $h5pPublicFolderUrl;

    /**
     * @Flow\InjectConfiguration(path="h5pPublicFolder.subfolders.libraries")
     * @var string
     */
    protected $h5pLibrariesFolderUrl;

    /**
     * @Flow\Inject
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * @Flow\Inject
     * @var ContentCRUDService
     */
    protected $contentCRUDService;

    /**
     * @Flow\SkipCsrfProtection
     * @param int $oldLibraryId
     * @param int $libraryId
     * @param string $params
     */
    public function migrateContentAction(int $oldLibraryId, int $libraryId, string $params = null)
    {
        $this->response->addHttpHeader('Cache-Control', 'no-cache');
        $this->response->addHttpHeader('Content-Type', 'application/json');

        // $params is null in the first request because the client does not yet know them
        if ($params !== null) {
            /** @var Library $targetLibrary */
            $targetLibrary = $this->libraryRepository->findOneByLibraryId($libraryId);

            $decodedParams = json_decode($params);
            foreach ($decodedParams as $id => $migratedParams) {
                $this->contentCRUDService->handleUpgrade($id, $migratedParams, $targetLibrary);
                $this->persistenceManager->persistAll();
            }
        }

        $this->persistenceManager->clearState();
        $remainingContentForOldLibrary = $this->h5pFramework->getNumContent($oldLibraryId);

        /** @var Library $oldLibrary */
        $oldLibrary = $this->libraryRepository->findOneByLibraryId($oldLibraryId);

        $response = [
            'token' => 'dummy',
            'left' => $remainingContentForOldLibrary,
            'params' => []
        ];

        foreach ($this->contentRepository->findFirstTenContentsByLibrary($oldLibrary) as $content) {
            // The params of each content object are expected as a JSON string, not a JSON object. They get decoded client-side.
            $response['params'][$content->getContentId()] = json_encode($content->getParamsWithMetadata());
        }

        return json_encode($response);
    }


    /**
     * @Flow\SkipCsrfProtection
     * @param string $libraryName
     * @param int $majorVersion
     * @param int $minorVersion
     * @return string
     */
    public function libraryInfoAction(string $libraryName, int $majorVersion, int $minorVersion)
    {
        $this->response->addHttpHeader('Cache-Control', 'no-cache');
        $this->response->addHttpHeader('Content-Type', 'application/json');

        $library = $this->libraryRepository->findOneByNameMajorVersionAndMinorVersion($libraryName, $majorVersion, $minorVersion);

        $response = [
            'name' => $libraryName,
            'version' => [
                'major' => $majorVersion,
                'minor' => $minorVersion
            ],
            // json_decode because it would be json_encoded twice otherwise
            'semantics' => json_decode($library->getSemantics()),
        ];

        if ($this->libraryFolderContainsUpgradeScript($library)) {
            // We assume that the library is already published
            $response['upgradesScript'] = $this->h5pPublicFolderUrl . '/' . $this->h5pLibrariesFolderUrl . '/' . $library->getFolderName() . '/upgrades.js';
        }

        return json_encode($response);
    }

    /**
     * @param Library $library
     * @return bool
     */
    private function libraryFolderContainsUpgradeScript(Library $library): bool
    {
        $localCopy = $library->getZippedLibraryFile()->createTemporaryLocalCopy();
        $zipArchive = new \ZipArchive();
        $zipArchive->open($localCopy);
        return $zipArchive->locateName('upgrades.js') !== false;
    }

}
