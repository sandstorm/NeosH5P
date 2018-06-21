<?php
namespace Sandstorm\NeosH5P\Domain\Model;

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
     * @var Library
     * @ORM\ManyToOne(inversedBy="cachedAssets")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    protected $library;

}
