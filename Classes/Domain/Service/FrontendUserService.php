<?php

namespace Sandstorm\NeosH5P\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;

/**
 * Works with the default Neos account, which doesn't provide any real user data.
 * Should be replaced by an implementation fitting to the Frontend User package of
 * the integrating site.
 *
 * @Flow\Scope("singleton")
 */
class FrontendUserService implements FrontendUserServiceInterface
{
    public function getXAPIUserSettings(Account $account)
    {
        return [
            'name' => $account->getAccountIdentifier(),
            'mail' => ''
        ];
    }
}
