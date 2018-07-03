<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Service\ContentCreationService;
use Sandstorm\NeosH5P\Domain\Service\ContentUpdateService;
use Sandstorm\NeosH5P\Domain\Service\H5PIntegrationService;

class ContentController extends AbstractModuleController
{
    /**
     * @Flow\InjectConfiguration(path="h5pPublicFolder.url")
     * @var string
     */
    protected $h5pPublicFolderUrl;

    /**
     * @Flow\Inject
     * @var H5PIntegrationService
     */
    protected $h5pIntegrationService;

    /**
     * @Flow\Inject(lazy=false)
     * @var \H5PCore
     */
    protected $h5pCore;

    /**
     * @Flow\Inject
     * @var ContentCreationService
     */
    protected $contentCreationService;

    /**
     * @Flow\Inject
     * @var ContentUpdateService
     */
    protected $contentUpdateService;

    /**
     * @Flow\Inject
     * @var ContentRepository
     */
    protected $contentRepository;

    public function indexAction()
    {
        $contents = $this->contentRepository->findAll();
        $this->view->assign('contents', $contents);
    }

    public function newAction()
    {
        $h5pIntegrationSettings = $this->h5pIntegrationService->getSettings($this->controllerContext, true);

        $this->view->assign('settings', json_encode($h5pIntegrationSettings));
        $this->view->assign('scripts', $h5pIntegrationSettings['core']['scripts']);
        $this->view->assign('styles', $h5pIntegrationSettings['core']['styles']);
    }

    /**
     * @param string $title
     * @param string $action
     * @param string $library
     * @param string $parameters
     * @throws StopActionException
     * @return bool
     */
    public function createAction(string $action, string $title, string $library, string $parameters)
    {
        // We only handle $action == 'create' so far
        if ($action === 'upload') {
            // TODO
        }

        $content = $this->contentCreationService->handleContentCreation($title, $library, $parameters);
        if ($content === null) {
            return false;
        }
        // TODO flashmessage
        $this->redirect('display', null, null, ['content' => $content]);
    }

    /**
     * @param int $contentId
     * @param string $title
     * @param string $library
     * @param string $parameters
     * @return bool
     */
    public function updateAction(int $contentId, string $title, string $library, string $parameters)
    {
        $this->contentUpdateService->handleContentUpdate($contentId, $title, $library, $parameters);
        return false;
    }

    /**
     * @param Content $content
     */
    public function displayAction(Content $content)
    {
        // Whitelist, because filterParams is run on the content.
        // TODO: refactor
        $this->persistenceManager->whitelistObject($content);

        $coreSettings = $this->h5pIntegrationService->getSettings($this->controllerContext);

        /*
        // currently, embed type is hard-set to "div" during content creation. therefore we do not need to reflect the following
        // logic (copied from WP):
        $embedType = \H5PCore::determineEmbedType($content->getEmbedType(), $content->getLibrary()->getEmbedTypes());
        $this->view->assign('embedType', $embedType);
        if ($embedType === 'div') {
            $this->enqueue_assets($files);
        }
        elseif ($embedType === 'iframe') {
            self::$settings['contents'][$cid]['scripts'] = $core->getAssetsUrls($files['scripts']);
            self::$settings['contents'][$cid]['styles'] = $core->getAssetsUrls($files['styles']);
        }*/

        // Make sure content isn't added twice - something like this will be needed when we render multiple content
        // elements on pne page (in fusion)
        $contentId = 'cid-' . $content->getContentId();
        if (!isset($coreSettings['contents'][$contentId])) {
            $coreSettings['contents'][$contentId] = $this->h5pIntegrationService->getContentSettings($this->controllerContext, $content);

            // Get assets for this content
            $preloadedDependencies = $this->h5pCore->loadContentDependencies($content->getContentId(), 'preloaded');
            $files = $this->h5pCore->getDependenciesFiles($preloadedDependencies, $this->h5pPublicFolderUrl);
            $this->view->assign('dependencyScripts', $files['scripts']);
            $this->view->assign('dependencyStyles', $files['styles']);
        }

        $this->view->assign('content', $content);
        $this->view->assign('settings', json_encode($coreSettings));
        $this->view->assign('scripts', $coreSettings['core']['scripts']);
        $this->view->assign('styles', $coreSettings['core']['styles']);
    }

    /**
     * @param Content $content
     */
    public function editAction(Content $content)
    {
        $h5pIntegrationSettings = $this->h5pIntegrationService->getSettings($this->controllerContext, true, $content->getContentId());

        $this->view->assign('settings', json_encode($h5pIntegrationSettings));
        $this->view->assign('scripts', $h5pIntegrationSettings['core']['scripts']);
        $this->view->assign('styles', $h5pIntegrationSettings['core']['styles']);
        $this->view->assign('content', $content);
    }

    /**
     * @param Content $content
     */
    public function deleteAction(Content $content)
    {
        // TODO: implement
        return false;
    }
}
