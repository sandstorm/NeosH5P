<?php

namespace Sandstorm\NeosH5P\Domain\Service\CRUD;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Context;
use Sandstorm\NeosH5P\Domain\Model\ContentResult;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\ContentResultRepository;
use Sandstorm\NeosH5P\H5PAdapter\Core\H5PFramework;

/**
 *
 *
 * @Flow\Scope("singleton")
 */
class ContentResultsCRUDService
{
    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var ContentResultRepository
     */
    protected $contentResultRepository;

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
     * @Flow\Inject
     * @var H5PFramework
     */
    protected $h5pFramework;

    /**
     * Adds a new ContentResults or updates the already existing one for this user and content.
     *
     * @param int $contentId
     * @param int $score
     * @param int $maxScore
     * @param int $opened
     * @param int $finished
     * @param int $time
     * @return array ['success' => boolean, 'message' => string]
     */
    public function handleCreateOrUpdate($contentId, $score, $maxScore, $opened, $finished, $time)
    {
        // Check if user tracking is allowed
        if (!$this->h5pFramework->getOption('track_user', false)) {
            return $this->makeResult(false, 'Result will not be saved as track_user is set to false.');
        }

        $currentAccount = $this->securityContext->getAccount();
        if ($currentAccount !== null) {
            $content = $this->contentRepository->findOneByContentId($contentId);
            if ($content !== null) {
                /** @var ContentResult $existingContentResult */
                $existingContentResult = $this->contentResultRepository->findOneByContentAndAccount($content, $currentAccount);
                if ($existingContentResult !== null) {
                    $existingContentResult->setScore($score);
                    $existingContentResult->setMaxScore($maxScore);
                    $existingContentResult->setOpened($opened);
                    $existingContentResult->setFinished($finished);
                    $existingContentResult->setTime($time);
                    $this->contentResultRepository->update($existingContentResult);
                } else {
                    $contentResult = new ContentResult($content, $currentAccount, $score, $maxScore, $opened, $finished, $time);
                    $this->contentResultRepository->add($contentResult);
                }
                // Persist here, because exit() might be used after controller action
                $this->persistenceManager->persistAll();
                return $this->makeResult(true);
            }
            return $this->makeResult(false, 'Result can\'t be saved as no content with id "' . $contentId . ' exists."');
        }
        return $this->makeResult(false, 'Result could not be saved as no account was authenticated. Have you added the requestPattern for the H5P frontend controllers (see integration guide)?');
    }

    private function makeResult(bool $success, string $message = '')
    {
        return [
            'success' => $success,
            'message' => $message
        ];
    }
}
