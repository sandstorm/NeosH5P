<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/* TODO: add KEY nameversion (machinename,majorversion,minorversion,patchversion) from the original SQL to migration manually */

/**
 * Class H5PLibrariesHubCache
 * @package Sandstorm\NeosH5P\Domain\Model
 * @Flow\Entity
 */
class HubCachedLibrary {

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $machineName;

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
     * @var int
     */
    protected $h5pMajorVersion;

    /**
     * @var int
     */
    protected $h5pMinorVersion;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $summary;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $icon;

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
     * @var bool
     * @ORM\Column(nullable=false)
     */
    protected $isRecommended;

    /**
     * @var int
     * @ORM\Column(nullable=false)
     */
    protected $popularity;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $screenshots;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $license;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $example;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $tutorial;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $keywords;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $categories;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $owner;

}
