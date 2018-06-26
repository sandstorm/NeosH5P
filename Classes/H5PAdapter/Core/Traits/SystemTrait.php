<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Core\Traits;

/**
 * Takes care of system-level H5P Framework methods.
 */
trait SystemTrait
{
    /**
     * Returns info for the current platform
     *
     * @return array
     *   An associative array containing:
     *   - name: The name of the platform, for instance "Wordpress"
     *   - version: The version of the platform, for instance "4.0"
     *   - h5pVersion: The version of the H5P plugin/module
     */
    public function getPlatformInfo()
    {
        return [
            "name" => "Neos",
            "version" => $this->packageManager->getPackage("Neos.Neos")->getInstalledVersion(),
            "h5pVersion" => $this->packageManager->getPackage("Sandstorm.NeosH5P")->getInstalledVersion()
        ];
    }

    /**
     * Show the user an error message
     *
     * @param string $message The error message
     * @param string $code An optional code
     */
    public function setErrorMessage($message, $code = NULL)
    {
        // TODO: Implement setErrorMessage() method.
    }

    /**
     * Show the user an information message
     *
     * @param string $message
     *  The error message
     */
    public function setInfoMessage($message)
    {
        // TODO: Implement setInfoMessage() method.
    }

    /**
     * Return messages
     *
     * @param string $type 'info' or 'error'
     * @return string[]
     */
    public function getMessages($type)
    {
        // TODO: Implement getMessages() method.
    }

    /**
     * Translation function
     *
     * @param string $message
     *  The english string to be translated.
     * @param array $replacements
     *   An associative array of replacements to make after translation. Incidences
     *   of any key in this array are replaced with the corresponding value. Based
     *   on the first character of the key, the value is escaped and/or themed:
     *    - "!variable": inserted as is
     *    - "@variable": escape plain text to HTML
     *    - "%variable": escape text and theme as a placeholder for user-submitted
     *      content
     * @return string Translated string
     * Translated string
     */
    public function t($message, $replacements = array())
    {
        // TODO: Implement t() method.
    }

    /**
     * Returns the URL to the library admin page
     *
     * @return string
     *   URL to admin page
     */
    public function getAdminUrl()
    {
        // TODO: Implement getAdminUrl() method.
    }

    /**
     * Is H5P in development mode?
     *
     * @return boolean
     *  TRUE if H5P development mode is active
     *  FALSE otherwise
     */
    public function isInDevMode()
    {
        // TODO: Implement isInDevMode() method.
    }

    /**
     * Aggregate the current number of H5P authors
     * @return int
     */
    public function getNumAuthors()
    {
        // TODO: Implement getNumAuthors() method.
    }
}
