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
use Sandstorm\NeosH5P\Domain\Model\EditorTempfile;
use Sandstorm\NeosH5P\Domain\Repository\EditorTempfileRepository;

/**
 * Extracts EditorTempfile assets to a subfolder per type.
 */
class EditorTempfileStorage implements StorageInterface
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
     * @var EditorTempfileRepository
     * @Flow\Inject
     */
    protected $editorTempfileRepository;

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
        $editorTempfiles = $this->editorTempfileRepository->findAll();
        /** @var EditorTempfile $editorTempfile */
        foreach ($editorTempfiles as $editorTempfile) {
            $resource = $editorTempfile->getResource();
            $object = new StorageObject();
            $object->setFilename($resource->getFilename());
            $object->setSha1($resource->getSha1());
            $object->setMd5($resource->getMd5());
            $object->setFileSize($resource->getFileSize());
            $object->setStream($resource->getStream());
            $object->setRelativePublicationPath(
                DIRECTORY_SEPARATOR . $this->options['publishingSubfolder'] .
                DIRECTORY_SEPARATOR . $resource->getRelativePublicationPath() . DIRECTORY_SEPARATOR
            );
            yield $object;
        }
    }
}
