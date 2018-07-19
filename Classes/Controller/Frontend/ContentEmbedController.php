<?php

namespace Sandstorm\NeosH5P\Controller\Frontend;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Service\H5PIntegrationService;

class ContentEmbedController extends ActionController
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

    public function indexAction(int $contentId)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($contentId);
        if ($content === null) {
            return false;
        }
        // When we embed content, we always want "div" as the embed mode
        $content->setEmbedType('div');
        $h5pIntegrationSettings = $this->h5pIntegrationService->getSettings($this->controllerContext, [$content->getContentId()]);
        $this->view->assign('content', $content);
        $this->view->assign('settings', json_encode($h5pIntegrationSettings));
        $this->view->assign('scripts', $this->h5pIntegrationService->getMergedScripts($h5pIntegrationSettings));
        $this->view->assign('styles', $this->h5pIntegrationService->getMergedStyles($h5pIntegrationSettings));
    }
}
