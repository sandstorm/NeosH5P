<?php

namespace Sandstorm\NeosH5P\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ControllerContext;

/**
 * Generates routes from backend modules based on the main request - see method
 *
 * @Flow\Scope("singleton")
 */
class UriGenerationService
{
    /**
     * Extracts the UriBuilder from the provided controller context, retrieves the main request, sets it on the uribuilder
     * so uris are built without the backend interfering (in case we're in a subrequest) and re-sets the original request
     * on the uribuilder so building can continue elsewhere in Neos.
     *
     * @param ControllerContext $controllerContext
     * @param string $actionName Name of the action to be called
     * @param array $controllerArguments Additional query parameters. Will be merged with $this->arguments.
     * @param string $controllerName Name of the target controller. If not set, current ControllerName is used.
     * @param string $packageKey Name of the target package. If not set, current Package is used.
     * @param string $subPackageKey Name of the target SubPackage. If not set, current SubPackage is used.
     * @return string the rendered URI
     */
    public function buildUriWithMainRequest(ControllerContext $controllerContext, $actionName, $controllerArguments = [], $controllerName = null, $packageKey = null, $subPackageKey = null): string
    {
        // Get the main request for URI building
        $mainRequest = $controllerContext->getRequest()->getMainRequest();
        $uriBuilder = $controllerContext->getUriBuilder();
        // Temporarily set the request to the main request so we get the correct URI
        $uriBuilder->setRequest($mainRequest);
        $uri = $uriBuilder->reset()->setCreateAbsoluteUri(true)->uriFor(
            $actionName,
            $controllerArguments,
            $controllerName,
            $packageKey,
            $subPackageKey
        );
        // Reset the URIBuilder to the subrequest to not mess with the backend module routing
        $uriBuilder->setRequest($controllerContext->getRequest());

        return $uri;
    }
}
