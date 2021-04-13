<?php

namespace Sandstorm\NeosH5P\Resource\Storage;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\ResourceManagement\CollectionInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\ResourceManagement\Storage\StorageInterface;
use Neos\Flow\ResourceManagement\Storage\StorageObject;
use Neos\Flow\Utility\Environment;

/**
 * Abstract base class for the extracted file storages for Library and Content.
 * Expects three storage Options:
 *
 * - Subfolder of the H5P Public web directory to publish to. Example:
 *   publishingSubfolder: 'libraries'
 * - Name of the method that yields the zipped PersistentResource which should be extracted. Example:
 *   resourceGetterMethod: 'getZippedLibraryFile'
 * - Method that returns the name of the directory for each individual extracted item. Example:
 *   itemFolderNameMethod: 'getFolderName'
 */
abstract class AbstractExtractedStorage implements StorageInterface
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
     * Subclasses must inject the repository from which the asset resources should come here.
     * (Will be a LibraryRepository or a ContentRepository)
     * @api
     *
     * @var Repository
     */
    protected $repository;

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
                case 'publishingSubfolder':
                case 'resourceGetterMethod':
                case 'itemFolderNameMethod':
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
     * @throws \Neos\Flow\ResourceManagement\Exception
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
     * @throws \Neos\Flow\ResourceManagement\Exception
     * @return \Generator<StorageObject>
     * @api
     */
    public function getObjectsByCollection(CollectionInterface $collection, callable $callback = null)
    {
        $iteration = 0;
        $items = $this->repository->findAll();
        foreach ($items as $item) {
            $resourceGetterMethod = $this->options['resourceGetterMethod'];
            $resource = $item->$resourceGetterMethod();
            if (!$resource instanceof PersistentResource) {
                continue;
            }
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
                $object->setFileSize(strlen($fileContents));
                $object->setStream($stream);
                $itemFolderNameMethod = $this->options['itemFolderNameMethod'];
                $object->setRelativePublicationPath(
                    DIRECTORY_SEPARATOR . $this->options['publishingSubfolder'] .
                    DIRECTORY_SEPARATOR . $item->$itemFolderNameMethod() .
                    DIRECTORY_SEPARATOR . dirname($pathAndFilenameInZip) .
                    DIRECTORY_SEPARATOR);
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
