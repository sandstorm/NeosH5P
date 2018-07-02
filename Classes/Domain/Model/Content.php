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
     * @var Library
     * @ORM\ManyToOne(inversedBy="contents")
     * @ORM\Column(nullable=false)
     * @ORM\JoinColumn(onDelete="restrict")
     */
    protected $library;

    /**
     * @var Account
     * @ORM\OneToOne
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
     * @param string $title
     * @param Library $library
     * @param string $parameters
     * @return Content
     */
    public static function createFromMetadata(string $title, Library $library, string $parameters): Content
    {
        // Keep track of the old library and params
        $oldLibrary = NULL;
        $oldParams = NULL;
        if ($content !== NULL) {
            $oldLibrary = $content['library'];
            $oldParams = json_decode($content['params']);
        } else {
            $content = array(
                'disable' => \H5PCore::DISABLE_NONE
            );
        }

        $content = new Content();
        $content->setDisable(false);

        // Get library
        $content['library'] = $core->libraryFromString($this->get_input('library'));
        if (!$content['library']) {
            $core->h5pF->setErrorMessage(__('Invalid library.', $this->plugin_slug));
            return FALSE;
        }

        // Check if library exists.
        $content['library']['libraryId'] = $core->h5pF->getLibraryId($content['library']['machineName'], $content['library']['majorVersion'], $content['library']['minorVersion']);
        if (!$content['library']['libraryId']) {
            $core->h5pF->setErrorMessage(__('No such library.', $this->plugin_slug));
            return FALSE;
        }

        // Get title
        $content['title'] = $this->get_input_title();
        if ($content['title'] === NULL) {
            return FALSE;
        }

        // Check parameters
        $content['params'] = $this->get_input('parameters');
        if ($content['params'] === NULL) {
            return FALSE;
        }
        $params = json_decode($content['params']);
        if ($params === NULL) {
            $core->h5pF->setErrorMessage(__('Invalid parameters.', $this->plugin_slug));
            return FALSE;
        }

        // Set disabled features
        $this->get_disabled_content_features($core, $content);

        // Save new content
        $content['id'] = $core->saveContent($content);

        // Move images and find all content dependencies
        $editor = $this->get_h5peditor_instance();
        $editor->processParameters($content['id'], $content['library'], $params, $oldLibrary, $oldParams);
        //$content['params'] = json_encode($params);
        return $content['id'];
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
}
