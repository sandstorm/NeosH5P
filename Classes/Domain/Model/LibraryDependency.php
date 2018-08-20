<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class LibraryDependency {

    /**
     * @var Library
     * @ORM\Id
     * @ORM\ManyToOne(inversedBy="libraryDependencies")
     */
    protected $library;

    /**
     * @var Library
     * @ORM\Id
     * @ORM\ManyToOne
     */
    protected $requiredLibrary;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $dependencyType;

    public function __construct(Library $library, Library $requiredLibrary, string $dependencyType)
    {
        $this->library = $library;
        $this->requiredLibrary = $requiredLibrary;
        $this->dependencyType = $dependencyType;
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
    public function setLibrary(Library $library)
    {
        $this->library = $library;
    }

    /**
     * @return Library
     */
    public function getRequiredLibrary(): Library
    {
        return $this->requiredLibrary;
    }

    /**
     * @param Library $requiredLibrary
     */
    public function setRequiredLibrary(Library $requiredLibrary)
    {
        $this->requiredLibrary = $requiredLibrary;
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
    public function setDependencyType(string $dependencyType)
    {
        $this->dependencyType = $dependencyType;
    }
}
