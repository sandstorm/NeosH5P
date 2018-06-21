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

}
