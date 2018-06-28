<?php

namespace Sandstorm\NeosH5P\Resource\Storage;

use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;
use Neos\Flow\Annotations as Flow;

/**
 * Read-only shim for all registered H5P Libraries; making the extracted versions exposed in the web root.
 *
 * Note: The ExtractedH5PFileStorage is NOT responsible for actually *storing* H5P files; instead, it reads them
 * by querying the FakeLibraryRepository. It's merely responsible for *exposing the contents* of all H5P bundles.
 *
 * @Flow\Scope("singleton")
 */
class ExtractedH5PLibraryStorage extends AbstractExtractedH5PStorage
{
    /**
     * @Flow\Inject
     * @var LibraryRepository
     */
    protected $repository;
}
