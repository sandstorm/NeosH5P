<?php

namespace Sandstorm\NeosH5P\Resource\Storage;

use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Neos\Flow\Annotations as Flow;

/**
 * Read-only shim for all H5P Contents; making the extracted versions exposed in the web root.
 *
 * Note: The ExtractedH5PFileStorage is NOT responsible for actually *storing* H5P files; instead, it reads them
 * by querying the ContentRepository. It's merely responsible for *exposing the contents* of all H5P content zips.
 *
 * @Flow\Scope("singleton")
 */
class ExtractedContentStorage extends AbstractExtractedStorage
{
    /**
     * @Flow\Inject
     * @var ContentRepository
     */
    protected $repository;
}
