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
     * @ORM\ManyToOne
     * @ORM\Column(nullable=false)
     * @ORM\JoinColumn(onDelete="CASCADE")
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

    /**
     * ContentUserData constructor.
     * @param Content $content
     * @param Account $account
     * @param Content $subContent
     * @param string $data
     * @param string $dataId
     * @param bool $preload
     * @param bool $invalidate
     */
    public function __construct($content, $account, $subContent, $data, $dataId, $preload, $invalidate)
    {
        $this->setUpdatedAt(new \DateTime());
        $this->setContent($content);
        $this->setAccount($account);
        $this->setSubContent($subContent);
        $this->setData($data);
        $this->setDataId($dataId);
        $this->setPreload($preload);
        $this->setInvalidate($invalidate);
    }

    /**
     * @return Content
     */
    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * @param Content $content
     */
    public function setContent(Content $content)
    {
        $this->content = $content;
    }

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param Account $account
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
    }

    /**
     * @return string
     */
    public function getDataId(): string
    {
        return $this->dataId;
    }

    /**
     * @param string $dataId
     */
    public function setDataId(string $dataId)
    {
        $this->dataId = $dataId;
    }

    /**
     * @return Content|null
     */
    public function getSubContent()
    {
        return $this->subContent;
    }

    /**
     * @param Content $subContent
     */
    public function setSubContent($subContent)
    {
        $this->subContent = $subContent;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data)
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isPreload(): bool
    {
        return $this->preload;
    }

    /**
     * @param bool $preload
     */
    public function setPreload(bool $preload)
    {
        $this->preload = $preload;
    }

    /**
     * @return bool
     */
    public function isInvalidate(): bool
    {
        return $this->invalidate;
    }

    /**
     * @param bool $invalidate
     */
    public function setInvalidate(bool $invalidate)
    {
        $this->invalidate = $invalidate;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}
