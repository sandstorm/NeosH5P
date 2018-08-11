<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Core;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Utility\Exception\FilesException;
use Neos\Utility\Files;
use Sandstorm\NeosH5P\Command\H5PCommandController;
use Sandstorm\NeosH5P\Domain\Model\CachedAsset;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Model\EditorTempfile;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\Domain\Repository\CachedAssetRepository;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
use Sandstorm\NeosH5P\Domain\Repository\EditorTempfileRepository;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;

/**
 * @Flow\Scope("singleton")
 */
class FileAdapter implements \H5PFileStorage
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * @Flow\Inject
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * @Flow\Inject
     * @var CachedAssetRepository
     */
    protected $cachedAssetRepository;

    /**
     * @Flow\Inject
     * @var EditorTempfileRepository
     */
    protected $editorTempfileRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    const H5P_TEMP_DIR = FLOW_PATH_DATA . 'Temporary/H5P';

    /**
     * Store the library folder.
     *
     * @param array $libraryData
     *  Library properties
     * @throws Exception
     */
    public function saveLibrary($libraryData)
    {
        $zipFileName = $this->zipDirectory($libraryData['uploadDirectory']);
        $resource = $this->resourceManager->importResource($zipFileName);

        /** @var Library $library */
        $library = $this->libraryRepository->findOneByLibraryId($libraryData['libraryId']);
        $library->setZippedLibraryFile($resource);
        $this->libraryRepository->update($library);
    }

    /**
     * @param $directoryPath string directory to zip.
     * @return string name of zipped file
     */
    private function zipDirectory($directoryPath): string
    {
        // ZIP everything in the library's upload directory and save it as a resource
        $zipFileName = $directoryPath . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipFileName, \ZipArchive::CREATE);

        // Create recursive directory iterator
        /** @var \SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directoryPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if ($file->isDir()) {
                continue;
            }

            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($directoryPath) + 1);

            // Add current file to archive
            $zip->addFile($filePath, $relativePath);
        }

        // Zip archive will be created only after closing object
        $zip->close();
        return $zipFileName;
    }

    /**
     * Store the content folder.
     *
     * @param string $source
     *  Path on file system to content directory.
     * @param array $content
     *  Content properties
     */
    public function saveContent($source, $content)
    {
        // TODO: Implement saveContent() method.
    }

    /**
     * Remove content folder.
     *
     * @param array $content
     *  Content properties
     */
    public function deleteContent($content)
    {
        // TODO: Implement deleteContent() method.
    }

    /**
     * Creates a stored copy of the content folder.
     *
     * @param string $id
     *  Identifier of content to clone.
     * @param int $newId
     *  The cloned content's identifier
     */
    public function cloneContent($id, $newId)
    {
        // TODO: Implement cloneContent() method.
    }

    /**
     * Get path to a new unique tmp folder.
     *
     * @return string Path
     * @throws FilesException
     */
    public function getTmpPath()
    {
        Files::createDirectoryRecursively(self::H5P_TEMP_DIR);
        return self::H5P_TEMP_DIR . DIRECTORY_SEPARATOR . uniqid('h5p-');
    }

    /**
     * Fetch content folder and save in target directory.
     *
     * @param int $id
     *  Content identifier
     * @param string $target
     *  Where the content folder will be saved
     */
    public function exportContent($id, $target)
    {
        // We essentially need to fetch to contents of the zippedContentFile here, and
        // extract them to another (temporary) location.
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($id);
        if ($content->getZippedContentFile() === null) {
            // We have no zip - all we need to do is make sure the directory exists
            Files::createDirectoryRecursively($target);
            return;
        }

        $zipArchive = new \ZipArchive();
        $zipArchive->open($content->getZippedContentFile()->createTemporaryLocalCopy());
        $zipArchive->extractTo($target);
        $zipArchive->close();
    }

    /**
     * Fetch library folder and save in target directory.
     *
     * @param array $library
     *  Library properties
     * @param string $target
     *  Where the library folder will be saved
     */
    public function exportLibrary($library, $target)
    {
        // We essentially need to fetch to contents of the zippedLibraryFile here, and
        // extract them to another (temporary) location.
        /** @var Library $library */
        $library = $this->libraryRepository->findOneByLibraryId($library['libraryId']);

        $zipArchive = new \ZipArchive();
        $zipArchive->open($library->getZippedLibraryFile()->createTemporaryLocalCopy());
        $zipArchive->extractTo($target . DIRECTORY_SEPARATOR . $library->getFolderName()); // crazy, but the API does that..
        $zipArchive->close();
    }

    /**
     * Save export in file system
     *
     * @param string $source
     *  Path on file system to temporary export file.
     * @param string $filename
     *  Name of export file.
     */
    public function saveExport($source, $filename)
    {
        // Get the content from the filename again
        $content = $this->getContentFromExportFilename($filename);

        // Import the new export file as a resource
        $exportResource = $this->resourceManager->importResource($source);
        $exportResource->setFilename($filename);
        $exportResource->setMediaType('application/zip');
        $content->setExportFile($exportResource);
        $this->contentRepository->update($content);
    }

    /**
     * Removes given export file
     *
     * @param string $filename
     */
    public function deleteExport($filename)
    {
        /**
         * Because of a bug, we get a different filename here than below in hasExport.
         * We get e.g. "5.h5p" here instead of "my-slug-5.h5p".
         * however, our regex can handle it and will return the correct Content object.
         */
        $content = $this->getContentFromExportFilename($filename);
        if ($content !== null && $content->getExportFile() !== null) {
            $this->resourceManager->deleteResource($content->getExportFile());
            $content->setExportFile(null);
            $this->contentRepository->update($content);
        }
    }

    /**
     * Check if the given export file exists
     *
     * @param string $filename
     * @return boolean
     */
    public function hasExport($filename)
    {
        /**
         * The filename we get here is $content['slug'] . '-' . $content['id'] . '.h5p').
         * As we don't want to search the resource repository by filename (there could be
         * other files with the same name), we will extract the content ID from it again,
         * retrieve the Content and ask it for its export file.
         */
        $content = $this->getContentFromExportFilename($filename);
        if ($content === null) {
            return false;
        }
        return $content->getExportFile() !== null;
    }

    /**
     * The filename we get here is $content['slug'] . '-' . $content['id'] . '.h5p').
     * As we don't want to search the resource repository by filename (there could be
     * other files with the same name), we will extract the content ID from it again,
     * retrieve the Content based on that.
     *
     * @param string $filename
     * @return Content|null
     */
    private function getContentFromExportFilename(string $filename)
    {
        $matches = [];
        preg_match('/^([^-]*-)?(\d+)\.h5p$/is', $filename, $matches);
        $contentId = end($matches);
        return $this->contentRepository->findOneByContentId($contentId);
    }

    /**
     * Will concatenate all JavaScrips and Stylesheets into two files in order
     * to improve page performance.
     *
     * @param array $files
     *  A set of all the assets required for content to display
     * @param string $key
     *  Hashed key for cached asset
     */
    public function cacheAssets(&$files, $key)
    {
        /**
         * The files we get here are published H5P library CSS and JS files.
         * We create and publish the PersistentResource objects and CachedAsset objects
         * here and make the assignment to libraries later when we have that information
         * in H5PFramework->saveCachedAssets().
         * @see H5PFramework::saveCachedAssets()
         * @see \H5PCore::getDependenciesFiles
         */
        foreach ($files as $type => $assets) {
            if (empty($assets)) {
                continue;
            }

            $content = '';
            foreach ($assets as $asset) {
                // Get content from asset file
                $assetContent = file_get_contents(FLOW_PATH_WEB . $asset->path);
                $cssRelPath = preg_replace('/[^\/]+$/', '', $asset->path);

                // Get file content and concatenate
                if ($type === 'scripts') {
                    $content .= $assetContent . ";\n";
                } else {
                    // Rewrite relative URLs used inside stylesheets
                    // TODO: This doesn't work correctly yet
                    $content .= preg_replace_callback(
                            '/url\([\'"]?([^"\')]+)[\'"]?\)/i',
                            function ($matches) use ($cssRelPath) {
                                if (preg_match("/^(data:|([a-z0-9]+:)?\/)/i", $matches[1]) === 1) {
                                    return $matches[0]; // Not relative, skip
                                }
                                return 'url("../../..' . $cssRelPath . $matches[1] . '")';
                            },
                            $assetContent) . "\n";
                }
            }

            $ext = ($type === 'scripts' ? 'js' : 'css');
            $persistentResource = $this->resourceManager->importResourceFromContent($content, $key . '.' . $ext);
            // Create the CachedAsset object here
            $cachedAsset = new CachedAsset();
            $cachedAsset->setHashKey($key);
            $cachedAsset->setType($type);
            $cachedAsset->setResource($persistentResource);
            $this->cachedAssetRepository->add($cachedAsset);
            // whitelist, as this can be called on GET requests
            $this->persistenceManager->whitelistObject($cachedAsset);

            $files[$type] = array((object)array(
                'path' => $this->resourceManager->getPublicPersistentResourceUri($persistentResource),
                'version' => ''
            ));
        }
        // Persist, so the cachedasset objects can be found in H5PFramework->saveCachedAssets
        $this->persistenceManager->persistAll();
    }

    /**
     * Will check if there are cache assets available for content.
     *
     * @param string $key
     *  Hashed key for cached asset
     * @return array|null
     */
    public function getCachedAssets($key)
    {
        $files = [];

        $cachedAssets = $this->cachedAssetRepository->findByHashKey($key);

        /** @var CachedAsset $cachedAsset */
        foreach ($cachedAssets as $cachedAsset) {
            if ($cachedAsset->getType() == 'scripts') {
                $files['scripts'] = [(object)[
                    'path' => $this->resourceManager->getPublicPersistentResourceUri($cachedAsset->getResource()),
                    'version' => ''
                ]];
            }
            if ($cachedAsset->getType() == 'styles') {
                $files['styles'] = [(object)[
                    'path' => $this->resourceManager->getPublicPersistentResourceUri($cachedAsset->getResource()),
                    'version' => ''
                ]];
            }
        }

        return empty($files) ? null : $files;
    }

    /**
     * Remove the aggregated cache files.
     *
     * @param array $keys
     *   The hash keys of removed files
     */
    public function deleteCachedAssets($keys)
    {
        /**
         * This is called right after H5PFramework->deleteCachedAssets and is supposed to remove the actual asset files.
         * Since we have cascade="remove" set on the relation CachedAsset->PersistentResource, the Resource should be
         * removed automatically by Doctrine. This means we have to do nothing here.
         */
    }

    /**
     * Read file content of given file and then return it.
     *
     * @param string $file_path
     * @return string contents
     */
    public function getContent($file_path)
    {
        /**
         * This might cause issues if files are not put locally, because the path is generated inside H5P
         * and cannot be modified.
         * @see \H5PCore::getDependenciesFiles()
         * @see \H5PCore::getDependencyAssets()
         */
        return file_get_contents($file_path);
    }

    /**
     * Save files uploaded through the editor.
     * The files must be marked as temporary until the content form is saved.
     *
     * @param \H5peditorFile $file
     * @param int $contentId
     */
    public function saveFile($file, $contentId)
    {
        // This creates an editortempfile, attaches a resource, publishes that resource, and returns a file ID.

        // If we have a content id set, we assign the file directly to a content element.
        // In the current implementation, this doesn't happen yet - all uploaded editor files are
        // first saved as an EditorTempfile. Therefore, we throw an Excaption if we get a content id.
        if (is_int($contentId) && $contentId > 0) {
            throw new Exception("Uploading files directly to a Content element is not supported yet.");
        }

        $persistentResource = $this->resourceManager->importResourceFromContent(
            $file->getData() ?: file_get_contents($_FILES['file']['tmp_name']),
            $file->getName());
        $persistentResource->setRelativePublicationPath($file->getType() . 's');

        $editorTempfile = new EditorTempfile();
        $editorTempfile->setResource($persistentResource);
        $editorTempfile->setCreatedAt(new \DateTime());
        // With this, we can find the editor temp file again later - see below in cloneContentFile
        $editorTempfile->setTemporaryFilename($file->getName());
        $this->editorTempfileRepository->add($editorTempfile);
        // Persist all, because this is fetched directly below when we publish
        $this->persistenceManager->persistAll();

        /**
         * We need to publish all resources from the collection 'h5p-editor-tempfiles' before
         * we can return, as this moves the EditorTempfile asset to the right location so
         * the H5P editor can find it. This takes a bit, but should not be an issues as
         * the EditorTempfiles should be cleaned up regularly.
         * @see H5PCommandController::clearEditorTempFilesCommand()
         */
        $collection = $this->resourceManager->getCollection('h5p-editor-tempfiles');
        $target = $collection->getTarget();
        $target->publishCollection($collection);

        return $file;
    }

    /**
     * Copy a file from another content or editor tmp dir.
     * Used when copy pasting content in H5P.
     *
     * @param string $file path + name
     * @param string|int $fromId Content ID or 'editor' string
     * @param int $toId Target Content ID
     */
    public function cloneContentFile($file, $fromId, $toId)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($toId);
        // Dump the contents of the zipfile to disk temporarily
        $content->dumpContentFileToTemporaryDirectory();

        // Determine source path. $fromId can also be a content ID, we don't support this yet.
        if ($fromId !== 'editor') {
            throw new Exception('Copying files from another content is not supported yet.');
        }

        // We can find the asset again by looking for the editortempfile
        /** @var EditorTempfile $editorTempfile */
        $filenameParts = explode('/', $file);
        $editorTempfile = $this->editorTempfileRepository->findOneByTemporaryFilename(end($filenameParts));

        // Determine target path
        $targetPath = $content->buildContentFileTempPath() . DIRECTORY_SEPARATOR . $file;
        // Make sure the target directory exists
        Files::createDirectoryRecursively(dirname($targetPath));
        file_put_contents($targetPath, $editorTempfile->getResource()->getStream());
    }

    /**
     * Copy a content from one directory to another. Defaults to cloning
     * content from the current temporary upload folder to the editor path.
     *
     * @param string $source path to source directory
     * @param string $contentId Id of content
     *
     * @return object Object containing h5p json and content json data
     */
    public function moveContentDirectory($source, $contentId = NULL)
    {
        // TODO: Implement moveContentDirectory() method.
    }

    /**
     * Checks to see if content has the given file.
     * Used when saving content.
     *
     * @param string $file path + name
     * @param int $contentId
     * @return string|int File ID or NULL if not found
     */
    public function getContentFile($file, $contentId)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($contentId);
        // Dump the contents of the zipfile to disk temporarily
        $content->dumpContentFileToTemporaryDirectory();

        $filePath = $content->buildContentFileTempPath() . DIRECTORY_SEPARATOR . $file;

        return file_exists($filePath) ? $filePath : null;
    }

    /**
     * Remove content files that are no longer used.
     * Used when saving content.
     *
     * @param string $file path + name
     * @param int $contentId
     */
    public function removeContentFile($file, $contentId)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($contentId);
        // Dump the contents of the zipfile to disk temporarily
        $content->dumpContentFileToTemporaryDirectory();

        $path = $content->buildContentFileTempPath() . DIRECTORY_SEPARATOR . $file;
        if (file_exists($path)) {
            unlink($path);
            // Clean up any empty parent directories to avoid cluttering the file system
            Files::removeEmptyDirectoriesOnPath(dirname($path));
        }
    }

    /**
     * Check if server setup has write permission to
     * the required folders
     *
     * @return bool True if server has the proper write access
     */
    public function hasWriteAccess()
    {
        // TODO: Implement hasWriteAccess() method.
    }

}
