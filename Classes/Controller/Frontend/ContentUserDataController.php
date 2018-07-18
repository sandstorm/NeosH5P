<?php

namespace Sandstorm\NeosH5P\Controller\Frontend;

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Service\CRUD\ContentUserDataCRUDService;

class ContentUserDataController extends ActionController
{

    /**
     * @Flow\Inject
     * @var ContentUserDataCRUDService
     */
    protected $contentUserDataCRUDService;

    /**
     * @param string $queryString
     * @param string $data
     * @param bool $preload
     * @param bool $invalidate
     *
     * No way to have csrf protection here, as we're not controlling the ajax call
     * @Flow\SkipCsrfProtection
     */
    public function saveAction($queryString, $data = '{}', $preload = false, $invalidate = false)
    {
        $queryArgs = $this->resolveQueryString($queryString);
        $result = $this->contentUserDataCRUDService->handleCreateOrUpdate($queryArgs['contentId'], $queryArgs['dataType'], $queryArgs['subContentId'], $data, $preload, $invalidate);

        // Send the default headers and exit afterwards
        if ($result['success']) {
            \H5PCore::ajaxSuccess();
        } else {
            \H5PCore::ajaxError($result['message']);
        }
        exit;
    }

    private function resolveQueryString(string $queryString): array
    {
        $arguments = [];
        foreach (explode('&', urldecode($queryString)) as $queryParameterString) {
            if (strlen($queryParameterString) == 0) {
                continue;
            }
            $parameterArray = explode('=', $queryParameterString);
            $arguments[$parameterArray[0]] = $parameterArray[1];
        }
        return $arguments;
    }
}
