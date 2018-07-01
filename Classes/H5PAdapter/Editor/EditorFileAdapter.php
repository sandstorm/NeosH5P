<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Editor;

use H5peditorFile;
use Neos\Flow\Annotations as Flow;
use Neos\Utility\Exception\FilesException;
use Neos\Utility\Files;

/**
 * @Flow\Scope("singleton")
 */
class EditorFileAdapter implements \H5peditorStorage
{
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
        // TODO: Implement getLibraries() method.
    }

    /**
     * "Callback" for mark the given file as a permanent file.
     * Used when saving content that has new uploaded files.
     *
     * @param int $fileId
     */
    public function keepFile($fileId)
    {
        // TODO: Implement keepFile() method.
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
        // TODO: Implement alterLibraryFiles() method.
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
        // TODO: Implement markFileForCleanup() method.
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
