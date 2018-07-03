<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
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
     * @Flow\InjectConfiguration(path="h5pPublicFolder.path")
     * @var string
     */
    protected $h5pPublicFolderPath;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

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
        // TODO: this probably has to do with output buffering - if we remove the "die", nothing is returned if errors occur.
        die;
        return false;
    }

    /**
     * @param string $queryString
     * @Flow\SkipCsrfProtection
     * @return bool
     */
    public function installLibraryAction(string $queryString)
    {
        $queryArguments = $this->resolveQueryString($queryString);
        $this->h5pEditor->ajax->action(\H5PEditorEndpoints::LIBRARY_INSTALL, 'dummy', $queryArguments['id']);
        // Publish the "libraries" collection so we get the unzipped file
        $libraryCollection = $this->resourceManager->getCollection('h5p-libraries');
        $libraryCollection->getTarget()->publishCollection($libraryCollection);
        // TODO: This doesnt work e.g. for H5P.CoursePresentation. make error msg visible to user!
        // TODO: this probably has to do with output buffering - if we remove the "die", nothing is returned if errors occur.
        die;
        return false;
    }

    /**
     * @param string $queryString
     * @return bool
     */
    public function libraryDetailsAction(string $queryString)
    {
        $queryArguments = $this->resolveQueryString($queryString);

        /**
         * This call is resolved to:
         * \H5peditor->getLibraryData($machineName, $majorVersion, $minorVersion, $languageCode, $prefix = '', $fileDir = '')
         * @see \H5peditor::getLibraryData()
         */
        $this->h5pEditor->ajax->action(
            \H5PEditorEndpoints::SINGLE_LIBRARY,
            $queryArguments['machineName'],
            $queryArguments['majorVersion'],
            $queryArguments['minorVersion'],
            'en', // TODO make configurable
            '',
            $this->h5pPublicFolderPath
        );
        // TODO: this probably has to do with output buffering - if we remove the "die", nothing is returned if errors occur.
        die;
        return false;
    }

    /**
     * @param array $libraries
     * @Flow\SkipCsrfProtection
     * @return bool
     */
    public function librariesAction($libraries = null)
    {
        /**
         * This call is resolved to:
         * @see \H5peditor::getLibraries()
         * This method expects $_POST['libraries'] to be set, so we need to fake it.
         */
        $_POST['libraries'] = $libraries;
        $this->h5pEditor->ajax->action(\H5PEditorEndpoints::LIBRARIES);
        // TODO: this probably has to do with output buffering - if we remove the "die", nothing is returned if errors occur.
        die;
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
