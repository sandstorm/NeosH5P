<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\H5PAdapter\Core\H5PFramework;

class EditorAjaxController extends ActionController
{
    /**
     * @Flow\Inject(lazy=false)
     * @var \H5peditor
     */
    protected $h5pEditor;

    /**
     * @Flow\Inject
     * @var H5PFramework
     */
    protected $h5pFramework;

    /**
     * This is never called, only serves as a uri generation base
     */
    public function indexAction()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function contentTypeCacheAction()
    {
        $this->h5pEditor->ajax->action(\H5PEditorEndpoints::CONTENT_TYPE_CACHE);
        return false;
    }

    /**
     * @param string $libraryInstallQuery
     * @return bool
     */
    public function installLibraryAction(string $libraryInstallQuery)
    {
        $queryArguments = $this->resolveQueryString($libraryInstallQuery);
        $this->h5pEditor->ajax->action(\H5PEditorEndpoints::LIBRARY_INSTALL, 'dummy', $queryArguments['id']);
        // TODO: This doesnt work e.g. for H5P.CoursePresentation. make error msg visible to user!
        return false;
    }

    protected function resolveQueryString(string $queryString): array
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
