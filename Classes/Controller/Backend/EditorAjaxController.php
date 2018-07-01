<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Flow\Mvc\Controller\ActionController;

class EditorAjaxController extends ActionController {

    /**
     * This is never called, only serves as a uri generation base
     */
    public function indexAction(){
        return false;
    }

    /**
     * @return string
     */
    public function contentTypeCacheAction() {
        return json_encode(["foo" => "bar"]);
    }
}
