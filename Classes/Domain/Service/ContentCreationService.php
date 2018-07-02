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
class ContentCreationService
{
    /**
     * @Flow\Inject(lazy=false)
     * @var \H5PCore
     */
    protected $h5pCore;

    /**
     * @Flow\Inject
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
     * Saves the newly created content.
     * @see \H5PCore::saveContent()
     *
     * @param string $title
     * @param string $library
     * @param string $parameters
     * @return Content|null
     */
    public function handleContentCreation(string $title, string $library, string $parameters)
    {
        $content = [];
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

        // Get title
        $content['title'] = $title;

        // Check parameters
        $content['params'] = $parameters;
        if ($content['params'] === null) {
            return null;
        }
        $params = json_decode($content['params']);
        if ($params === null) {
            $this->h5pCore->h5pF->setErrorMessage('Invalid parameters.');
            return null;
        }

        // Set disabled features
        // TODO: actually make the frame, embed, download etc... configurable.
        // for now, we just disable the entire frame.
        // $this->get_disabled_content_features($this->h5pCore, $content);
        $content['disable'] = \H5PCore::DISABLE_FRAME;

        // Save new content
        $content['id'] = $this->h5pCore->saveContent($content);

        // The call to filterParameters is done during content editing (before content is loaded into the form) in WP.
        // We do it here to save performance and avoid writes in GET requests. It expects $content['slug'] to exist.
        $content['slug'] = null;
        $this->h5pCore->filterParameters($content);

        // Move images and find all content dependencies
        $this->h5pEditor->processParameters($content['id'], $content['library'], $params);
        return $this->contentRepository->findByIdentifier($content['id']);
    }
}
