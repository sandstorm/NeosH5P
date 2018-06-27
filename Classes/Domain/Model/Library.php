<?php

namespace Sandstorm\NeosH5P\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Neos\Flow\ResourceManagement\PersistentResource;

/**
 * @Flow\Entity
 */
class Library
{

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


    // Inversed relations (not in DB)

    /**
     * @var Collection<Content>
     * @ORM\OneToMany(mappedBy="library")
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
    protected $zippedLibraryFiles;

    /**
     * Creates a library from a metadata array.
     *
     * @param array $libraryData
     * @return Library
     */
    public static function createFromMetadata(array &$libraryData)
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
        self::updateFromMetadata($libraryData, $library);
        $library->setCreatedAt(new \DateTime());
        $library->setUpdatedAt(new \DateTime());
        $library->setRestricted(false);
        $library->setTutorialUrl('');
        return $library;
    }

    /**
     * @param array $libraryData
     * @param Library $library
     */
    public static function updateFromMetadata(array $libraryData, Library $library)
    {
        $library->setUpdatedAt(new \DateTime());
        $library->setName($libraryData['machineName']);
        $library->setTitle($libraryData['title']);
        $library->setMajorVersion($libraryData['majorVersion']);
        $library->setMinorVersion($libraryData['minorVersion']);
        $library->setPatchVersion($libraryData['patchVersion']);
        $library->setRunnable($libraryData['runnable']);
        $library->setHasIcon($libraryData['hasIcon'] ? true : false);

        if (isset($libraryData['semantics'])) {
            $library->setSemantics($libraryData['semantics']);
        }

        if (isset($libraryData['fullscreen'])) {
            $library->setFullscreen($libraryData['fullscreen']);
        }
        if (isset($libraryData['__embedTypes'])) {
            $library->setEmbedTypes($libraryData['__embedTypes']);
        }
        if (isset($libraryData['__preloadedJs'])) {
            $library->setPreloadedJs($libraryData['__preloadedJs']);
        }
        if (isset($libraryData['__preloadedCss'])) {
            $library->setPreloadedCss($libraryData['__preloadedCss']);
        }
        if (isset($libraryData['__dropLibraryCss'])) {
            $library->setDropLibraryCss($libraryData['__dropLibraryCss']);
        }
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

    public function __construct()
    {
        $this->contents = new ArrayCollection();
        $this->contentDependencies = new ArrayCollection();
        $this->libraryDependencies = new ArrayCollection();
        $this->libraryTranslations = new ArrayCollection();
        $this->cachedAssets = new ArrayCollection();
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
    public function setCreatedAt(\DateTime $createdAt): void
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
    public function setUpdatedAt(\DateTime $updatedAt): void
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
    public function setName(string $name): void
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
    public function setTitle(string $title): void
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
    public function setMajorVersion(int $majorVersion): void
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
    public function setMinorVersion(int $minorVersion): void
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
    public function setPatchVersion(int $patchVersion): void
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
    public function setRunnable(bool $runnable): void
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
    public function setRestricted(bool $restricted): void
    {
        $this->restricted = $restricted;
    }

    /**
     * @return bool
     */
    public function isFullscreen(): bool
    {
        return $this->fullscreen;
    }

    /**
     * @param bool $fullscreen
     */
    public function setFullscreen(bool $fullscreen): void
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
    public function setEmbedTypes(string $embedTypes): void
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
    public function setPreloadedJs(string $preloadedJs): void
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
    public function setPreloadedCss(string $preloadedCss): void
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
    public function setDropLibraryCss(string $dropLibraryCss): void
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
    public function setSemantics(string $semantics): void
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
    public function setTutorialUrl(string $tutorialUrl): void
    {
        $this->tutorialUrl = $tutorialUrl;
    }

    /**
     * @return bool
     */
    public function isHasIcon(): bool
    {
        return $this->hasIcon;
    }

    /**
     * @param bool $hasIcon
     */
    public function setHasIcon(bool $hasIcon): void
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
    public function setContents(Collection $contents): void
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
    public function setContentDependencies(Collection $contentDependencies): void
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
    public function setLibraryDependencies(Collection $libraryDependencies): void
    {
        $this->libraryDependencies = $libraryDependencies;
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
    public function setLibraryTranslations(Collection $libraryTranslations): void
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
    public function setCachedAssets(Collection $cachedAssets): void
    {
        $this->cachedAssets = $cachedAssets;
    }
}
