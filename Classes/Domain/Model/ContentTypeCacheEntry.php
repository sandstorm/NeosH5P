<?php

namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Utility\ObjectAccess;

/**
 * @Flow\Entity
 */
class ContentTypeCacheEntry
{
    /**
     * This is the "Entry ID" we pass to H5P. H5P expects an int here, but we cannot use this as a technical primary
     * key because doctrine doesnt handle it correctly. So this is a unique key.
     *
     * @var int
     * @ORM\Column(nullable=false, columnDefinition="INT AUTO_INCREMENT UNIQUE")
     */
    protected $entryId;

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

    public static function create(\stdClass $contentTypeCacheObject)
    {
        $entry = new ContentTypeCacheEntry();
        $entry->setMachineName($contentTypeCacheObject->id);
        $entry->setMajorVersion($contentTypeCacheObject->version->major);
        $entry->setMinorVersion($contentTypeCacheObject->version->minor);
        $entry->setPatchVersion($contentTypeCacheObject->version->patch);
        $entry->setH5pMajorVersion($contentTypeCacheObject->coreApiVersionNeeded->major);
        $entry->setH5pMinorVersion($contentTypeCacheObject->coreApiVersionNeeded->minor);
        $entry->setTitle($contentTypeCacheObject->title);
        $entry->setSummary($contentTypeCacheObject->summary);
        $entry->setDescription($contentTypeCacheObject->description);
        $entry->setIcon($contentTypeCacheObject->icon);
        $entry->setCreatedAt(new \DateTime($contentTypeCacheObject->createdAt));
        $entry->setUpdatedAt(new \DateTime($contentTypeCacheObject->updatedAt));
        $entry->setIsRecommended($contentTypeCacheObject->isRecommended);
        $entry->setPopularity($contentTypeCacheObject->popularity);
        $entry->setScreenshots(json_encode($contentTypeCacheObject->screenshots));
        $entry->setLicense(json_encode(isset($contentTypeCacheObject->license) ? $contentTypeCacheObject->license : []));
        $entry->setExample($contentTypeCacheObject->example);
        $entry->setTutorial(isset($contentTypeCacheObject->tutorial) ? $contentTypeCacheObject->tutorial : '');
        $entry->setKeywords(json_encode(isset($contentTypeCacheObject->keywords) ? $contentTypeCacheObject->keywords : []));
        $entry->setCategories(json_encode(isset($contentTypeCacheObject->categories) ? $contentTypeCacheObject->categories : []));
        $entry->setOwner($contentTypeCacheObject->owner);
        return $entry;
    }

    /**
     * Returns the library cache entry in a format that H5P expects.
     * @return \stdClass
     */
    public function toStdClass(): \stdClass
    {
        return (object)[
            'id' => $this->getEntryId(),
            'machine_name' => $this->getMachineName(),
            'major_version' => $this->getMajorVersion(),
            'minor_version' => $this->getMinorVersion(),
            'patch_version' => $this->getPatchVersion(),
            'h5p_major_version' => $this->getH5pMajorVersion(),
            'h5p_minor_version' => $this->getH5pMinorVersion(),
            'title' => $this->getTitle(),
            'summary' => $this->getSummary(),
            'description' => $this->getDescription(),
            'icon' => $this->getIcon(),
            'created_at' => $this->getCreatedAt()->getTimestamp(),
            'updated_at' => $this->getUpdatedAt()->getTimestamp(),
            'is_recommended' => $this->isRecommended(),
            'popularity' => $this->getPopularity(),
            'screenshots' => $this->getScreenshots(),
            'license' => $this->getLicense(),
            'owner' => $this->getOwner()
        ];
    }

    /**
     * @return int|null
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @return string
     */
    public function getMachineName(): string
    {
        return $this->machineName;
    }

    /**
     * @param string $machineName
     */
    public function setMachineName(string $machineName)
    {
        $this->machineName = $machineName;
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
     * @return int
     */
    public function getH5pMajorVersion(): int
    {
        return $this->h5pMajorVersion;
    }

    /**
     * @param int $h5pMajorVersion
     */
    public function setH5pMajorVersion(int $h5pMajorVersion)
    {
        $this->h5pMajorVersion = $h5pMajorVersion;
    }

    /**
     * @return int
     */
    public function getH5pMinorVersion(): int
    {
        return $this->h5pMinorVersion;
    }

    /**
     * @param int $h5pMinorVersion
     */
    public function setH5pMinorVersion(int $h5pMinorVersion)
    {
        $this->h5pMinorVersion = $h5pMinorVersion;
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
     * @return string
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     */
    public function setSummary(string $summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon)
    {
        $this->icon = $icon;
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
     * @return bool
     */
    public function isRecommended(): bool
    {
        return $this->isRecommended;
    }

    /**
     * @param bool $isRecommended
     */
    public function setIsRecommended(bool $isRecommended)
    {
        $this->isRecommended = $isRecommended;
    }

    /**
     * @return int
     */
    public function getPopularity(): int
    {
        return $this->popularity;
    }

    /**
     * @param int $popularity
     */
    public function setPopularity(int $popularity)
    {
        $this->popularity = $popularity;
    }

    /**
     * @return string
     */
    public function getScreenshots(): string
    {
        return $this->screenshots;
    }

    /**
     * @param string $screenshots
     */
    public function setScreenshots(string $screenshots)
    {
        $this->screenshots = $screenshots;
    }

    /**
     * @return string
     */
    public function getLicense(): string
    {
        return $this->license;
    }

    /**
     * @param string $license
     */
    public function setLicense(string $license)
    {
        $this->license = $license;
    }

    /**
     * @return string
     */
    public function getExample(): string
    {
        return $this->example;
    }

    /**
     * @param string $example
     */
    public function setExample(string $example)
    {
        $this->example = $example;
    }

    /**
     * @return string
     */
    public function getTutorial(): string
    {
        return $this->tutorial;
    }

    /**
     * @param string $tutorial
     */
    public function setTutorial(string $tutorial)
    {
        $this->tutorial = $tutorial;
    }

    /**
     * @return string
     */
    public function getKeywords(): string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords(string $keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getCategories(): string
    {
        return $this->categories;
    }

    /**
     * @param string $categories
     */
    public function setCategories(string $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner(string $owner)
    {
        $this->owner = $owner;
    }
}
