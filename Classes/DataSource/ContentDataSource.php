<?php

namespace Sandstorm\NeosH5P\DataSource;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;

class ContentDataSource extends AbstractDataSource
{
    /**
     * @var string
     */
    protected static $identifier = 'sandstorm-neosh5p-content';

    /**
     * @Flow\Inject
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @param NodeInterface|null $node
     * @param array $arguments
     * @return array
     */
    public function getData(NodeInterface $node = null, array $arguments)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($arguments['contentId']);

        return [
            'persistenceObjectIdentifier' => $this->persistenceManager->getIdentifierByObject($content),
            'contentId' => $content->getContentId(),
            'contentTitle' => $content->getTitle(),
            'libraryTitle' => $content->getLibrary()->getTitle()
        ];
    }
}
