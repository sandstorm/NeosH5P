<?php

namespace Sandstorm\NeosH5P\Resource\Storage;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\CollectionInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\ResourceManagement\ResourceRepository;
use Neos\Flow\ResourceManagement\Storage\StorageInterface;
use Neos\Flow\ResourceManagement\Storage\StorageObject;
use Neos\Flow\Utility\Environment;

/**
 * Read-only shim for all registered H5P Libraries; making the extracted versions exposed in the web root.
 *
 * Note: The ExtractedH5PFileStorage is NOT responsible for actually *storing* H5P files; instead, it reads them
 * by querying the FakeLibraryRepository. It's merely responsible for *exposing the contents* of all H5P bundles.
 *
 * @Flow\Scope("singleton")
 */
class ExtractedH5PFileStorage implements StorageInterface
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
     * @Flow\Inject
     * @var ResourceRepository
     */
    protected $resourceRepository;

    /**
     * Constructor
     *
     * @param string $name Name of this storage instance, according to the resource settings
     * @param array $options Options for this storage
     * @throws \Neos\Flow\Exception
     */
    public function __construct($name, array $options = [])
    {
        $this->name = $name;
        foreach ($options as $key => $value) {
            switch ($key) {
                default:
                    if ($value !== null) {
                        throw new \Neos\Flow\Exception(sprintf('An unknown option "%s" was specified in the configuration of a resource FileSystemStorage. Please check your settings.', $key), 1361533187);
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
     * @return \Generator<StorageObject>
     * @api
     */
    public function getObjectsByCollection(CollectionInterface $collection, callable $callback = null)
    {
        $iterator = $this->resourceRepository->findByCollectionNameIterator($collection->getName());
        $iteration = 0;
        foreach ($this->resourceRepository->iterate($iterator, $callback) as $resource) {
            $h5pPathAndFilename = $resource->createTemporaryLocalCopy();
            $zipArchive = new \ZipArchive();

            $zipArchive->open($h5pPathAndFilename);
            for ($i = 0; $i < $zipArchive->numFiles; $i++) {
                $pathAndFilenameInZip = $zipArchive->getNameIndex($i);
                if (substr($pathAndFilenameInZip, -1) === '/') {
                    // Skip directories (everything ending with "/")
                    continue;
                }
                $fileContents = stream_get_contents($zipArchive->getStream($pathAndFilenameInZip));

                $stream = fopen('php://memory', 'r+');
                fwrite($stream, $fileContents);
                rewind($stream);
                $object = new StorageObject();
                $object->setFilename($pathAndFilenameInZip);
                $object->setSha1(sha1($fileContents));
                $object->setMd5(md5($fileContents));
                $object->setFileSize(strlen($fileContents));
                $object->setStream($stream);
                $object->setRelativePublicationPath('libraries/' . dirname($pathAndFilenameInZip) . '/');
                yield $object;

                if (is_callable($callback)) {
                    call_user_func($callback, $iteration, $object);
                }
                $iteration++;
            }
            $zipArchive->close();
        }
    }
}
