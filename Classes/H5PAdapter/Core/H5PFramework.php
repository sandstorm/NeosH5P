<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Core;

use Neos\Flow\Package\PackageManagerInterface;
use Sandstorm\NeosH5P\Domain\Model\ConfigSetting;
use Sandstorm\NeosH5P\Domain\Repository\ConfigSettingRepository;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class H5PFramework implements \H5PFrameworkInterface
{
    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var ConfigSettingRepository
     */
    protected $configSettingRepository;

    public function getPlatformInfo()
    {
        return [
            "name" => "Neos",
            "version" => $this->packageManager->getPackage("Neos.Neos")->getInstalledVersion(),
            "h5pVersion" => $this->packageManager->getPackage("Sandstorm.NeosH5P")->getInstalledVersion()
        ];
    }

    public function fetchExternalData($url, $data = NULL, $blocking = TRUE, $stream = NULL)
    {
        // TODO: Implement fetchExternalData() method.
    }

    public function setLibraryTutorialUrl($machineName, $tutorialUrl)
    {
        // TODO: Implement setLibraryTutorialUrl() method.
    }

    public function setErrorMessage($message, $code = NULL)
    {
        // TODO: Implement setErrorMessage() method.
    }

    public function setInfoMessage($message)
    {
        // TODO: Implement setInfoMessage() method.
    }

    public function getMessages($type)
    {
        // TODO: Implement getMessages() method.
    }

    public function t($message, $replacements = array())
    {
        // TODO: Implement t() method.
    }

    public function getLibraryFileUrl($libraryFolderName, $fileName)
    {
        // TODO: Implement getLibraryFileUrl() method.
    }

    public function getUploadedH5pFolderPath()
    {
        // TODO: Implement getUploadedH5pFolderPath() method.
    }

    public function getUploadedH5pPath()
    {
        // TODO: Implement getUploadedH5pPath() method.
    }

    public function loadLibraries()
    {
        // TODO: Implement loadLibraries() method.
    }

    public function getAdminUrl()
    {
        // TODO: Implement getAdminUrl() method.
    }

    public function getLibraryId($machineName, $majorVersion = NULL, $minorVersion = NULL)
    {
        // TODO: Implement getLibraryId() method.
    }

    public function getWhitelist($isLibrary, $defaultContentWhitelist, $defaultLibraryWhitelist)
    {
        // TODO: Implement getWhitelist() method.
    }

    public function isPatchedLibrary($library)
    {
        // TODO: Implement isPatchedLibrary() method.
    }

    public function isInDevMode()
    {
        // TODO: Implement isInDevMode() method.
    }

    public function mayUpdateLibraries()
    {
        // TODO: Implement mayUpdateLibraries() method.
    }

    public function saveLibraryData(&$libraryData, $new = TRUE)
    {
        // TODO: Implement saveLibraryData() method.
    }

    public function insertContent($content, $contentMainId = NULL)
    {
        // TODO: Implement insertContent() method.
    }

    public function updateContent($content, $contentMainId = NULL)
    {
        // TODO: Implement updateContent() method.
    }

    public function resetContentUserData($contentId)
    {
        // TODO: Implement resetContentUserData() method.
    }

    public function saveLibraryDependencies($libraryId, $dependencies, $dependency_type)
    {
        // TODO: Implement saveLibraryDependencies() method.
    }

    public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = NULL)
    {
        // TODO: Implement copyLibraryUsage() method.
    }

    public function deleteContentData($contentId)
    {
        // TODO: Implement deleteContentData() method.
    }

    public function deleteLibraryUsage($contentId)
    {
        // TODO: Implement deleteLibraryUsage() method.
    }

    public function saveLibraryUsage($contentId, $librariesInUse)
    {
        // TODO: Implement saveLibraryUsage() method.
    }

    public function getLibraryUsage($libraryId, $skipContent = FALSE)
    {
        // TODO: Implement getLibraryUsage() method.
    }

    public function loadLibrary($machineName, $majorVersion, $minorVersion)
    {
        // TODO: Implement loadLibrary() method.
    }

    public function loadLibrarySemantics($machineName, $majorVersion, $minorVersion)
    {
        // TODO: Implement loadLibrarySemantics() method.
    }

    public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion)
    {
        // TODO: Implement alterLibrarySemantics() method.
    }

    public function deleteLibraryDependencies($libraryId)
    {
        // TODO: Implement deleteLibraryDependencies() method.
    }

    public function lockDependencyStorage()
    {
        // TODO: Implement lockDependencyStorage() method.
    }

    public function unlockDependencyStorage()
    {
        // TODO: Implement unlockDependencyStorage() method.
    }

    public function deleteLibrary($library)
    {
        // TODO: Implement deleteLibrary() method.
    }

    public function loadContent($id)
    {
        // TODO: Implement loadContent() method.
    }

    public function loadContentDependencies($id, $type = NULL)
    {
        // TODO: Implement loadContentDependencies() method.
    }

    public function getOption($name, $default = NULL)
    {
        $configSetting = $this->configSettingRepository->findOneByKey($name);

        if ($configSetting != null) {
            return $configSetting->getValue();
        }

        return $default;
    }

    public function setOption($name, $value)
    {
        // TODO: Implement setOption() method.
    }

    public function updateContentFields($id, $fields)
    {
        // TODO: Implement updateContentFields() method.
    }

    public function clearFilteredParameters($library_id)
    {
        // TODO: Implement clearFilteredParameters() method.
    }

    public function getNumNotFiltered()
    {
        // TODO: Implement getNumNotFiltered() method.
    }

    public function getNumContent($libraryId)
    {
        // TODO: Implement getNumContent() method.
    }

    public function isContentSlugAvailable($slug)
    {
        // TODO: Implement isContentSlugAvailable() method.
    }

    public function getLibraryStats($type)
    {
        // TODO: Implement getLibraryStats() method.
    }

    public function getNumAuthors()
    {
        // TODO: Implement getNumAuthors() method.
    }

    public function saveCachedAssets($key, $libraries)
    {
        // TODO: Implement saveCachedAssets() method.
    }

    public function deleteCachedAssets($library_id)
    {
        // TODO: Implement deleteCachedAssets() method.
    }

    public function getLibraryContentCount()
    {
        // TODO: Implement getLibraryContentCount() method.
    }

    public function afterExportCreated($content, $filename)
    {
        // TODO: Implement afterExportCreated() method.
    }

    public function hasPermission($permission, $id = NULL)
    {
        // TODO: Implement hasPermission() method.
    }

    public function replaceContentTypeCache($contentTypeCache)
    {
        // TODO: Implement replaceContentTypeCache() method.
    }


}
