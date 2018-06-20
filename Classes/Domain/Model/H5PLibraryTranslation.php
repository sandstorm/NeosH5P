<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class H5PLibraryTranslation
 * @package Sandstorm\NeosH5P\Domain\Model
 * @Flow\Entity
 */
class H5PLibraryTranslation {

    /**
     * @var H5PLibrary
     * @ORM\ManyToOne(inversedBy="libraryTranslations")
     */
    protected $library;

    /**
     * @var string
     */
    protected $languageCode;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $translation;
}
