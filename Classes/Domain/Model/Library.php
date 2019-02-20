<?php

namespace Sandstorm\NeosH5P\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Sandstorm\NeosH5P\Domain\Repository\LibraryDependencyRepository;
use Sandstorm\NeosH5P\Domain\Service\LibraryUpgradeService;
use Sandstorm\NeosH5P\H5PAdapter\Core\H5PFramework;

/**
 * @Flow\Entity
 */
class Library
{
    /**
     * This is the "Library ID" we pass to H5P. H5P expects an int here, but we cannot use this as a technical primary
     * key because doctrine doesnt handle it correctly. So this is a unique key.
     *
     * @var int
     * @ORM\Column(nullable=false, columnDefinition="INT AUTO_INCREMENT UNIQUE")
     */
    protected $libraryId;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    protected $updatedAt;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $title;

    /**
     * @var int
     * @ORM\Column(nullable=false)
     */
    protected $majorVersion;

    /**
     * @var int
     * @ORM\Column(nullable=false)
     */
    protected $minorVersion;

    /**
     * @var int
     * @ORM\Column(nullable=false)
     */
    protected $patchVersion;

    /**
     * @var bool
     * @ORM\Column(nullable=false)
     */
    protected $runnable;

    /**
     * @var bool
     * @ORM\Column(nullable=false)
     */
    protected $restricted;

    /**
     * @var bool
     * @ORM\Column(nullable=false)
     */
    protected $fullscreen;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $embedTypes;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $preloadedJs;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $preloadedCss;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     * This field is apparently not used
     */
    protected $dropLibraryCss;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $semantics;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $tutorialUrl;

    /**
     * @var bool
     * @ORM\Column(nullable=false)
     */
    protected $hasIcon;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $metadataSettings;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $addTo;


    // Inversed relations (not in DB)

    /**
     * @var Collection<Content>
     * @ORM\OneToMany(mappedBy="library", cascade={"persist"})
     */
    protected $contents;

    /**
     * @var Collection<ContentDependency>
     * @ORM\OneToMany(mappedBy="library", cascade={"persist", "remove"})
     */
    protected $contentDependencies;

    /**
     * @var Collection<LibraryDependency>
     * @ORM\OneToMany(mappedBy="library", cascade={"persist", "remove"})
     */
    protected $libraryDependencies;

    /**
     * @var Collection<LibraryTranslation>
     * @ORM\OneToMany(mappedBy="library", cascade={"persist", "remove"})
     */
    protected $libraryTranslations;

    /**
     * @var Collection<CachedAsset>
     * @ORM\ManyToMany(inversedBy="libraries", cascade={"persist"})
     */
    protected $cachedAssets;

    /**
     * @var PersistentResource
     * @ORM\OneToOne(cascade={"persist", "remove"})
     */
    protected $zippedLibraryFile;

    /**
     * @var LibraryUpgradeService
     * @Flow\Inject
     */
    protected $libraryUpgradeService;

    /**
     * @Flow\Inject
     * @var LibraryDependencyRepository
     */
    protected $libraryDependencyRepository;

    /**
     * Creates a library from a metadata array.
     *
     * @param array $libraryData
     * @return Library
     */
    public static function createFromLibraryData(array &$libraryData)
    {
        $libraryData['__preloadedJs'] = self::pathsToCsv($libraryData, 'preloadedJs');
        $libraryData['__preloadedCss'] = self::pathsToCsv($libraryData, 'preloadedCss');

        $libraryData['__dropLibraryCss'] = '0';
        if (isset($libraryData['dropLibraryCss'])) {
            $libs = array();
            foreach ($libraryData['dropLibraryCss'] as $lib) {
                $libs[] = $lib['machineName'];
            }
            $libraryData['__dropLibraryCss'] = implode(', ', $libs);
        }

        $libraryData['__embedTypes'] = '';
        if (isset($libraryData['embedTypes'])) {
            $libraryData['__embedTypes'] = implode(', ', $libraryData['embedTypes']);
        }
        if (!isset($libraryData['semantics'])) {
            $libraryData['semantics'] = '';
        }
        if (!isset($libraryData['hasIcon'])) {
            $libraryData['hasIcon'] = 0;
        }
        if (!isset($libraryData['fullscreen'])) {
            $libraryData['fullscreen'] = 0;
        }

        $library = new Library();
        $library->updateFromLibraryData($libraryData);
        $library->setCreatedAt(new \DateTime());
        $library->setUpdatedAt(new \DateTime());
        $library->setRestricted(false);
        $library->setTutorialUrl('');
        return $library;
    }

    /**
     * @param array $libraryData
     */
    public function updateFromLibraryData(array $libraryData)
    {
        $this->setUpdatedAt(new \DateTime());
        $this->setName($libraryData['machineName']);
        $this->setTitle($libraryData['title']);
        $this->setMajorVersion($libraryData['majorVersion']);
        $this->setMinorVersion($libraryData['minorVersion']);
        $this->setPatchVersion($libraryData['patchVersion']);
        $this->setRunnable($libraryData['runnable']);
        $this->setHasIcon($libraryData['hasIcon'] ? true : false);
        $this->setAddTo(empty($libraryData['addTo']) ? null : json_encode($libraryData['addTo']));
        $this->setMetadataSettings($libraryData['metadataSettings']);
        if (isset($libraryData['semantics'])) {
            $this->setSemantics($libraryData['semantics']);
        }
        if (isset($libraryData['fullscreen'])) {
            $this->setFullscreen($libraryData['fullscreen']);
        }
        if (isset($libraryData['__embedTypes'])) {
            $this->setEmbedTypes($libraryData['__embedTypes']);
            /** @var Content $content */
            foreach ($this->getContents() as $content) {
                /** Embed types might have changed, so we trigger a redetermination */
                $content->determineEmbedType();
            }
        }
        if (isset($libraryData['__preloadedJs'])) {
            $this->setPreloadedJs($libraryData['__preloadedJs']);
        }
        if (isset($libraryData['__preloadedCss'])) {
            $this->setPreloadedCss($libraryData['__preloadedCss']);
        }
        if (isset($libraryData['__dropLibraryCss'])) {
            $this->setDropLibraryCss($libraryData['__dropLibraryCss']);
        }
    }

    /**
     * Returns the library name in a format such as
     * H5P.MultiChoice-1.12
     *
     * @return string
     */
    public function getFolderName(): string
    {
        return \H5PCore::libraryToString($this->toAssocArray(), true);
    }

    /**
     * Returns the library name in a format such as
     * H5P.MultiChoice 1.12
     *
     * @return string
     */
    public function getString(): string
    {
        return \H5PCore::libraryToString($this->toAssocArray(), false);
    }

    /**
     * Convert list of file paths to csv
     *
     * @param array $library
     *  Library data as found in library.json files
     * @param string $key
     *  Key that should be found in $libraryData
     * @return string
     *  file paths separated by ', '
     */
    private static function pathsToCsv($library, $key)
    {
        if (isset($library[$key])) {
            $paths = array();
            foreach ($library[$key] as $file) {
                $paths[] = $file['path'];
            }
            return implode(', ', $paths);
        }
        return '';
    }

    /**
     * Returns an associative array containing the library in the form that
     * H5PFramework->loadLibrary is expected to return.
     * @see H5PFramework::loadLibrary()
     */
    public function toAssocArray(): array
    {
        // the keys majorVersion and major_version are both used within the h5p library classes. Same goes for minor and patch.
        $libraryArray = [
            'id' => $this->getLibraryId(),
            'libraryId' => $this->getLibraryId(),
            'name' => $this->getName(),
            'machineName' => $this->getName(),
            'title' => $this->getTitle(),
            'major_version' => $this->getMajorVersion(),
            'majorVersion' => $this->getMajorVersion(),
            'minor_version' => $this->getMinorVersion(),
            'minorVersion' => $this->getMinorVersion(),
            'patch_version' => $this->getPatchVersion(),
            'patchVersion' => $this->getPatchVersion(),
            'embedTypes' => $this->getEmbedTypes(),
            'preloadedJs' => $this->getPreloadedJs(),
            'preloadedCss' => $this->getPreloadedCss(),
            'dropLibraryCss' => $this->getDropLibraryCss(),
            'fullscreen' => $this->getFullscreen(),
            'runnable' => $this->isRunnable(),
            'semantics' => $this->getSemantics(),
            'hasIcon' => $this->hasIcon()
        ];

        /** @var LibraryDependency $dependency */
        foreach ($this->getLibraryDependencies() as $dependency) {
            $libraryArray[$dependency->getDependencyType() . 'Dependencies'][] = [
                'machineName' => $dependency->getRequiredLibrary()->getName(),
                'majorVersion' => $dependency->getRequiredLibrary()->getMajorVersion(),
                'minorVersion' => $dependency->getRequiredLibrary()->getMinorVersion()
            ];
        }

        return $libraryArray;
    }

    /**
     * Returns this library as a stdClass object in a format that H5P expects
     * when it calls the method:
     * @see \H5peditorStorage::getLibraries()
     * @return \stdClass
     */
    public function toStdClass(): \stdClass
    {
        return (object)$this->toAssocArray();
    }

    public function __construct()
    {
        $this->contents = new ArrayCollection();
        $this->contentDependencies = new ArrayCollection();
        $this->libraryDependencies = new ArrayCollection();
        $this->libraryTranslations = new ArrayCollection();
        $this->cachedAssets = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getVersionString() : string
    {
        return $this->getMajorVersion() . '.' . $this->getMinorVersion() . '.' . $this->getPatchVersion();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getMajorVersion(): int
    {
        return $this->majorVersion;
    }

    /**
     * @param int $majorVersion
     */
    public function setMajorVersion(int $majorVersion)
    {
        $this->majorVersion = $majorVersion;
    }

    /**
     * @return int
     */
    public function getMinorVersion(): int
    {
        return $this->minorVersion;
    }

    /**
     * @param int $minorVersion
     */
    public function setMinorVersion(int $minorVersion)
    {
        $this->minorVersion = $minorVersion;
    }

    /**
     * @return int
     */
    public function getPatchVersion(): int
    {
        return $this->patchVersion;
    }

    /**
     * @param int $patchVersion
     */
    public function setPatchVersion(int $patchVersion)
    {
        $this->patchVersion = $patchVersion;
    }

    /**
     * @return bool
     */
    public function isRunnable(): bool
    {
        return $this->runnable;
    }

    /**
     * @param bool $runnable
     */
    public function setRunnable(bool $runnable)
    {
        $this->runnable = $runnable;
    }

    /**
     * @return bool
     */
    public function isRestricted(): bool
    {
        return $this->restricted;
    }

    /**
     * @param bool $restricted
     */
    public function setRestricted(bool $restricted)
    {
        $this->restricted = $restricted;
    }

    /**
     * @return bool
     */
    public function getFullscreen(): bool
    {
        return $this->fullscreen;
    }

    /**
     * @param bool $fullscreen
     */
    public function setFullscreen(bool $fullscreen)
    {
        $this->fullscreen = $fullscreen;
    }

    /**
     * @return string
     */
    public function getEmbedTypes(): string
    {
        return $this->embedTypes;
    }

    /**
     * @param string $embedTypes
     */
    public function setEmbedTypes(string $embedTypes)
    {
        $this->embedTypes = $embedTypes;
    }

    /**
     * @return string
     */
    public function getPreloadedJs(): string
    {
        return $this->preloadedJs;
    }

    /**
     * @param string $preloadedJs
     */
    public function setPreloadedJs(string $preloadedJs)
    {
        $this->preloadedJs = $preloadedJs;
    }

    /**
     * @return string
     */
    public function getPreloadedCss(): string
    {
        return $this->preloadedCss;
    }

    /**
     * @param string $preloadedCss
     */
    public function setPreloadedCss(string $preloadedCss)
    {
        $this->preloadedCss = $preloadedCss;
    }

    /**
     * @return string
     */
    public function getDropLibraryCss(): string
    {
        return $this->dropLibraryCss;
    }

    /**
     * @param string $dropLibraryCss
     */
    public function setDropLibraryCss(string $dropLibraryCss)
    {
        $this->dropLibraryCss = $dropLibraryCss;
    }

    /**
     * @return string
     */
    public function getSemantics(): string
    {
        return $this->semantics;
    }

    /**
     * @param string $semantics
     */
    public function setSemantics(string $semantics)
    {
        $this->semantics = $semantics;
    }

    /**
     * @return string
     */
    public function getTutorialUrl(): string
    {
        return $this->tutorialUrl;
    }

    /**
     * @param string $tutorialUrl
     */
    public function setTutorialUrl(string $tutorialUrl)
    {
        $this->tutorialUrl = $tutorialUrl;
    }

    /**
     * @return bool
     */
    public function hasIcon(): bool
    {
        return $this->hasIcon;
    }

    /**
     * @param bool $hasIcon
     */
    public function setHasIcon(bool $hasIcon)
    {
        $this->hasIcon = $hasIcon;
    }

    /**
     * @return Collection
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    /**
     * @param Collection $contents
     */
    public function setContents(Collection $contents)
    {
        $this->contents = $contents;
    }

    /**
     * @return Collection
     */
    public function getContentDependencies(): Collection
    {
        return $this->contentDependencies;
    }

    /**
     * @param Collection $contentDependencies
     */
    public function setContentDependencies(Collection $contentDependencies)
    {
        $this->contentDependencies = $contentDependencies;
    }

    /**
     * @return Collection
     */
    public function getLibraryDependencies(): Collection
    {
        return $this->libraryDependencies;
    }

    /**
     * @param Collection $libraryDependencies
     */
    public function setLibraryDependencies(Collection $libraryDependencies)
    {
        $this->libraryDependencies = $libraryDependencies;
    }

    /**
     * @return array
     */
    public function getDependentLibraries(): array
    {
        return $this->libraryDependencyRepository->findByRequiredLibrary($this)->toArray();
    }

    /**
     * @return Collection
     */
    public function getLibraryTranslations(): Collection
    {
        return $this->libraryTranslations;
    }

    /**
     * @param Collection $libraryTranslations
     */
    public function setLibraryTranslations(Collection $libraryTranslations)
    {
        $this->libraryTranslations = $libraryTranslations;
    }

    /**
     * @return Collection
     */
    public function getCachedAssets(): Collection
    {
        return $this->cachedAssets;
    }

    /**
     * @param Collection $cachedAssets
     */
    public function setCachedAssets(Collection $cachedAssets)
    {
        $this->cachedAssets = $cachedAssets;
    }

    /**
     * @param CachedAsset $cachedAsset
     */
    public function addCachedAsset(CachedAsset $cachedAsset)
    {
        $this->cachedAssets->add($cachedAsset);
    }

    /**
     * @return PersistentResource|null
     */
    public function getZippedLibraryFile()
    {
        return $this->zippedLibraryFile;
    }

    /**
     * @param PersistentResource $zippedLibraryFile
     */
    public function setZippedLibraryFile(PersistentResource $zippedLibraryFile)
    {
        $this->zippedLibraryFile = $zippedLibraryFile;
    }

    /**
     * @return int|null
     */
    public function getLibraryId()
    {
        return $this->libraryId;
    }

    /**
     * @return bool
     */
    public function getUpgradeAvailable()
    {
        return $this->libraryUpgradeService->upgradeAvailable($this);
    }

    /**
     * @return string
     */
    public function getMetadataSettings()
    {
        return $this->metadataSettings;
    }

    /**
     * @param string $metadataSettings
     */
    public function setMetadataSettings($metadataSettings): void
    {
        $this->metadataSettings = $metadataSettings;
    }

    /**
     * @return string
     */
    public function getAddTo()
    {
        return $this->addTo;
    }

    /**
     * @param string $addTo
     */
    public function setAddTo($addTo): void
    {
        $this->addTo = $addTo;
    }
}
