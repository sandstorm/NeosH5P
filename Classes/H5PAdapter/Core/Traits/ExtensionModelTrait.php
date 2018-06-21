<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Core\Traits;

/**
 * Implements all db storage and loading for models
 * and model properties related to classes from the "model extension", meaning:
 * H5PConfigSetting
 * H5PHubCachedLibrary
 * H5PEvent
 * H5PEditorTempFile
 */
trait ExtensionModelTrait
{
    /**
     * Get stored setting.
     *
     * @param string $name
     *   Identifier for the setting
     * @param string $default
     *   Optional default value if settings is not set
     * @return mixed
     *   Whatever has been stored as the setting
     */
    public function getOption($name, $default = NULL)
    {
        // TODO: Implement getOption() method.
    }

    /**
     * Stores the given setting.
     * For example when did we last check h5p.org for updates to our libraries.
     *
     * @param string $name
     *   Identifier for the setting
     * @param mixed $value Data
     *   Whatever we want to store as the setting
     */
    public function setOption($name, $value)
    {
        // TODO: Implement setOption() method.
    }

    /**
     * Stores hash keys for cached assets, aggregated JavaScripts and
     * stylesheets, and connects it to libraries so that we know which cache file
     * to delete when a library is updated.
     *
     * @param string $key
     *  Hash key for the given libraries
     * @param array $libraries
     *  List of dependencies(libraries) used to create the key
     */
    public function saveCachedAssets($key, $libraries)
    {
        // TODO: Implement saveCachedAssets() method.
    }

    /**
     * Locate hash keys for given library and delete them.
     * Used when cache file are deleted.
     *
     * @param int $library_id
     *  Library identifier
     * @return array
     *  List of hash keys removed
     */
    public function deleteCachedAssets($library_id)
    {
        // TODO: Implement deleteCachedAssets() method.
    }

    /**
     * Replaces existing content type cache with the one passed in
     *
     * @param object $contentTypeCache Json with an array called 'libraries'
     *  containing the new content type cache that should replace the old one.
     */
    public function replaceContentTypeCache($contentTypeCache)
    {
        // TODO: Implement replaceContentTypeCache() method.
    }
}
