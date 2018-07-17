<?php

namespace Sandstorm\NeosH5P\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context;

/**
 * Retrieves the username from the currently logged-in account and leaves the e-mail blank.
 *
 * @Flow\Scope("singleton")
 */
class DefaultxApiUserService implements xApiUserServiceInterface
{
    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    public function getUserSettings()
    {
        $currentAccount = $this->securityContext->getAccount();
        if ($currentAccount === null) {
            return null;
        }
        return [
            'name' => $currentAccount->getAccountIdentifier(),
            'mail' => ''
        ];
    }
}
