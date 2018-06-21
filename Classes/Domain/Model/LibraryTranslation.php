<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class LibraryTranslation {

    /**
     * @var Library
     * @ORM\Id
     * @ORM\ManyToOne(inversedBy="libraryTranslations")
     */
    protected $library;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(nullable=false)
     */
    protected $languageCode;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $translation;
}
