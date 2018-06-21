<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Security\Account;

/**
 * @Flow\Entity
 */
class ContentUserData {

    /**
     * @var Content
     * @ORM\Id
     * @ORM\ManyToOne(inversedBy="contentUserDatas")
     */
    protected $content;

    /**
     * @var Account
     * @ORM\Id
     * @ORM\OneToOne
     */
    protected $account;

    /**
     * @var string
     * @ORM\Id
     * @see https://h5p.org/node/74904
     */
    protected $dataId;

    /**
     * @var Content
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     * Should be part of the unique key but it has to be nullable which is impossible when the DB is managed by doctrine
     */
    protected $subContent;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $data;

    /**
     * @var bool
     * @ORM\Column(nullable=false)
     */
    protected $preload;

    /**
     * @var bool
     * @ORM\Column(nullable=false)
     */
    protected $invalidate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    protected $updatedAt;

}
