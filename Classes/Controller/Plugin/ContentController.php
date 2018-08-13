<?php

namespace Sandstorm\NeosH5P\Controller\Plugin;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\ResourceManagement\ResourceManager;
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

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\InjectConfiguration(path="xAPI")
     * @var array
     */
    protected $xAPISettings;

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

        $mergedScripts = $this->h5pIntegrationService->getMergedScripts($h5pIntegrationSettings);
        // If xAPI settinga are provided, load the debugging script and integration script too
        if (array_key_exists('debugMode', $this->xAPISettings) && $this->xAPISettings['debugMode']) {
            $debuggingScriptPath = 'resource://Sandstorm.NeosH5P/Public/Scripts/xAPIDebug.js';
            array_push($mergedScripts, $this->resourceManager->getPublicPackageResourceUriByPath($debuggingScriptPath));
        }
        if (array_key_exists('integrationScript', $this->xAPISettings) && file_exists($this->xAPISettings['integrationScript'])) {
            $integrationScriptPath = $this->xAPISettings['integrationScript'];
            array_push($mergedScripts, $this->resourceManager->getPublicPackageResourceUriByPath($integrationScriptPath));
        }

        $this->view->assign('settings', json_encode($h5pIntegrationSettings));
        $this->view->assign('scripts', $mergedScripts);
        $this->view->assign('styles', $this->h5pIntegrationService->getMergedStyles($h5pIntegrationSettings));
        // If xAPI integration settings have been added, inject them.
        if (array_key_exists('integrationSettings', $this->xAPISettings)) {
            $this->view->assign('xAPIIntegrationSettings', json_encode($this->xAPISettings['integrationSettings']));
        }
    }
}
