<?php

namespace Sandstorm\NeosH5P\Domain\Service\CRUD;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Model\Library;
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
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

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
        $oldLibrary = null;
        $oldParameters = null;

        // Before we save the new data, load the old data from the DB.
        if ($contentId) {
            $content['id'] = $contentId;
            $contentObject = $this->contentRepository->findOneByContentId($content['id']);

            if ($contentObject !== null) {
                $oldLibrary = $contentObject->getLibrary()->toAssocArray();
                $oldParameters = json_decode($contentObject->getParameters());
            }
        }
        $content['disable'] = \H5PCore::DISABLE_NONE;
        $content['title'] = $title;
        $content['params'] = $parameters;

        // Get library
        $content['library'] = $this->h5pCore->libraryFromString($library);
        if (! $content['library']) {
            $this->h5pCore->h5pF->setErrorMessage('Invalid library.');
            return null;
        }

        // Check if library exists.
        $content['library']['libraryId'] = $this->h5pCore->h5pF->getLibraryId($content['library']['machineName'],
            $content['library']['majorVersion'], $content['library']['minorVersion']);
        if (! $content['library']['libraryId']) {
            $this->h5pCore->h5pF->setErrorMessage('No such library.');
            return null;
        }

        // Check if parameters are valid JSON.
        $params = json_decode($content['params']);
        if ($params === null) {
            $this->h5pCore->h5pF->setErrorMessage('Invalid parameters.');
            return null;
        }

        // Since H5P core now upgrades content on save, which can throw an exception, we need to be able to handle it.
        try {
            $content['id'] = $this->h5pCore->saveContent($content);
        } catch (\Exception $e) {
            $this->h5pCore->h5pF->setErrorMessage($e->getMessage());
            return null;
        }

        // Move images and find all content dependencies
        $this->h5pEditor->processParameters($content['id'], $content['library'], $params->params, $oldLibrary, $oldParameters);

        // Re-Import the content files as a zipfile for the content element.
        /** @var Content $contentObject */
        $contentObject = $this->contentRepository->findOneByContentId($content['id']);
        $contentObject->createZippedContentFileFromTemporaryDirectory();

        /**
         * The call to filterParameters is done during content editing (before content is loaded into the form) in WP.
         * We do it here to save performance and avoid writes in GET requests. It expects the full content array to
         * be populated.
         */
        $contentArray = $this->generateFilteredParametersAndContentFile($contentObject);

        return $this->contentRepository->findOneByContentId($contentArray['id']);
    }

    /**
     * Handles the upgrade process of a content element.
     *
     * @param string $contentId
     * @param string $migratedParams
     * @param Library $targetLibrary
     * @return null|Content
     */
    public function handleUpgrade($contentId, $migratedParams, Library $targetLibrary)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($contentId);

        $contentData = json_decode($migratedParams, true);
        $content->updateFromContentData($contentData, $targetLibrary);

        $this->contentRepository->update($content);
        $this->generateFilteredParametersAndContentFile($content);
        return $content;
    }

    /**
     * @param Content $content
     * @return array
     */
    private function generateFilteredParametersAndContentFile(Content $content) {
        $contentArray = $content->toAssocArray();
        // If "slug" is not empty, filterParameters thinks that we don't have to delete old exports, so make sure
        // it's empty so it gets repopulated and old exports get removed. Crazy...
        $contentArray['slug'] = '';
        $this->h5pCore->filterParameters($contentArray);

        // Persist again as the zippedresource object, export file etc. may have changed
        $this->contentRepository->update($content);

        // If there is a zipped content file now, publish it.
        if ($content->getZippedContentFile() !== null) {
            $collection = $this->resourceManager->getCollection('h5p-content');

            // PublishResource does not work as apparently a different logic is used, so we publish the whole
            // collection here for now
            // $collection->getTarget()->publishResource($contentObject->getZippedContentFile(), $collection);
            $collection->getTarget()->publishCollection($collection);
        }

        return $contentArray;
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
