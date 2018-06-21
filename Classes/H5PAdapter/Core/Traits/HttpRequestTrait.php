<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Core\Traits;

/**
 * Implements outside communication with a method to fetch external data.
 */
trait HttpRequestTrait
{

    /**
     * Fetches a file from a remote server using HTTP GET
     *
     * @param string $url Where you want to get or send data.
     * @param array $data Data to post to the URL.
     * @param bool $blocking Set to 'FALSE' to instantly time out (fire and forget).
     * @param string $stream Path to where the file should be saved.
     * @return string The content (response body). NULL if something went wrong
     */
    public function fetchExternalData($url, $data = NULL, $blocking = TRUE, $stream = NULL)
    {
        // TODO: Implement fetchExternalData() method.
    }
}
