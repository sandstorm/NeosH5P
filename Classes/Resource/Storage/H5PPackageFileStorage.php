<?php

namespace Sandstorm\NeosH5P\Resource\Storage;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\ResourceManagement\CollectionInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\ResourceManagement\Storage\StorageInterface;
use Neos\Flow\ResourceManagement\Storage\StorageObject;
use Neos\Flow\Utility\Environment;
use Neos\Utility\Exception\FilesException;
use Neos\Utility\Files;
use Neos\Utility\Unicode\Functions as UnicodeFunctions;

/**
 * Read-only storage for H5P core packages ; making some subfolders exposed in the web root.
 *
 * @Flow\Scope("singleton")
 */
class H5PPackageFileStorage implements StorageInterface
{
    /**
     * Name which identifies this resource storage
     *
     * @var string
     */
    protected $name;

    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param string $name Name of this storage instance, according to the resource settings
     * @param array $options Options for this storage
     * @throws Exception
     */
    public function __construct($name, array $options = [])
    {
        $this->name = $name;
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'path':
                case 'library':
                case 'subfolders':
                    $this->options[$key] = $value;
                    break;
                default:
                    if ($value !== null) {
                        throw new Exception(sprintf('An unknown option "%s" was specified in the configuration of a resource FileSystemStorage. Please check your settings.', $key), 1361533187);
                    }
            }
        }
    }

    /**
     * Returns the instance name of this storage
     *
     * @return string
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a stream handle which can be used internally to open / copy the given resource
     * stored in this storage.
     *
     * @param PersistentResource $resource The resource stored in this storage
     * @return resource | boolean The resource stream or FALSE if the stream could not be obtained
     * @api
     */
    public function getStreamByResource(PersistentResource $resource)
    {
        throw new \RuntimeException("not supported");
    }

    /**
     * Returns a stream handle which can be used internally to open / copy the given resource
     * stored in this storage.
     *
     * @param string $relativePath A path relative to the storage root, for example "MyFirstDirectory/SecondDirectory/Foo.css"
     * @return resource | boolean A URI (for example the full path and filename) leading to the resource file or FALSE if it does not exist
     * @api
     */
    public function getStreamByResourcePath($relativePath)
    {
        throw new \RuntimeException("not supported currently");
    }

    /**
     * Retrieve all Objects stored in this storage.
     *
     * @return \Generator<StorageObject>
     * @api
     */
    public function getObjects()
    {
        foreach ($this->resourceManager->getCollectionsByStorage($this) as $collection) {
            yield $this->getObjectsByCollection($collection);
        }
    }

    /**
     * Retrieve all Objects stored in this storage, filtered by the given collection name
     *
     * @param CollectionInterface $collection
     * @param callable $callback Function called after each iteration
     * @throws FilesException
     * @return \Generator<StorageObject>
     * @api
     */
    public function getObjectsByCollection(CollectionInterface $collection, callable $callback = null)
    {
        $iteration = 0;

        foreach ($this->options['subfolders'] as $subfoldername) {
            $relativeSubfolderPath = $this->options['library'] . DIRECTORY_SEPARATOR . $subfoldername;
            $absolutePath = $this->options['path'] . DIRECTORY_SEPARATOR . $relativeSubfolderPath;
            foreach (Files::getRecursiveDirectoryGenerator($absolutePath) as $resourcePathAndFilename) {
                $object = $this->createStorageObject($resourcePathAndFilename, $relativeSubfolderPath);
                yield $object;
                if (is_callable($callback)) {
                    call_user_func($callback, $iteration, $object);
                }
                $iteration++;
            }
        }
    }

    /**
     * Create a storage object for the given static resource path.
     *
     * @param string $resourcePathAndFilename
     * @param string $relativePublicationPath
     * @return StorageObject
     */
    protected function createStorageObject($resourcePathAndFilename, string $relativePublicationPath)
    {
        $pathInfo = UnicodeFunctions::pathinfo($resourcePathAndFilename);

        $object = new StorageObject();
        $object->setFilename($pathInfo['basename']);
        $object->setSha1(sha1_file($resourcePathAndFilename));
        $object->setMd5(md5_file($resourcePathAndFilename));
        $object->setFileSize(filesize($resourcePathAndFilename));
        if (isset($pathInfo['dirname'])) {
            $object->setRelativePublicationPath($relativePublicationPath . '/');
        }
        $object->setStream(function () use ($resourcePathAndFilename) {
            return fopen($resourcePathAndFilename, 'r');
        });

        return $object;
    }
}
