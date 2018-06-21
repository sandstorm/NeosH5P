<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\ResourceManagement\PersistentResource;

/**
 * @Flow\Entity
 */
class EditorTempfile {

    /**
     * @var PersistentResource
     * @ORM\OneToOne
     * @ORM\Column(nullable=false)
     * @ORM\JoinColumn(onDelete="cascade")
     */
    protected $resource;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    protected $createdAt;

}
