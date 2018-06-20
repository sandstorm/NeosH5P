<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class H5PLibraryDependency
 * @package Sandstorm\NeosH5P\Domain\Model
 * @Flow\Entity
 */
class H5PLibraryDependency {

    /**
     * @var H5PLibrary
     * @ORM\ManyToOne(inversedBy="libraryDependencies")
     */
    protected $library;

    /**
     * @var H5PLibrary
     * @ORM\ManyToOne
     */
    protected $requiredLibrary;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $dependencyType;
}
