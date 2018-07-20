<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Editor;

use H5peditorFile;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Utility\Exception\FilesException;
use Neos\Utility\Files;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\Domain\Repository\LibraryRepository;
use Sandstorm\NeosH5P\H5PAdapter\Core\FileAdapter;

/**
 * @Flow\Scope("singleton")
 */
class EditorFileAdapter implements \H5peditorStorage
{

    /**
     * @Flow\Inject
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * Load language file(JSON) from database.
     * This is used to translate the editor fields(title, description etc.)
     *
     * @param string $name The machine readable name of the library(content type)
     * @param int $major Major part of version number
     * @param int $minor Minor part of version number
     * @param string $lang Language code
     * @return string Translation in JSON format
     */
    public function getLanguage($machineName, $majorVersion, $minorVersion, $language)
    {
        // TODO: Implement getLanguage() method.
    }

    /**
     * Decides which content types the editor should have.
     *
     * Two usecases:
     * 1. No input, will list all the available content types.
     * 2. Libraries supported are specified, load additional data and verify
     * that the content types are available. Used by e.g. the Presentation Tool
     * Editor that already knows which content types are supported in its
     * slides.
     *
     * @param array $libraries List of library names + version to load info for
     * @return array List of all libraries loaded
     */
    public function getLibraries($libraries = NULL)
    {
        $librariesWithDetails = [];

        if ($libraries !== null) {
            // Get details for the specified libraries only.
            foreach ($libraries as $libraryData) {
                /** @var Library $library */
                $library = $this->libraryRepository->findOneBy([
                    'name' => $libraryData->name,
                    'majorVersion' => $libraryData->majorVersion,
                    'minorVersion' => $libraryData->minorVersion
                ]);
                if ($library === null || $library->getSemantics() === null) {
                    continue;
                }
                // Library found, add details to list
                $libraryData->tutorialUrl = $library->getTutorialUrl();
                $libraryData->title = $library->getTitle();
                $libraryData->runnable = $library->isRunnable();
                $libraryData->restricted = false; // for now
                // TODO: Implement the below correctly with auth check
                // $libraryData->restricted = $super_user ? FALSE : $library->isRestricted();
                $librariesWithDetails[] = $libraryData;
            }
            // Done, return list with library details
            return $librariesWithDetails;
        }

        // Load all libraries that have semantics and are runnable
        $libraries = $this->libraryRepository->findBy(
            ['runnable' => true],
            ['title' => QueryInterface::ORDER_ASCENDING]
        );
        /** @var Library $library */
        foreach ($libraries as $library) {
            if ($library->getSemantics() === null) {
                continue;
            }
            $libraryData = $library->toStdClass();
            // Make sure we only display the newest version of a library.
            foreach ($librariesWithDetails as $key => $existingLibrary) {
                if ($libraryData->name === $existingLibrary->name) {

                    // Found library with same name, check versions
                    if (($libraryData->majorVersion === $existingLibrary->majorVersion &&
                            $libraryData->minorVersion > $existingLibrary->minorVersion) ||
                        ($libraryData->majorVersion > $existingLibrary->majorVersion)) {
                        // This is a newer version
                        $existingLibrary->isOld = TRUE;
                    } else {
                        // This is an older version
                        $libraryData->isOld = TRUE;
                    }
                }
            }

            // Check to see if content type should be restricted
            $libraryData->restricted = false; // for now
            // TODO: Implement the below correctly with auth check
            // $libraryData->restricted = $super_user ? FALSE : $library->isRestricted();

            // Add new library
            $librariesWithDetails[] = $libraryData;
        }
        return $librariesWithDetails;
    }

    /**
     * "Callback" for mark the given file as a permanent file.
     * Used when saving content that has new uploaded files.
     *
     * @param int $fileId
     */
    public function keepFile($fileId)
    {
        /**
         * Nothing has to be done here, as we don't need the assets connected to EditorTempfiles anymore - they are
         * all zipped up and stored with the content itself.
         */
    }

    /**
     * Alter styles and scripts
     *
     * @param array $files
     *  List of files as objects with path and version as properties
     * @param array $libraries
     *  List of libraries indexed by machineName with objects as values. The objects
     *  have majorVersion and minorVersion as properties.
     */
    public function alterLibraryFiles(&$files, $libraries)
    {
        // Not implemented yet.
    }

    /**
     * Saves a file or moves it temporarily. This is often necessary in order to
     * validate and store uploaded or fetched H5Ps.
     *
     * @param string $data Uri of data that should be saved as a temporary file
     * @param boolean $move_file Can be set to TRUE to move the data instead of saving it
     *
     * @return bool|object Returns false if saving failed or the path to the file
     *  if saving succeeded
     */
    public static function saveFileTemporarily($data, $move_file)
    {
        // TODO: Implement saveFileTemporarily() method.
    }

    /**
     * Marks a file for later cleanup, useful when files are not instantly cleaned
     * up. E.g. for files that are uploaded through the editor.
     *
     * @param H5peditorFile
     * @param $content_id
     */
    public static function markFileForCleanup($file, $content_id)
    {
        /**
         * This is called after
         * @see FileAdapter::saveFile()
         * and is supposed to create an entry in the DB. Since we already do that
         * in the method above, this does nothing.
         */
    }

    /**
     * Clean up temporary files
     *
     * @param string $filePath Path to file or directory
     */
    public static function removeTemporarilySavedFiles($filePath)
    {
        if (is_dir($filePath)) {
            try {
                Files::removeDirectoryRecursively($filePath);
            } catch (FilesException $e) {
                // Swallow - temp dir is regarded as trash anyway and should be deleted regularly (e.g. on deployment)
            }
        } else {
            try {
                unlink($filePath);
            } catch (\Exception $e) {
                // TODO - probably handle better
                // File doesnt exist anymore, swallow
            }
        }
    }

}
