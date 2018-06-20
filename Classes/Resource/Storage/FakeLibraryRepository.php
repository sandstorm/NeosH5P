<?php

namespace Sandstorm\NeosH5P\Resource\Storage;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;


/**
 * TODO: remove and replace by *REAL* library Repository
 *
 * @Flow\Scope("singleton")
 */
class FakeLibraryRepository
{

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @return FakeLibrary[]
     */
    public function findAll(): array
    {
        $resource = $this->resourceManager->importResource($this->packageManager->getPackage('Sandstorm.NeosH5P')->getPackagePath() . 'Resources/Private/ExampleH5P/memory-game-5-708.h5p');
        return [
            new FakeLibrary('H5P.MemoryGame', $resource)
        ];
    }
}
