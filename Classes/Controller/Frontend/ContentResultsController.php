<?php

namespace Sandstorm\NeosH5P\Controller\Frontend;

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Service\CRUD\ContentResultsCRUDService;

class ContentResultsController extends ActionController
{

    /**
     * @Flow\Inject
     * @var ContentResultsCRUDService
     */
    protected $contentResultsCRUDService;

    /**
     * @param int $contentId
     * @param int $score
     * @param int $maxScore
     * @param int $opened
     * @param int $finished
     * @param int $time
     *
     * No way to have csrf protection here, as we're not controlling the ajax call
     * @Flow\SkipCsrfProtection
     */
    public function saveAction($contentId, $score, $maxScore, $opened, $finished, $time = 0)
    {
        $result = $this->contentResultsCRUDService->handleCreateOrUpdate($contentId, $score, $maxScore, $opened, $finished, $time);

        // Send the default headers and exit afterwards
        if ($result['success']) {
            \H5PCore::ajaxSuccess();
        } else {
            \H5PCore::ajaxError($result['message']);
        }
        exit;
    }
}
