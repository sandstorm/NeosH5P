<?php

namespace Sandstorm\NeosH5P\DataSource;

use Neos\ContentRepository\Domain\Model\NodeInterface;
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
     * @param NodeInterface|null $node
     * @param array $arguments
     * @return array
     */
    public function getData(NodeInterface $node = null, array $arguments)
    {
        $contents = $this->contentRepository->findAll()->toArray();
        return array_map(function ($content) {
            /** @var Content $content */
            return [
                'value' => $content->getContentId(),
                'label' => $content->getTitle(),
                'group' => $content->getLibrary()->getTitle()
            ];
        }, $contents);


        $formDefinitions['']['label'] = '';
        $forms = $this->yamlPersistenceManager->listForms();

        foreach ($forms as $form) {
            $formDefinitions[$form['identifier']]['label'] = $form['name'];
        }

        return $formDefinitions;
    }
}
