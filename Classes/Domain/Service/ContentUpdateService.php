<?php

namespace Sandstorm\NeosH5P\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;

/**
 *
 *
 * @Flow\Scope("singleton")
 */
class ContentUpdateService
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
     * Updates already existing content.
     * @see \H5PCore::saveContent()
     *
     * @param int $contentId
     * @param string $title
     * @param string $library
     * @param string $parameters
     * @return null|Content
     */
    public function handleContentUpdate(int $contentId, string $title, string $library, string $parameters)
    {
        /** @var Content $contentBeforeUpdate */
        $contentBeforeUpdate = $this->contentRepository->findOneByContentId($contentId);
        $libraryBeforeUpdate = $contentBeforeUpdate->getLibrary();

        $content = [];

        $content['id'] = $contentId;
        $content['disable'] = \H5PCore::DISABLE_NONE;

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

        $content['title'] = $title;

        $content['params'] = $parameters;

        $params = json_decode($content['params']);
        //TODO: refactor into actual form validation
        if ($params === null) {
            $this->h5pCore->h5pF->setErrorMessage('Invalid parameters.');
            return null;
        }

        $this->h5pCore->saveContent($content, $contentId);

        // The call to filterParameters is done during content editing (before content is loaded into the form) in WP.
        // We do it here to save performance and avoid writes in GET requests. It expects $content['slug'] to exist.
        $content['slug'] = null;
        //TODO: reenable filterParameters, was commented out because it inserts contentdependencies during update which we haven't understood yet
        $this->h5pCore->filterParameters($content);

        // Move images and find all content dependencies
        $this->h5pEditor->processParameters($contentId, $content['library'], $params, $libraryBeforeUpdate->toAssocArray());

        return $this->contentRepository->findOneByContentId($contentId);
    }
}
