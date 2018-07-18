<?php

namespace Sandstorm\NeosH5P\Domain\Service;
use Neos\Flow\Security\Account;

/**
 * This interface allows integrating sites to define how data is fetched from their frontend user model, as Neos
 * does not make any assumptions as to which user model you're using for frontend logins. We
 */
interface FrontendUserServiceInterface
{
    /**
     * Returns an array of: [
     *   'name' => username that is sent in xAPI statements,
     *   'mail' => email address that is sent in xAPI statements
     * ]
     * If no user is found for the given account, must return null.
     * @return array|null
     */
    public function getXAPIUserSettings(Account $account);
}
