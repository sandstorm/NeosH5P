<?php

namespace Sandstorm\NeosH5P\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Context;
use Sandstorm\NeosH5P\Domain\Model\ContentUserData;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\ContentUserDataRepository;
use Sandstorm\NeosH5P\H5PAdapter\Core\H5PFramework;

/**
 * @Flow\Scope("singleton")
 */
class ContentUserDataCRUDService
{
    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var ContentUserDataRepository
     */
    protected $contentUserDataRepository;

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
     * Adds a new ContentUserDatas or updates the already existing one for this user and content.
     *
     * @param int $contentId
     * @param string $dataType
     * @param int $subContentId
     * @param string $data
     * @param bool $preload
     * @param bool $invalidate
     * @return array ['success' => boolean, 'message' => string]
     */
    public function handleCreateOrUpdate($contentId, $dataType, $subContentId, $data, $preload, $invalidate)
    {
        // Check if user tracking is allowed
        if (!$this->h5pFramework->getOption('save_content_state', false)) {
            return $this->makeUserData(false, 'UserData will not be saved as save_content_state is set to false.');
        }

        $currentAccount = $this->securityContext->getAccount();
        if ($currentAccount !== null) {
            $content = $this->contentRepository->findOneByContentId($contentId);
            $subContent = $this->contentRepository->findOneByContentId($subContentId);
            if ($content !== null) {
                /** @var ContentUserData $existingContentUserData */
                $existingContentUserData = $this->contentUserDataRepository->findOneByContentAccountAndDataId($content, $currentAccount, $dataType);
                if ($existingContentUserData !== null) {
                    $existingContentUserData->setUpdatedAt(new \DateTime());
                    $existingContentUserData->setDataId($dataType);
                    $existingContentUserData->setSubContent($subContent);
                    $existingContentUserData->setData($data);
                    $existingContentUserData->setPreload($preload);
                    $existingContentUserData->setInvalidate($invalidate);
                    $this->contentUserDataRepository->update($existingContentUserData);
                } else {
                    $contentUserData = new ContentUserData($content, $currentAccount, $subContent, $data, $dataType, $preload, $invalidate);
                    $this->contentUserDataRepository->add($contentUserData);
                }
                // Persist here, because exit() might be used after controller action
                $this->persistenceManager->persistAll();
                return $this->makeUserData(true);
            }
            return $this->makeUserData(false, 'UserData can\'t be saved as no content with id "' . $contentId . ' exists."');
        }
        return $this->makeUserData(false, 'UserData could not be saved as no account was authenticated. Have you added the requestPattern for the H5P frontend controllers (see integration guide)?');
    }

    private function makeUserData(bool $success, string $message = '')
    {
        return [
            'success' => $success,
            'message' => $message
        ];
    }
}
