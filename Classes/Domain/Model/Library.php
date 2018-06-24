<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @Flow\Entity
 */
class Library {

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

}
