<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Error\Messages\Message;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Security\Account;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Sandstorm\NeosH5P\Domain\Model\ContentResult;
use Sandstorm\NeosH5P\Domain\Repository\ContentResultRepository;
use Neos\Flow\Annotations as Flow;

class ContentResultsController extends AbstractModuleController
{
    /**
     * @var ContentResultRepository
     * @Flow\Inject
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
        // Retrieve all results grouped by user
        $this->view->assign('contentResults', $this->contentResultRepository->findAllGroupedByAccount());
    }

    public function displayAction(Account $account)
    {
        $this->view->assign('contentResults', $this->contentResultRepository->findByAccount($account));
        $this->view->assign('perContent', true);
    }

    public function deleteSingleResultAction(ContentResult $contentResult)
    {
        $this->contentResultRepository->remove($contentResult);
        $this->addFlashMessage('The result has been deleted.', 'Result deleted', Message::SEVERITY_OK);
        $this->redirect('display', null, null, ['account' => $contentResult->getAccount()]);
    }

    public function deleteAllAction(Account $account)
    {
        $contentResults = $this->contentResultRepository->findByAccount($account);
        foreach ($contentResults as $contentResult) {
            $this->contentResultRepository->remove($contentResult);
        }
        $this->addFlashMessage('All results for user "%s" have been deleted.', 'Results deleted', Message::SEVERITY_OK, [$account->getAccountIdentifier()]);
        $this->redirect('index');
    }
}
