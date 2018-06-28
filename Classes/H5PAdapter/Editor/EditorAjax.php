<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Editor;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryInterface;
use Sandstorm\NeosH5P\Domain\Model\ContentTypeCacheEntry;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\Domain\Repository\ContentTypeCacheEntryRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;

/**
 * @Flow\Scope("singleton")
 */
class EditorAjax implements \H5PEditorAjaxInterface
{
    /**
     * @Flow\Inject
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * @Flow\Inject
     * @var ContentTypeCacheEntryRepository
     */
    protected $contentTypeCacheEntryRepository;

    /**
     * Gets latest library versions that exists locally
     *
     * @return array Latest version of all local libraries
     */
    public function getLatestLibraryVersions()
    {
        $librariesOrderedByMajorAndMinorVersion = $this->libraryRepository->findBy([], [
            'name' => QueryInterface::ORDER_DESCENDING,
            'majorVersion' => QueryInterface::ORDER_DESCENDING,
            'minorVersion' => QueryInterface::ORDER_DESCENDING
        ]);

        $versionInformation = [];
        /** @var Library $library */
        foreach ($librariesOrderedByMajorAndMinorVersion as $library) {
            if(array_key_exists($library->getName(), $versionInformation)) {
                continue;
            }
            $versionInformation[] = (object)[
                'id' => $library->getLibraryId(),
                'machine_name' => $library->getName(),
                'title' => $library->getTitle(),
                'major_version' => $library->getMajorVersion(),
                'minor_version' => $library->getMinorVersion(),
                'patch_version' => $library->getPatchVersion(),
                'restricted' => $library->isRestricted(),
                'has_icon' => $library->hasIcon()
            ];
        }
        return $versionInformation;
    }

    /**
     * Get locally stored Content Type Cache. If machine name is provided
     * it will only get the given content type from the cache
     *
     * @param $machineName
     *
     * @return array|object|null Returns results from querying the database
     */
    public function getContentTypeCache($machineName = NULL)
    {
        if ($machineName != null) {
            return $this->contentTypeCacheEntryRepository->findOneByMachineName($machineName);
        }

        return $this->contentTypeCacheEntryRepository->getContentTypeCacheObjects();
    }

    /**
     * Gets recently used libraries for the current author
     *
     * @return array machine names. The first element in the array is the
     * most recently used.
     */
    public function getAuthorsRecentlyUsedLibraries()
    {
        // TODO: Implement getAuthorsRecentlyUsedLibraries() method.
    }

    /**
     * Checks if the provided token is valid for this endpoint
     *
     * @param string $token The token that will be validated for.
     *
     * @return bool True if successful validation
     */
    public function validateEditorToken($token)
    {
        // TODO
        return true;
    }

}
