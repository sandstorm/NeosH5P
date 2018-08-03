<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Annotations as Flow;

class ContentUpgradeAjaxController extends ActionController
{
    /**
     * @Flow\SkipCsrfProtection
     * @param int $oldLibraryId
     */
    public function migrateContentAction(int $oldLibraryId){
        echo $oldLibraryId;
        exit;
    }
    /**
     * @Flow\SkipCsrfProtection
     * @param int $libraryId
     */
    public function libraryInfoAction(int $libraryId){
        echo $libraryId;
        exit;
    }
}
