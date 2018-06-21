<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Neos\Flow\Security\Account;

/**
 * @Flow\Entity
 */
class Content {

    /**
     * @var Library
     * @ORM\ManyToOne(inversedBy="contents")
     * @ORM\Column(nullable=false)
     * @ORM\JoinColumn(onDelete="restrict")
     */
    protected $library;

    /**
     * @var Account
     * @ORM\OneToOne
     * @ORM\Column(nullable=false)
     */
    protected $account;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    protected $updatedAt;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $parameters;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $filtered;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $embedType;

    /**
     * @var bool
     * @ORM\Column(nullable=false)
     */
    protected $disable;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $contentType;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $author;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $license;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $keywords;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;


    // Inversed relations (not in DB)

    /**
     * @var Collection<ContentDependency>
     * @ORM\OneToMany(mappedBy="content", cascade={"persist", "remove"})
     */
    protected $contentDependencies;

    /**
     * @var Collection<ContentUserData>
     * @ORM\OneToMany(mappedBy="content", cascade={"persist", "remove"})
     */
    protected $contentUserDatas;

}
