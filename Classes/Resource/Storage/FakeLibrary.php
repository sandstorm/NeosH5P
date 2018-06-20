<?php

namespace Sandstorm\NeosH5P\Resource\Storage;


use Neos\Flow\ResourceManagement\PersistentResource;

/**
 * TODO: remove and replace by *REAL* library
 */
class FakeLibrary
{
    protected $id;

    /**
     * @var PersistentResource
     */
    protected $h5pFile;


    public function __construct($id, PersistentResource $h5pFile)
    {
        $this->id = $id;
        $this->h5pFile = $h5pFile;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getH5pFile(): PersistentResource
    {
        return $this->h5pFile;
    }


}
