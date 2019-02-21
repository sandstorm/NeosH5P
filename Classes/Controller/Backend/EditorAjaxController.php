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
     * @Flow\Inject(lazy=false)
     * @var \H5PCore
     */
    protected $h5pCore;

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

    public function contentTypeCacheAction()
    {
        $this->h5pEditor->ajax->action(\H5PEditorEndpoints::CONTENT_TYPE_CACHE);

        /**
         * We need the "exit" here because otherwise Flow will set content headers that don't fit what
         * H5P does. We can't influence H5P here, so we'll just live with this.
         * @see \H5PCore::ajaxError()
         * @see \H5PCore::ajaxSuccess()
         */
        exit;
    }

    /**
     * @Flow\SkipCsrfProtection
     */
    public function installLibraryAction()
    {
        $libraryId = $this->request->getArguments()['id'];
        $this->h5pEditor->ajax->action(\H5PEditorEndpoints::LIBRARY_INSTALL, 'dummy', $libraryId);
        // Publish the "libraries" collection so we get the unzipped file
        $libraryCollection = $this->resourceManager->getCollection('h5p-libraries');
        $libraryCollection->getTarget()->publishCollection($libraryCollection);

        // See above
        exit;
    }

    public function libraryDetailsAction()
    {
        $queryArguments = $this->request->getArguments();

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

        // See above
        exit;
    }

    /**
     * @param array $libraries
     * @Flow\SkipCsrfProtection
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

        // See above
        exit;
    }

    /**
     * @param string $queryString
     * @Flow\SkipCsrfProtection
     */
    public function fileUploadAction()
    {
        /**
         * This call is resolved to:
         * @see \H5peditorAjax::fileUpload()
         */
        // $this->h5pEditor->ajax->action(\H5PEditorEndpoints::FILES, 'dummy', null);

        /**
         * !!! We can't use the public API because it contains a bug in the fileUpload method
         * where $this->storage->markFileForCleanup($file_id); is called with 1 parameter even though
         * it expects 2. This is fixed on master, but not released yet. Therefore we make the same calls
         * here that \H5peditorAjax::fileUpload() makes, working around the bug.
         */
        $file = new \H5peditorFile($this->h5pFramework);
        if (!$file->isLoaded()) {
            \H5PCore::ajaxError($this->h5pFramework->t('File not found on server. Check file upload settings.'));
            return;
        }

        // Make sure file is valid and mark it for cleanup at a later time
        if ($file->validate()) {
            $file_id = $this->h5pCore->fs->saveFile($file, 0);
            $this->h5pEditor->ajax->storage->markFileForCleanup($file_id, 0);
        }
        $file->printResult();

        // See above
        exit;
    }
}
