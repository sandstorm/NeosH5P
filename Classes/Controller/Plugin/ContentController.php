<?php

namespace Sandstorm\NeosH5P\Controller\Plugin;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Service\H5PIntegrationService;

class ContentController extends ActionController
{
    /**
     * @Flow\Inject
     * @var H5PIntegrationService
     */
    protected $h5pIntegrationService;

    /**
     * @Flow\Inject
     * @var ContentRepository
     */
    protected $contentRepository;

    public function contentAction()
    {
        /** @var NodeInterface $node */
        $node = $this->request->getInternalArgument('__node');
        $content = $this->contentRepository->findOneByContentId($node->getProperty('contentId'));
        $this->view->assign('content', $content);
    }

    public function scriptsAndStylesAction()
    {
        /** @var array $h5pContentNodes */
        $h5pContentNodes = $this->request->getInternalArgument('__h5pContentNodes');
        $contentIds = [];
        /** @var NodeInterface $node */
        foreach ($h5pContentNodes as $node) {
            $contentId = $node->getProperty('contentId');
            if ($contentId) {
                $contentIds[] = $contentId;
            }
        }

        if (count($contentIds) === 0) {
            return false;
        }

        $h5pIntegrationSettings = $this->h5pIntegrationService->getSettings($this->controllerContext, $contentIds);

        $this->view->assign('settings', json_encode($h5pIntegrationSettings));
        $this->view->assign('scripts', $this->h5pIntegrationService->getMergedScripts($h5pIntegrationSettings));
        $this->view->assign('styles', $this->h5pIntegrationService->getMergedStyles($h5pIntegrationSettings));
    }
}
