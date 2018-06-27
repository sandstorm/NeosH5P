<?php

namespace Sandstorm\NeosH5P\Command;

use Neos\Flow\Cli\CommandController;
use Neos\Flow\Annotations as Flow;

class H5PCommandController extends CommandController
{
    /**
     * @var \H5peditor
     * @Flow\Inject(lazy=false)
     */
    protected $h5peditor;

    /**
     * @var \H5PCore
     * @Flow\Inject(lazy=false)
     */
    protected $h5pCore;

    /**
     * Clears all EditorTempfiles from the database and file system.
     */
    public function cleanEditorTempFilesCommand()
    {
        $this->outputLine("TODO");
    }

    /**
     * Installs a library via the H5P Hub.
     *
     * @param string $machineName
     */
    public function installLibraryCommand(string $machineName)
    {
        // Setup, needed for CLI request
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Start the library import
        $this->h5peditor->ajax->action(\H5PEditorEndpoints::LIBRARY_INSTALL, 'dummy', $machineName);
    }

    /**
     * Refreshes the library cache from the H5P hub
     */
    public function refreshContentTypeCacheCommand()
    {
        $this->h5pCore->updateContentTypeCache();
        $this->outputLine('Content Type Cache update finished.');
    }

}
