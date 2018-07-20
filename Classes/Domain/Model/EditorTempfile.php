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
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $resource;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    protected $createdAt;

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
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
