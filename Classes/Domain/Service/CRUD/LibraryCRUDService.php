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
class LibraryCRUDService
{
    /**
     * @Flow\Inject(lazy=false)
     * @var \H5PCore
     */
    protected $h5pCore;

    /**
     * Upgrades a library to its newest version
     *
     * @param Library $library
     */
    public function handleUpgrade(Library $library)
    {

    }

    /**
     * Deletes an already existing library.
     *
     * @param Library $library
     */
    public function handleDelete(Library $library)
    {
        $this->h5pCore->deleteLibrary($library->toStdClass());
    }

}
