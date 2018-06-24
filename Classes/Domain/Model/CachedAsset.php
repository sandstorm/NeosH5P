<?php
namespace Sandstorm\NeosH5P\Domain\Model;

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
}
