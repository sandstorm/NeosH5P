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
     * @return Content
     */
    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * @param Content $content
     */
    public function setContent(Content $content): void
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
    public function setAccount(Account $account): void
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
    public function setDataId(string $dataId): void
    {
        $this->dataId = $dataId;
    }

    /**
     * @return Content
     */
    public function getSubContent(): Content
    {
        return $this->subContent;
    }

    /**
     * @param Content $subContent
     */
    public function setSubContent(Content $subContent): void
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
    public function setData(string $data): void
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
    public function setPreload(bool $preload): void
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
    public function setInvalidate(bool $invalidate): void
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
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
