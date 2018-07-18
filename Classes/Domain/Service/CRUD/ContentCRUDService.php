<?php

namespace Sandstorm\NeosH5P\Domain\Service\CRUD;

use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;

/**
 *
 *
 * @Flow\Scope("singleton")
 */
class ContentCRUDService
{
    /**
     * @Flow\Inject(lazy=false)
     * @var \H5PCore
     */
    protected $h5pCore;

    /**
     * @Flow\Inject(lazy=false)
     * @var \H5peditor
     */
    protected $h5pEditor;

    /**
     * @Flow\Inject(lazy=false)
     * @var \H5PStorage
     */
    protected $h5pStorage;

    /**
     * @Flow\Inject
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * @Flow\Inject
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * Creates the content data structure that H5P expects and passes it into its API.
     * If a $contentId is provided, will try to find and update that content. If there
     * is no content with that ID, it will be created.
     *
     * @see \H5PCore::saveContent()
     *
     * @param string $title
     * @param string $library
     * @param string $parameters
     * @param int $contentId
     * @return null|Content
     */
    public function handleCreateOrUpdate(string $title, string $library, string $parameters, $contentId = null)
    {
        $content = [];
        if($contentId) {
            $content['id'] = $contentId;
        }
        // TODO: actually make the frame, embed, download etc... configurable.
        $content['disable'] = \H5PCore::DISABLE_FRAME;
        $content['title'] = $title;
        $content['params'] = $parameters;
        $content['slug'] = '';

        // Get library
        $content['library'] = $this->h5pCore->libraryFromString($library);
        if (!$content['library']) {
            $this->h5pCore->h5pF->setErrorMessage('Invalid library.');
            return null;
        }

        // Check if library exists.
        $content['library']['libraryId'] = $this->h5pCore->h5pF->getLibraryId($content['library']['machineName'], $content['library']['majorVersion'], $content['library']['minorVersion']);
        if (!$content['library']['libraryId']) {
            $this->h5pCore->h5pF->setErrorMessage('No such library.');
            return null;
        }

        // Check if parameters are valid JSON.
        $params = json_decode($content['params']);
        if ($params === null) {
            $this->h5pCore->h5pF->setErrorMessage('Invalid parameters.');
            return null;
        }

        $content['id'] = $this->h5pCore->saveContent($content);

        // The call to filterParameters is done during content editing (before content is loaded into the form) in WP.
        // We do it here to save performance and avoid writes in GET requests. It expects $content['slug'] to exist.
        $this->h5pCore->filterParameters($content);

        $oldLibrary = null;
        $oldParameters = null;
        /** @var Content $existingContent */
        $existingContent = $this->contentRepository->findOneByContentId($content['id']);
        if ($existingContent !== null) {
            $oldLibrary = $existingContent->getLibrary()->toAssocArray();
            $oldParameters = $existingContent->getParameters();
        }
        // Move images and find all content dependencies
        $this->h5pEditor->processParameters($content['id'], $content['library'], $params, $oldLibrary, $oldParameters);

        return $this->contentRepository->findOneByContentId($content['id']);
    }

    /**
     * Deletes already existing content.
     *
     * @param Content $content
     */
    public function handleDelete(Content $content)
    {
        $this->h5pStorage->deletePackage($content->toAssocArray());
    }

}
