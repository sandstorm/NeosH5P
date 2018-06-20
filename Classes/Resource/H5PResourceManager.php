<?php

namespace Sandstorm\NeosH5P\Resource;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;


/**
 * The idea of exposing H5P resources is the following:
 *
 * The H5P file itself is a normal Persistent Resource in the Flow framework; which is attached to the Library
 * object.
 *
 * The ExtractedH5PFileStorage presents an *extracted view* of all H5P files to the Resource Management, so
 * that the extracted files can be published to a Web-accessible publishing target using the standard Flow resource management.
 *
 * @Flow\Scope("singleton")
 */
class H5PResourceManager
{

    const DEFAULT_H5P_COLLECTION_NAME = 'h5p';

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * Publish all H5P files known to the system to the web-root.
     *
     * Needed after a new library has been installed.
     */
    public function publish()
    {
        $collection = $this->resourceManager->getCollection(self::DEFAULT_H5P_COLLECTION_NAME);
        $target = $collection->getTarget();
        $target->publishCollection($collection);
    }

    /**
     * Get a public URL for a H5P Library file. This is the main entry point for ViewHelpers / Fusion objects during rendering.
     *
     * @param string $id
     * @param string $relativePathAndFilename
     * @return string
     */
    public function getPublicResourceUri(string $id, string $relativePathAndFilename): string
    {
        $target = $this->resourceManager->getCollection(self::DEFAULT_H5P_COLLECTION_NAME)->getTarget();
        return $target->getPublicStaticResourceUri($id . '/' . $relativePathAndFilename);
    }
}
