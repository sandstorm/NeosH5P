<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Neos\Flow\Exception;
use Neos\Flow\ObjectManagement\DependencyInjection\DependencyProxy;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Neos\Domain\Service\UserService;
use Neos\Utility\ObjectAccess;
use Sandstorm\NeosH5P\Domain\Model\CachedAsset;
use Sandstorm\NeosH5P\Domain\Model\ConfigSetting;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Model\ContentDependency;
use Sandstorm\NeosH5P\Domain\Model\ContentTypeCacheEntry;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\Domain\Model\LibraryDependency;
use Sandstorm\NeosH5P\Domain\Model\LibraryTranslation;
use Sandstorm\NeosH5P\Domain\Repository\CachedAssetRepository;
use Sandstorm\NeosH5P\Domain\Repository\ConfigSettingRepository;
use Sandstorm\NeosH5P\Domain\Repository\ContentDependencyRepository;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\ContentTypeCacheEntryRepository;
use Sandstorm\NeosH5P\Domain\Repository\ContentUserDataRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryDependencyRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryTranslationRepository;

/**
 * @Flow\Scope("singleton")
 */
class H5PFramework implements \H5PFrameworkInterface
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * !!! DO NOT ACCESS THIS DIRECTLY!
     * Use $this->>getInjectedH5PCore instead. Reason: This object exposes public properties (such as "fs" for the file
     * storage adapter) which, when accessed, do not trigger the activation of the dependency proxy (only method calls
     * do that). So we need to make sure the dependency proxy is resolved before accessing the H5PCore object.
     * Because this object is itself a construction parameter of H5PCore, we cannot use lazy=false as that would lead
     * to a circular DI graph.
     *
     * @Flow\Inject
     * @var \H5PCore
     */
    protected $h5pCore;

    /**
     * @Flow\Inject(lazy=false)
     * @var FileAdapter
     */
    protected $fileAdapter;

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

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
     * @var ContentDependencyRepository
     */
    protected $contentDependencyRepository;

    /**
     * @Flow\Inject
     * @var ContentUserDataRepository
     */
    protected $contentUserDataRepository;

    /**
     * @Flow\Inject
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * @Flow\Inject
     * @var LibraryDependencyRepository
     */
    protected $libraryDependencyRepository;

    /**
     * @Flow\Inject
     * @var LibraryTranslationRepository
     */
    protected $libraryTranslationRepository;

    /**
     * @Flow\Inject
     * @var ConfigSettingRepository
     */
    protected $configSettingRepository;

    /**
     * @Flow\Inject
     * @var ContentTypeCacheEntryRepository
     */
    protected $contentTypeCacheEntryRepository;

    /**
     * @Flow\Inject
     * @var CachedAssetRepository
     */
    protected $cachedAssetRepository;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * ================================================================================================================
     * ================================================================================================================
     * Non-Injected Properties
     * ================================================================================================================
     * ================================================================================================================
     */


    /**
     * Path to a temporary folder where uploaded H5P content is processed.
     * Needs to be stable during one request.
     *
     * @var string
     */
    protected $uploadedH5pFolderPath;

    /**
     * Path to a temporary H5P file.
     * Needs to be stable during one request.
     *
     * @var string
     */
    protected $uploadedH5pPath;

    /**
     * Error and info message store.
     * Needs to be stable during one request.
     *
     * @var array
     */
    protected $messages;


    /**
     * ================================================================================================================
     * ================================================================================================================
     * Custom Methods / Helper Functions
     * ================================================================================================================
     * ================================================================================================================
     */
    protected function getInjectedH5PCore(): \H5PCore
    {
        if ($this->h5pCore instanceof DependencyProxy) {
            $this->h5pCore->_activateDependency();
        }
        return $this->h5pCore;
    }

    /**
     * ================================================================================================================
     * ================================================================================================================
     * Authorization
     * ================================================================================================================
     * ================================================================================================================
     */


    /**
     * Is the current user allowed to update libraries?
     *
     * @return boolean
     *  TRUE if the user is allowed to update libraries
     *  FALSE if the user is not allowed to update libraries
     */
    public function mayUpdateLibraries()
    {
        // TODO: Proper implementation
        return true;
    }

    /**
     * Check if user has permissions to an action
     *
     * @method hasPermission
     * @param  [H5PPermission] $permission Permission type, ref H5PPermission
     * @param  [int]           $id         Id need by platform to determine permission
     * @return boolean
     */
    public function hasPermission($permission, $id = NULL)
    {
        // TODO: Proper implementation
        return true;
    }


    /**
     * ================================================================================================================
     * ================================================================================================================
     * Content Model
     * ================================================================================================================
     * ================================================================================================================
     */


    /**
     * Insert new content.
     *
     * @param array $content
     *   An associative array containing:
     *   - id: The content id
     *   - params: The content in json format
     *   - library: An associative array containing:
     *     - libraryId: The id of the main library for this content
     * @param int $contentMainId
     *   Main id for the content if this is a system that supports versions
     * @return string
     */
    public function insertContent($content, $contentMainId = NULL)
    {
        /** @var Library $library */
        $library = $this->libraryRepository->findOneByLibraryId($content['library']['libraryId']);
        $account = $this->userService->getCurrentUser()->getAccounts()->first();
        $content = Content::createFromMetadata($content, $library, $account);

        // Persist and re-read the entity to generate the content ID in the DB and fill the field
        $this->contentRepository->add($content);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();
        /** @var Content $content */
        $content = $this->contentRepository->findByIdentifier($this->persistenceManager->getIdentifierByObject($content));

        return $content->getContentId();
    }

    /**
     * Update old content.
     *
     * @param array $content
     *   An associative array containing:
     *   - id: The content id
     *   - params: The content in json format
     *   - library: An associative array containing:
     *     - libraryId: The id of the main library for this content
     * @param int $contentMainId
     *   Main id for the content if this is a system that supports versions
     */
    public function updateContent($content, $contentMainId = NULL)
    {
        // TODO: Implement updateContent() method.
    }

    /**
     * Resets marked user data for the given content.
     *
     * @param int $contentId
     */
    public function resetContentUserData($contentId)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($contentId);
        if ($content === null) {
            return;
        }
        foreach ($content->getContentUserDatas() as $contentUserData) {
            $this->contentUserDataRepository->remove($contentUserData);
        }
    }

    /**
     * Deletes content data
     *
     * @param int $contentId
     *   Id identifying the content
     */
    public function deleteContentData($contentId)
    {
        // TODO: Implement deleteContentData() method.
    }

    /**
     * Delete what libraries a content item is using
     *
     * @param int $contentId
     *   Content Id of the content we'll be deleting library usage for
     */
    public function deleteLibraryUsage($contentId)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($contentId);
        if ($content === null) {
            return;
        }
        foreach ($content->getContentDependencies() as $contentDependency) {
            $this->persistenceManager->whitelistObject($contentDependency);
            $this->contentDependencyRepository->remove($contentDependency);
        }
        // Persist, because directly afterwards saveLibraryUsage() might be called
        $this->persistenceManager->persistAll();
    }

    /**
     * Saves what libraries the content uses
     *
     * @param int $contentId
     *   Id identifying the content
     * @param array $librariesInUse
     *   List of libraries the content uses. Libraries consist of associative arrays with:
     *   - library: Associative array containing:
     *     - dropLibraryCss(optional): comma separated list of machineNames
     *     - machineName: Machine name for the library
     *     - libraryId: Id of the library
     *   - type: The dependency type. Allowed values:
     *     - editor
     *     - dynamic
     *     - preloaded
     */
    public function saveLibraryUsage($contentId, $librariesInUse)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($contentId);
        if ($content === null) {
            return;
        }

        $dropLibraryCssList = [];
        foreach ($librariesInUse as $dependencyData) {
            if (!empty($dependencyData['library']['dropLibraryCss'])) {
                $dropLibraryCssList = array_merge($dropLibraryCssList, explode(', ', $dependencyData['library']['dropLibraryCss']));
            }
        }

        foreach ($librariesInUse as $dependencyData) {
            $contentDependency = new ContentDependency();
            $contentDependency->setContent($content);
            $contentDependency->setLibrary($this->libraryRepository->findOneByLibraryId($dependencyData['library']['libraryId']));
            $contentDependency->setDependencyType($dependencyData['type']);
            $contentDependency->setDropCss(in_array($dependencyData['library']['machineName'], $dropLibraryCssList));
            $contentDependency->setWeight($dependencyData['weight']);
            $this->contentDependencyRepository->add($contentDependency);
            $this->persistenceManager->whitelistObject($contentDependency);
        }
        $this->persistenceManager->persistAll();
    }

    /**
     * Load content.
     *
     * @param int $id
     *   Content identifier
     * @return array
     *   Associative array containing:
     *   - contentId: Identifier for the content
     *   - params: json content as string
     *   - embedType: csv of embed types
     *   - title: The contents title
     *   - language: Language code for the content
     *   - libraryId: Id for the main library
     *   - libraryName: The library machine name
     *   - libraryMajorVersion: The library's majorVersion
     *   - libraryMinorVersion: The library's minorVersion
     *   - libraryEmbedTypes: CSV of the main library's embed types
     *   - libraryFullscreen: 1 if fullscreen is supported. 0 otherwise.
     */
    public function loadContent($id)
    {
        // TODO: Implement loadContent() method.
    }

    /**
     * Load dependencies for the given content of the given type.
     *
     * @param int $id
     *   Content identifier
     * @param int $type
     *   Dependency types. Allowed values:
     *   - editor
     *   - preloaded
     *   - dynamic
     * @return array
     *   List of associative arrays containing:
     *   - libraryId: The id of the library if it is an existing library.
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     *   - patchVersion: The library's patchVersion
     *   - preloadedJs(optional): comma separated string with js file paths
     *   - preloadedCss(optional): comma separated sting with css file paths
     *   - dropCss(optional): csv of machine names
     */
    public function loadContentDependencies($id, $type = NULL)
    {
        $dependencyArray = [];
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($id);
        if ($content === null) {
            return $dependencyArray;
        }

        $criteria = [
            'content' => $content
        ];
        if ($type !== null) {
            $criteria['dependencyType'] = $type;
        }

        $dependencies = $this->contentDependencyRepository->findBy($criteria, ['weight' => QueryInterface::ORDER_ASCENDING]);
        /** @var ContentDependency $dependency */
        foreach ($dependencies as $dependency) {
            $dependencyArray[] = $dependency->toAssocArray();
        }

        return $dependencyArray;
    }

    /**
     * This will update selected fields on the given content.
     *
     * @param int $id Content identifier
     * @param array $fields Content fields, e.g. filtered or slug.
     */
    public function updateContentFields($id, $fields)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($id);
        if ($content === null) {
            return;
        }

        foreach ($fields as $propertyName => $value) {
            ObjectAccess::setProperty($content, $propertyName, $value);
        }

        try {
            $this->contentRepository->update($content);
        } catch (IllegalObjectTypeException $ex) {
            // will never happen
        }
    }

    /**
     * Will clear filtered params for all the content that uses the specified
     * library. This means that the content dependencies will have to be rebuilt,
     * and the parameters re-filtered.
     *
     * @param int $library_id
     * @throws Exception
     */
    public function clearFilteredParameters($library_id)
    {
        /** @var Library $library */
        $library = $this->libraryRepository->findOneByLibraryId($library_id);
        if ($library === null) {
            throw new Exception("Library with ID " . $library_id . " could not be found!");
        }
        $contentsOfThisLibrary = $this->contentRepository->findByLibrary($library);
        /** @var Content $content */
        foreach ($contentsOfThisLibrary as $content) {
            $content->setFiltered('');
            $this->contentRepository->update($content);
        }
    }

    /**
     * Get number of contents that has to get their content dependencies rebuilt
     * and parameters re-filtered.
     *
     * @return int
     */
    public function getNumNotFiltered()
    {
        // TODO: Implement getNumNotFiltered() method.
    }

    /**
     * Get number of contents using library as main library.
     *
     * @param int $libraryId
     * @return int
     */
    public function getNumContent($libraryId)
    {
        // TODO: Implement getNumContent() method.
    }

    /**
     * Determines if content slug is used.
     *
     * @param string $slug
     * @return boolean
     */
    public function isContentSlugAvailable($slug)
    {
        return $this->contentRepository->findOneBySlug($slug) === null;
    }


    /**
     * ================================================================================================================
     * ================================================================================================================
     * Library Model
     * ================================================================================================================
     * ================================================================================================================
     */

    /**
     * Set the tutorial URL for a library. All versions of the library is set
     *
     * @param string $machineName
     * @param string $tutorialUrl
     */
    public function setLibraryTutorialUrl($machineName, $tutorialUrl)
    {
        // TODO: Implement setLibraryTutorialUrl() method.
    }

    /**
     * Get a list of the current installed libraries
     *
     * @return array
     *   Associative array containing one entry per machine name.
     *   For each machineName there is a list of libraries(with different versions)
     */
    public function loadLibraries()
    {
        // TODO: Implement loadLibraries() method.
        return [];
    }

    /**
     * Get id to an existing library.
     * If version number is not specified, the newest version will be returned.
     *
     * @param string $machineName
     *   The librarys machine name
     * @param int $majorVersion
     *   Optional major version number for library
     * @param int $minorVersion
     *   Optional minor version number for library
     * @return int
     *   The id of the specified library or FALSE
     */
    public function getLibraryId($machineName, $majorVersion = NULL, $minorVersion = NULL)
    {
        $criteria = ['name' => $machineName];
        if ($majorVersion) {
            $criteria['majorVersion'] = $majorVersion;
        }
        if ($minorVersion) {
            $criteria['minorVersion'] = $minorVersion;
        }

        $libraries = $this->libraryRepository->findBy(
            $criteria,
            [
                'majorVersion' => QueryInterface::ORDER_DESCENDING,
                'minorVersion' => QueryInterface::ORDER_DESCENDING,
                'patchVersion' => QueryInterface::ORDER_DESCENDING,
            ]
        );

        if (count($libraries) > 0) {
            /** @var Library $library */
            $library = $libraries[0];
            return $library->getLibraryId();
        }
        return false;
    }

    /**
     * Is the library a patched version of an existing library?
     *
     * @param object $library
     *   An associative array containing:
     *   - machineName: The library machineName
     *   - majorVersion: The librarys majorVersion
     *   - minorVersion: The librarys minorVersion
     *   - patchVersion: The librarys patchVersion
     * @return boolean
     *   TRUE if the library is a patched version of an existing library
     *   FALSE otherwise
     */
    public function isPatchedLibrary($library)
    {
        $criteria = [
            'name' => $library['machineName'],
            'majorVersion' => $library['majorVersion'],
            'minorVersion' => $library['minorVersion'],
            'patchVersion' => $library['patchVersion']
        ];

        $existingLibraries = $this->libraryRepository->findBy($criteria);

        return count($existingLibraries) > 0;
    }

    /**
     * Store data about a library
     *
     * Also fills in the libraryId in the libraryData object if the object is new
     *
     * @param array $libraryData
     *   Associative array containing:
     *   - libraryId: The id of the library if it is an existing library.
     *   - title: The library's name
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     *   - patchVersion: The library's patchVersion
     *   - runnable: 1 if the library is a content type, 0 otherwise
     *   - fullscreen(optional): 1 if the library supports fullscreen, 0 otherwise
     *   - embedTypes(optional): list of supported embed types
     *   - preloadedJs(optional): list of associative arrays containing:
     *     - path: path to a js file relative to the library root folder
     *   - preloadedCss(optional): list of associative arrays containing:
     *     - path: path to css file relative to the library root folder
     *   - dropLibraryCss(optional): list of associative arrays containing:
     *     - machineName: machine name for the librarys that are to drop their css
     *   - semantics(optional): Json describing the content structure for the library
     *   - language(optional): associative array containing:
     *     - languageCode: Translation in json format
     * @param bool $new
     * @throws Exception
     * @return
     */
    public function saveLibraryData(&$libraryData, $new = TRUE)
    {
        $library = null;
        if ($new) {
            $library = Library::createFromMetadata($libraryData);
            $this->libraryRepository->add($library);
            // Persist and re-read the entity to generate the library ID in the DB and fill the field
            $this->persistenceManager->persistAll();
            $this->persistenceManager->clearState();
            $library = $this->libraryRepository->findByIdentifier($this->persistenceManager->getIdentifierByObject($library));
            $libraryData['libraryId'] = $library->getLibraryId();
        } else {
            /** @var Library $library */
            $library = $this->libraryRepository->findOneByLibraryId($libraryData['libraryId']);
            if ($library === null) {
                throw new Exception("Library with ID " . $libraryData['libraryId'] . " could not be found!");
            }
            Library::updateFromMetadata($libraryData, $library);
            $this->libraryRepository->update($library);
            $this->deleteLibraryDependencies($libraryData['libraryId']);
        }

        // Update languages
        $translations = $this->libraryTranslationRepository->findByLibrary($library);
        /** @var LibraryTranslation $translation */
        foreach ($translations as $translation) {
            $this->libraryTranslationRepository->remove($translation);
        }
        // Persist before we create new translations
        $this->persistenceManager->persistAll();

        if (isset($libraryData['language'])) {
            foreach ($libraryData['language'] as $languageCode => $translation) {
                $libraryTranslation = LibraryTranslation::create($library, $languageCode, $translation);
                $this->libraryTranslationRepository->add($libraryTranslation);
            }
        }
    }

    /**
     * Save what libraries a library is depending on
     *
     * @param int $libraryId
     *   Library Id for the library we're saving dependencies for
     * @param array $dependencies
     *   List of dependencies as associative arrays containing:
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     * @param string $dependency_type
     *   What type of dependency this is, the following values are allowed:
     *   - editor
     *   - preloaded
     *   - dynamic
     * @throws Exception
     */
    public function saveLibraryDependencies($libraryId, $dependencies, $dependency_type)
    {
        $dependingLibrary = $this->libraryRepository->findOneByLibraryId($libraryId);
        if ($dependingLibrary === null) {
            throw new Exception("The Library with ID " . $libraryId . " could not be found.");
        }

        foreach ($dependencies as $dependency) {
            // Load the library we're depending on
            /** @var Library $requiredLibrary */
            $requiredLibrary = $this->libraryRepository->findOneBy([
                'name' => $dependency['machineName'],
                'majorVersion' => $dependency['majorVersion'],
                'minorVersion' => $dependency['minorVersion']
            ]);
            // We don't have this library and thus can't register a dependency
            if ($requiredLibrary === null) {
                continue;
            }
            /** @var LibraryDependency $existingDependency */
            $existingDependency = $this->libraryDependencyRepository->findOneBy([
                'library' => $dependingLibrary,
                'requiredLibrary' => $requiredLibrary
            ]);
            if ($existingDependency !== null) {
                // Dependency exists, only update the type
                $existingDependency->setDependencyType($dependency_type);
                $this->libraryDependencyRepository->update($existingDependency);
            } else {
                // Depedency does not exist, create it
                $dependency = new LibraryDependency($dependingLibrary, $requiredLibrary, $dependency_type);
                $this->libraryDependencyRepository->add($dependency);
            }
        }
    }

    /**
     * Give an H5P the same library dependencies as a given H5P
     *
     * @param int $contentId
     *   Id identifying the content
     * @param int $copyFromId
     *   Id identifying the content to be copied
     * @param int $contentMainId
     *   Main id for the content, typically used in frameworks
     *   That supports versions. (In this case the content id will typically be
     *   the version id, and the contentMainId will be the frameworks content id
     */
    public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = NULL)
    {
        // TODO: Implement copyLibraryUsage() method.
    }

    /**
     * Get number of content/nodes using a library, and the number of
     * dependencies to other libraries
     *
     * @param int $libraryId
     *   Library identifier
     * @param boolean $skipContent
     *   Flag to indicate if content usage should be skipped
     * @return array
     *   Associative array containing:
     *   - content: Number of content using the library
     *   - libraries: Number of libraries depending on the library
     */
    public function getLibraryUsage($libraryId, $skipContent = FALSE)
    {
        // TODO: Implement getLibraryUsage() method.
    }

    /**
     * Loads a library
     *
     * @param string $machineName
     *   The library's machine name
     * @param int $majorVersion
     *   The library's major version
     * @param int $minorVersion
     *   The library's minor version
     * @return array|FALSE
     *   FALSE if the library does not exist.
     *   Otherwise an associative array containing:
     *   - libraryId: The id of the library if it is an existing library.
     *   - title: The library's name
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     *   - patchVersion: The library's patchVersion
     *   - runnable: 1 if the library is a content type, 0 otherwise
     *   - fullscreen(optional): 1 if the library supports fullscreen, 0 otherwise
     *   - embedTypes(optional): list of supported embed types
     *   - preloadedJs(optional): comma separated string with js file paths
     *   - preloadedCss(optional): comma separated sting with css file paths
     *   - dropLibraryCss(optional): list of associative arrays containing:
     *     - machineName: machine name for the librarys that are to drop their css
     *   - semantics(optional): Json describing the content structure for the library
     *   - preloadedDependencies(optional): list of associative arrays containing:
     *     - machineName: Machine name for a library this library is depending on
     *     - majorVersion: Major version for a library this library is depending on
     *     - minorVersion: Minor for a library this library is depending on
     *   - dynamicDependencies(optional): list of associative arrays containing:
     *     - machineName: Machine name for a library this library is depending on
     *     - majorVersion: Major version for a library this library is depending on
     *     - minorVersion: Minor for a library this library is depending on
     *   - editorDependencies(optional): list of associative arrays containing:
     *     - machineName: Machine name for a library this library is depending on
     *     - majorVersion: Major version for a library this library is depending on
     *     - minorVersion: Minor for a library this library is depending on
     */
    public function loadLibrary($machineName, $majorVersion, $minorVersion)
    {
        /** @var Library $library */
        $library = $this->libraryRepository->findOneBy([
            'name' => $machineName,
            'majorVersion' => $majorVersion,
            'minorVersion' => $minorVersion
        ]);
        if ($library === null) {
            return false;
        }

        return $library->toAssocArray();
    }

    /**
     * Loads library semantics.
     *
     * @param string $machineName
     *   Machine name for the library
     * @param int $majorVersion
     *   The library's major version
     * @param int $minorVersion
     *   The library's minor version
     * @return string
     *   The library's semantics as json
     */
    public function loadLibrarySemantics($machineName, $majorVersion, $minorVersion)
    {
        /** @var Library $library */
        $library = $this->libraryRepository->findOneBy([
            'name' => $machineName,
            'majorVersion' => $majorVersion,
            'minorVersion' => $minorVersion
        ]);
        if ($library === null) {
            return null;
        }
        return $library->getSemantics();
    }

    /**
     * Makes it possible to alter the semantics, adding custom fields, etc.
     *
     * @param array $semantics
     *   Associative array representing the semantics
     * @param string $machineName
     *   The library's machine name
     * @param int $majorVersion
     *   The library's major version
     * @param int $minorVersion
     *   The library's minor version
     */
    public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion)
    {
        // Not implemented yet
    }

    /**
     * Delete all dependencies belonging to given library
     *
     * @param int $libraryId
     *   Library identifier
     */
    public function deleteLibraryDependencies($libraryId)
    {
        $library = $this->libraryRepository->findOneByLibraryId($libraryId);
        if ($library === null) {
            return;
        }
        $dependencies = $this->libraryDependencyRepository->findByLibrary($library);
        /** @var LibraryDependency $dependency */
        foreach ($dependencies as $dependency) {
            $this->libraryDependencyRepository->remove($dependency);
        }

        // Make sure we persist here, because new dependencies can be created right afterwards
        $this->persistenceManager->persistAll();
    }

    /**
     * Start an atomic operation against the dependency storage
     */
    public function lockDependencyStorage()
    {
        // TODO: Implement lockDependencyStorage() method.
    }

    /**
     * Stops an atomic operation against the dependency storage
     */
    public function unlockDependencyStorage()
    {
        // TODO: Implement unlockDependencyStorage() method.
    }

    /**
     * Delete a library from database and file system
     *
     * @param \stdClass $library
     *   Library object with id, name, major version and minor version.
     */
    public function deleteLibrary($library)
    {
        // TODO: Implement deleteLibrary() method.
    }

    /**
     * Generates statistics from the event log per library
     *
     * @param string $type Type of event to generate stats for
     * @return array Number values indexed by library name and version
     */
    public function getLibraryStats($type)
    {
        // TODO: Implement getLibraryStats() method.
        return [];
    }

    /**
     * Get the amount of content items associated to a library
     * return int
     */
    public function getLibraryContentCount()
    {
        // TODO: Implement getLibraryContentCount() method.
        return 0;
    }

    /**
     * ================================================================================================================
     * ================================================================================================================
     * Other Models
     * ================================================================================================================
     * ================================================================================================================
     */


    /**
     * Get stored setting.
     *
     * @param string $name
     *   Identifier for the setting
     * @param string $default
     *   Optional default value if settings is not set
     * @return mixed
     *   Whatever has been stored as the setting
     */
    public function getOption($name, $default = NULL)
    {
        /** @var ConfigSetting $configSetting */
        $configSetting = $this->configSettingRepository->findOneByConfigKey($name);

        if ($configSetting != null) {
            return $configSetting->getConfigValue();
        }

        return $default;
    }

    /**
     * Stores the given setting.
     * For example when did we last check h5p.org for updates to our libraries.
     *
     * @param string $name
     *   Identifier for the setting
     * @param mixed $value Data
     *   Whatever we want to store as the setting
     */
    public function setOption($name, $value)
    {
        /** @var ConfigSetting $configSetting */
        $configSetting = $this->configSettingRepository->findOneByConfigKey($name);

        try {
            if ($configSetting != null) {
                $configSetting->setConfigValue($value);
                $this->configSettingRepository->update($configSetting);
            } else {
                $configSetting = new ConfigSetting($name, $value);
                $this->configSettingRepository->add($configSetting);
            }
            $this->persistenceManager->persistAll();
        } catch (IllegalObjectTypeException $ex) {
            // Swallow, will never happen
        }

    }

    /**
     * Stores hash keys for cached assets, aggregated JavaScripts and
     * stylesheets, and connects it to libraries so that we know which cache file
     * to delete when a library is updated.
     *
     * @param string $key
     *  Hash key for the given libraries
     * @param array $libraries
     *  List of dependencies(libraries) used to create the key
     */
    public function saveCachedAssets($key, $libraries)
    {
        /**
         * This is called after FileAdapter->cacheAssets and makes the assignment of
         * CachedAsset and Library.
         * @see FileAdapter::cacheAssets()
         * @see \H5PCore::getDependenciesFiles()
         */

        $cachedAssets = $this->cachedAssetRepository->findByHashKey($key);

        /** @var CachedAsset $cachedAsset */
        foreach ($cachedAssets as $cachedAsset) {
            foreach ($libraries as $libraryData) {
                /** @var Library $library */
                $library = $this->libraryRepository->findOneByLibraryId($libraryData['libraryId']);
                if ($library === null) {
                    continue;
                }
                $library->addCachedAsset($cachedAsset);
                $cachedAsset->addLibrary($library);
                // Whitelist, as this can be called on GETs
                $this->persistenceManager->whitelistObject($library);
                $this->persistenceManager->whitelistObject($cachedAsset);
                try {
                    $this->libraryRepository->update($library);
                    $this->cachedAssetRepository->update($cachedAsset);
                } catch (IllegalObjectTypeException $ex) {
                    // Swallow, will never happen
                }
            }
        }
    }

    /**
     * Locate hash keys for given library and delete them.
     * Used when cache file are deleted.
     *
     * @param int $library_id
     *  Library identifier
     * @return array
     *  List of hash keys removed
     */
    public function deleteCachedAssets($library_id)
    {
        $removedKeys = [];

        /** @var Library $library */
        $library = $this->libraryRepository->findOneByLibraryId($library_id);
        if ($library === null) {
            return $removedKeys;
        }

        $cachedAssetsForLibrary = $this->cachedAssetRepository->findByLibrary($library);
        foreach ($cachedAssetsForLibrary as $cachedAsset) {
            $removedKeys[] = $this->persistenceManager->getIdentifierByObject($cachedAsset);
            $this->cachedAssetRepository->remove($cachedAsset);
        }
        return $removedKeys;
    }

    /**
     * Replaces existing content type cache with the one passed in
     *
     * @param object $contentTypeCache Json with an array called 'libraries'
     *  containing the new content type cache that should replace the old one.
     */
    public function replaceContentTypeCache($contentTypeCache)
    {
        // Remove all entries and persist
        $this->contentTypeCacheEntryRepository->removeAll();
        $this->persistenceManager->persistAll();

        // Create new entries
        foreach ($contentTypeCache->contentTypes as $contentType) {
            $this->contentTypeCacheEntryRepository->add(ContentTypeCacheEntry::create($contentType));
        }
    }


    /**
     * ================================================================================================================
     * ================================================================================================================
     * File Management
     * ================================================================================================================
     * ================================================================================================================
     */


    /**
     * Get URL to file in the specific library
     * @param string $libraryFolderName
     * @param string $fileName
     * @return string URL to file
     */
    public function getLibraryFileUrl($libraryFolderName, $fileName)
    {
        // TODO: Implement getLibraryFileUrl() method.
    }

    /**
     * Get the Path to the last uploaded h5p
     *
     * @return string
     *   Path to the folder where the last uploaded h5p for this session is located.
     */
    public function getUploadedH5pFolderPath()
    {
        if (!$this->uploadedH5pFolderPath) {
            $this->uploadedH5pFolderPath = $this->getInjectedH5PCore()->fs->getTmpPath();
        }
        return $this->uploadedH5pFolderPath;
    }

    /**
     * Get the path to the last uploaded h5p file
     *
     * @return string
     *   Path to the last uploaded h5p
     */
    public function getUploadedH5pPath()
    {
        if (!$this->uploadedH5pPath) {
            $this->uploadedH5pPath = $this->getInjectedH5PCore()->fs->getTmpPath() . '.h5p';
        }
        return $this->uploadedH5pPath;
    }

    /**
     * Get file extension whitelist
     *
     * The default extension list is part of h5p, but admins should be allowed to modify it
     *
     * @param boolean $isLibrary
     *   TRUE if this is the whitelist for a library. FALSE if it is the whitelist
     *   for the content folder we are getting
     * @param string $defaultContentWhitelist
     *   A string of file extensions separated by whitespace
     * @param string $defaultLibraryWhitelist
     *   A string of file extensions separated by whitespace
     * @return string
     */
    public function getWhitelist($isLibrary, $defaultContentWhitelist, $defaultLibraryWhitelist)
    {
        $whitelist = $defaultContentWhitelist;
        if ($isLibrary) {
            $whitelist .= ' ' . $defaultLibraryWhitelist;
        }
        return $whitelist;
    }

    /**
     * Will trigger after the export file is created.
     */
    public function afterExportCreated($content, $filename)
    {
        // TODO: Implement afterExportCreated() method.
    }



    /**
     * ================================================================================================================
     * ================================================================================================================
     * HTTP Requests
     * ================================================================================================================
     * ================================================================================================================
     */


    /**
     * Fetches a file from a remote server using HTTP GET
     *
     * @param string $url Where you want to get or send data.
     * @param array $data Data to post to the URL.
     * @param bool $blocking Set to 'FALSE' to instantly time out (fire and forget).
     * @param string $stream Path to where the file should be saved.
     * @return string The content (response body). NULL if something went wrong
     */
    public function fetchExternalData($url, $data = NULL, $blocking = TRUE, $stream = NULL)
    {
        $client = new Client();
        $options = [
            // if $blocking is set, we want to do a synchronous request
            'synchronous' => $blocking,
            // if we have something in $stream, we pass it into the sink
            'sink' => $stream,
            // post data goes in form_params
            'form_params' => $data
        ];

        try {
            // if $data is provided, we do a POST request - otherwise it's a GET
            $response = $client->request($data === null ? 'GET' : 'POST', $url, $options);
            if ($response->getStatusCode() === 200) {
                return $response->getBody()->getSize() ? $response->getBody()->getContents() : true;
            }
        } catch (GuzzleException $e) {
            $this->setErrorMessage($e->getMessage(), 'failed-fetching-external-data');
        }
        return false;
    }


    /**
     * ================================================================================================================
     * ================================================================================================================
     * System
     * ================================================================================================================
     * ================================================================================================================
     */

    /**
     * Returns info for the current platform
     *
     * @return array
     *   An associative array containing:
     *   - name: The name of the platform, for instance "Wordpress"
     *   - version: The version of the platform, for instance "4.0"
     *   - h5pVersion: The version of the H5P plugin/module
     */
    public function getPlatformInfo()
    {
        return [
            "name" => "Neos",
            "version" => $this->packageManager->getPackage("Neos.Neos")->getInstalledVersion(),
            "h5pVersion" => $this->packageManager->getPackage("Sandstorm.NeosH5P")->getInstalledVersion()
        ];
    }

    /**
     * Show the user an error message
     *
     * @param string $message The error message
     * @param string $code An optional code
     */
    public function setErrorMessage($message, $code = NULL)
    {
        $this->messages['error'][] = (object)[
            'code' => $code,
            'message' => $message
        ];
    }

    /**
     * Show the user an information message
     *
     * @param string $message
     *  The error message
     */
    public function setInfoMessage($message)
    {
        $this->messages['info'][] = $message;
    }

    /**
     * Return messages
     *
     * @param string $type 'info' or 'error'
     * @return string[]
     */
    public function getMessages($type)
    {
        if (empty($this->messages[$type])) {
            return null;
        }
        $messages = $this->messages[$type];
        $this->messages[$type] = [];
        return $messages;
    }

    /**
     * Translation function
     *
     * @param string $message
     *  The english string to be translated.
     * @param array $replacements
     *   An associative array of replacements to make after translation. Incidences
     *   of any key in this array are replaced with the corresponding value. Based
     *   on the first character of the key, the value is escaped and/or themed:
     *    - "!variable": inserted as is
     *    - "@variable": escape plain text to HTML
     *    - "%variable": escape text and theme as a placeholder for user-submitted
     *      content
     * @return string Translated string
     * Translated string
     */
    public function t($message, $replacements = array())
    {
        return $message;
    }

    /**
     * Returns the URL to the library admin page
     *
     * @return string
     *   URL to admin page
     */
    public function getAdminUrl()
    {
        // TODO: Implement getAdminUrl() method.
    }

    /**
     * Is H5P in development mode?
     *
     * @return boolean
     *  TRUE if H5P development mode is active
     *  FALSE otherwise
     */
    public function isInDevMode()
    {
        // TODO: Implement isInDevMode() method.
    }

    /**
     * Aggregate the current number of H5P authors
     * @return int
     */
    public function getNumAuthors()
    {
        // TODO: Implement getNumAuthors() method.
        return 0;
    }
}
