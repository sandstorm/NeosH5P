<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Error\Messages\Message;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Model\ContentResult;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\ContentResultRepository;
use Sandstorm\NeosH5P\Domain\Service\CRUD\ContentCRUDService;
use Sandstorm\NeosH5P\Domain\Service\H5PIntegrationService;

class ContentController extends AbstractModuleController
{
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
     * @Flow\Inject
     * @var ContentResultRepository
     */
    protected $contentResultRepository;

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
        $view->getTemplatePaths()->setPartialRootPaths(array_merge(
            ['resource://Neos.Neos/Private/Partials', 'resource://Neos.Neos/Private/Partials'],
            $view->getTemplatePaths()->getPartialRootPaths()
        ));
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
        $h5pIntegrationSettings = $this->h5pIntegrationService->getSettings($this->controllerContext, [$content->getContentId()]);
        $this->view->assign('content', $content);
        $this->view->assign('settings', json_encode($h5pIntegrationSettings));
        $this->view->assign('scripts', $this->h5pIntegrationService->getMergedScripts($h5pIntegrationSettings));
        $this->view->assign('styles', $this->h5pIntegrationService->getMergedStyles($h5pIntegrationSettings));
    }

    /**
     * @param Content $content
     */
    public function resultsAction(Content $content)
    {
        $this->view->assign('content', $content);
        $this->view->assign('contentResults', $this->contentResultRepository->findByContent($content));
        $this->view->assign('perUser', true);
    }

    /**
     * @param ContentResult $contentResult
     */
    public function deleteSingleResultAction(ContentResult $contentResult)
    {
        $this->contentResultRepository->remove($contentResult);
        $this->addFlashMessage('The content result has been deleted.', 'Result deleted', Message::SEVERITY_OK);
        $this->redirect('display', null, null, ['content' => $contentResult->getContent()]);
    }

    /**
     * @param Content $content
     */
    public function deleteResultsAction(Content $content)
    {
        foreach ($content->getContentResults() as $contentResult) {
            $this->contentResultRepository->remove($contentResult);
        }
        $this->addFlashMessage('All results for content "%s" have been deleted.', 'Results deleted', Message::SEVERITY_OK, [$content->getTitle()]);
        $this->redirect('display', null, null, ['content' => $content]);
    }

    public function newAction()
    {
        $h5pIntegrationSettings = $this->h5pIntegrationService->getSettingsWithEditor($this->controllerContext);

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
        $h5pIntegrationSettings = $this->h5pIntegrationService->getSettingsWithEditor($this->controllerContext, $content->getContentId());

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
