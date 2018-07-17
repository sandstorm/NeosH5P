<?php

namespace Sandstorm\NeosH5P\Domain\Service;

/**
 * This is used by the H5PIntegrationService to fetch the user data from the currently logged-in account.
 */
interface xApiUserServiceInterface
{
    /**
     * Returns an array of: [
     *   'name' => username that is sent in xAPI statements,
     *   'mail' => email address that is sent in xAPI statements
     * ]
     * If no user is logged in, must return null.
     * @return array|null
     */
    public function getUserSettings();
}
