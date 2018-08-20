<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class LibraryTranslation {

    public static function create(Library $library, string $languageCode, string $translation) : LibraryTranslation {
        $translationInstance = new LibraryTranslation();
        $translationInstance->setLibrary($library);
        $translationInstance->setLanguageCode($languageCode);
        $translationInstance->setTranslation($translation);
        return $translationInstance;
    }

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
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * @param string $languageCode
     */
    public function setLanguageCode(string $languageCode)
    {
        $this->languageCode = $languageCode;
    }

    /**
     * @return string
     */
    public function getTranslation(): string
    {
        return $this->translation;
    }

    /**
     * @param string $translation
     */
    public function setTranslation(string $translation)
    {
        $this->translation = $translation;
    }


}
