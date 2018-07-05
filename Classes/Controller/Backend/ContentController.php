<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Error\Messages\Message;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Service\ContentCRUDService;
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
     * @var ContentCRUDService
     */
    protected $contentCRUDService;

    /**
     * @Flow\Inject
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * We add the Neos default partials and layouts here, so we can use them
     * in our backend modules
     *
     * @param ViewInterface $view
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
        $view->getTemplatePaths()->setLayoutRootPath('resource://Neos.Neos/Private/Layouts');
        $view->getTemplatePaths()->setPartialRootPath('resource://Neos.Neos/Private/Partials');
    }

    public function indexAction()
    {
        $contents = $this->contentRepository->findAll();
        $this->view->assign('contents', $contents);
    }

    /**
     * @param Content $content
     */
    public function displayAction(Content $content)
    {
        $h5pIntegrationSettings = $this->h5pIntegrationService->getSettings($this->controllerContext);

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
        if (!isset($h5pIntegrationSettings['contents'][$contentId])) {
            $h5pIntegrationSettings['contents'][$contentId] = $this->h5pIntegrationService->getContentSettings($this->controllerContext, $content);

            // Get assets for this content
            $preloadedDependencies = $this->h5pCore->loadContentDependencies($content->getContentId(), 'preloaded');
            $files = $this->h5pCore->getDependenciesFiles($preloadedDependencies, $this->h5pPublicFolderUrl);
            $this->view->assign('dependencyScripts', $files['scripts']);
            $this->view->assign('dependencyStyles', $files['styles']);
        }

        $this->view->assign('content', $content);
        $this->view->assign('settings', json_encode($h5pIntegrationSettings));
        $this->view->assign('scripts', $h5pIntegrationSettings['core']['scripts']);
        $this->view->assign('styles', $h5pIntegrationSettings['core']['styles']);
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
     */
    public function createAction(string $action, string $title, string $library, string $parameters)
    {
        // We only handle $action == 'create' so far
        if ($action === 'upload') {
            // TODO
        }

        $content = $this->contentCRUDService->handleCreateOrUpdate($title, $library, $parameters);
        if ($content === null) {
            $this->showH5pErrorMessages();
            $this->redirect('index');
        } else {
            $this->addFlashMessage('The content "%s" has been created.', 'Content created', Message::SEVERITY_OK, [$content->getTitle()]);
            $this->redirect('display', null, null, ['content' => $content]);
        }
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
     * @param int $contentId
     * @param string $title
     * @param string $library
     * @param string $parameters
     * @throws StopActionException
     * @return bool
     */
    public function updateAction(int $contentId, string $title, string $library, string $parameters)
    {
        $content = $this->contentCRUDService->handleCreateOrUpdate($title, $library, $parameters, $contentId);
        if ($content === null) {
            $this->showH5pErrorMessages();
            $this->redirect('index');
        } else {
            $this->addFlashMessage('The content "%s" has been updated.', 'Content updated', Message::SEVERITY_OK, [$content->getTitle()]);
            $this->redirect('display', null, null, ['content' => $content]);
        }

        return false;
    }

    /**
     * @param Content $content
     * @throws StopActionException
     * @return bool
     */
    public function deleteAction(Content $content)
    {
        $this->contentCRUDService->handleDelete($content);

        $this->addFlashMessage('The content "%s" has been deleted.', 'Content deleted', Message::SEVERITY_OK, [$content->getTitle()]);
        $this->redirect('index', null, null);
        return false;
    }

    private function showH5pErrorMessages()
    {
        foreach ($this->h5pCore->h5pF->getMessages('error') as $errorMessage) {
            $this->addFlashMessage($errorMessage->message, $errorMessage->code ?: 'H5P error', Message::SEVERITY_ERROR);
        }
    }
}
