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
}
