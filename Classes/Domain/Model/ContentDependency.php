<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class H5PContentsLibraries
 * @package Sandstorm\NeosH5P\Domain\Model
 * @Flow\Entity
 */
class ContentDependency {

    /**
     * @var Content
     * @ORM\Id
     * @ORM\ManyToOne(inversedBy="contentDependencies")
     */
    protected $content;

    /**
     * @var Library
     * @ORM\Id
     * @ORM\ManyToOne(inversedBy="contentDependencies")
     */
    protected $library;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(nullable=false)
     */
    protected $dependencyType;

    /**
     * @var int
     * @ORM\Column(nullable=false)
     */
    protected $weight;

    /**
     * @var bool
     * @ORM\Column(nullable=false)
     */
    protected $dropCss;

    /**
     * @return Content
     */
    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * @param Content $content
     */
    public function setContent(Content $content): void
    {
        $this->content = $content;
    }

    /**
     * @return Library
     */
    public function getLibrary(): Library
    {
        return $this->library;
    }

    /**
     * @param Library $library
     */
    public function setLibrary(Library $library): void
    {
        $this->library = $library;
    }

    /**
     * @return string
     */
    public function getDependencyType(): string
    {
        return $this->dependencyType;
    }

    /**
     * @param string $dependencyType
     */
    public function setDependencyType(string $dependencyType): void
    {
        $this->dependencyType = $dependencyType;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return bool
     */
    public function isDropCss(): bool
    {
        return $this->dropCss;
    }

    /**
     * @param bool $dropCss
     */
    public function setDropCss(bool $dropCss): void
    {
        $this->dropCss = $dropCss;
    }
}
