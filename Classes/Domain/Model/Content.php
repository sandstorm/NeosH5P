<?php

namespace Sandstorm\NeosH5P\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Neos\Flow\Security\Account;

/**
 * @Flow\Entity
 */
class Content
{
    /**
     * This is the "Content ID" we pass to H5P. H5P expects an int here, but we cannot use this as a technical primary
     * key because doctrine doesnt handle it correctly. So this is a unique key.
     *
     * @var int
     * @ORM\Column(nullable=false, columnDefinition="INT AUTO_INCREMENT UNIQUE")
     */
    protected $contentId;

    /**
     * @var Library
     * @ORM\ManyToOne(inversedBy="contents")
     * @ORM\Column(nullable=false)
     * @ORM\JoinColumn(onDelete="restrict")
     */
    protected $library;

    /**
     * @var Account
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
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
     * @var int
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

    /**
     * Creates a Content from a metadata array.
     *
     * @param array $contentData
     * @param Library $library
     * @param Account $account
     * @return Content
     */
    public static function createFromMetadata(array $contentData, Library $library, Account $account): Content
    {
        $content = new Content();
        $content->setLibrary($library);
        $content->setAccount($account);
        $content->setCreatedAt(new \DateTime());
        $content->setUpdatedAt(new \DateTime());
        $content->setTitle($contentData['title']);
        $content->setParameters($contentData['params']);
        $content->setDisable($contentData['disable']);
        $content->setSlug(''); // Set by h5p later, but must not be null
        $content->setEmbedType('div');
        $content->setFiltered('');

        return $content;
    }

    /**
     * Returns an associative array containing the content in the form that
     * \H5PCore->filterParameters() expects.
     * @see H5PCore::filterParameters()
     */
    public function toAssocArray(): array
    {
        $contentArray = [
            'id' => $this->getContentId(),
            'title' => $this->getTitle(),
            'library' => $this->getLibrary()->toAssocArray(),
            'slug' => $this->getSlug(),
            'disable' => $this->getDisable(),
            'embedType' => $this->getEmbedType(),
            'params' => $this->getParameters(),
            'filtered' => $this->getFiltered()
        ];

        return $contentArray;
    }

    public function __construct()
    {
        $this->contentDependencies = new ArrayCollection();
        $this->contentUserDatas = new ArrayCollection();
    }

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
    public function setLibrary(Library $library): void
    {
        $this->library = $library;
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

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getParameters(): string
    {
        return $this->parameters;
    }

    /**
     * @param string $parameters
     */
    public function setParameters(string $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getFiltered(): string
    {
        return $this->filtered;
    }

    /**
     * @param string $filtered
     */
    public function setFiltered(string $filtered): void
    {
        $this->filtered = $filtered;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getEmbedType(): string
    {
        return $this->embedType;
    }

    /**
     * @param string $embedType
     */
    public function setEmbedType(string $embedType): void
    {
        $this->embedType = $embedType;
    }

    /**
     * @return int
     */
    public function getDisable(): int
    {
        return $this->disable;
    }

    /**
     * @param int $disable
     */
    public function setDisable(int $disable): void
    {
        $this->disable = $disable;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     */
    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getLicense(): string
    {
        return $this->license;
    }

    /**
     * @param string $license
     */
    public function setLicense(string $license): void
    {
        $this->license = $license;
    }

    /**
     * @return string
     */
    public function getKeywords(): string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords(string $keywords): void
    {
        $this->keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Collection
     */
    public function getContentDependencies(): Collection
    {
        return $this->contentDependencies;
    }

    /**
     * @param Collection $contentDependencies
     */
    public function setContentDependencies(Collection $contentDependencies): void
    {
        $this->contentDependencies = $contentDependencies;
    }

    /**
     * @return Collection
     */
    public function getContentUserDatas(): Collection
    {
        return $this->contentUserDatas;
    }

    /**
     * @param Collection $contentUserDatas
     */
    public function setContentUserDatas(Collection $contentUserDatas): void
    {
        $this->contentUserDatas = $contentUserDatas;
    }

    /**
     * @return int
     */
    public function getContentId(): int
    {
        return $this->contentId;
    }

    /**
     * @param int $contentId
     */
    public function setContentId(int $contentId): void
    {
        $this->contentId = $contentId;
    }
}
