<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\ResourceManagement\PersistentResource;


/**
 * @Flow\Entity
 */
class CachedAsset {

    /**
     * @var PersistentResource
     * @ORM\OneToOne(cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="cascade")
     */
    protected $resource;

    /**
     * @var Collection<Library>
     * @ORM\ManyToMany(mappedBy="cachedAssets")
     */
    protected $libraries;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $hashKey;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $type;

    public function __construct()
    {
        $this->libraries = new ArrayCollection();
    }

    /**
     * @return PersistentResource
     */
    public function getResource(): PersistentResource
    {
        return $this->resource;
    }

    /**
     * @param PersistentResource $resource
     */
    public function setResource(PersistentResource $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * @return Collection
     */
    public function getLibraries(): Collection
    {
        return $this->libraries;
    }

    /**
     * @param Collection $libraries
     */
    public function setLibraries(Collection $libraries): void
    {
        $this->libraries = $libraries;
    }

    /**
     * @param Library $library
     */
    public function addLibrary(Library $library): void
    {
        $this->libraries->add($library);
    }

    /**
     * @return string
     */
    public function getHashKey(): string
    {
        return $this->hashKey;
    }

    /**
     * @param string $hashKey
     */
    public function setHashKey(string $hashKey): void
    {
        $this->hashKey = $hashKey;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
