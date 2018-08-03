<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;

class ContentUpgradeAjaxController extends ActionController
{
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
     * @Flow\SkipCsrfProtection
     * @param int $oldLibraryId
     */
    public function migrateContentAction(int $oldLibraryId){
        echo $oldLibraryId;
        exit;
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
        $this->response->setHeader('Cache-Control', 'no-cache');
        $this->response->setHeader('Content-type', 'application/json');

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
            $response['upgradesScript'] = $this->h5pPublicFolderUrl . '/'. $this->h5pLibrariesFolderUrl . '/' . $library->getFolderName() . '/upgrades.js';
        }

        return json_encode($response);
    }

    /**
     * @param Library $library
     * @return bool
     */
    private function libraryFolderContainsUpgradeScript(Library $library) : bool
    {
        $localCopy = $library->getZippedLibraryFile()->createTemporaryLocalCopy();
        $zipArchive = new \ZipArchive();
        $zipArchive->open($localCopy);
        return $zipArchive->locateName('upgrades.js') !== false;
    }

}
