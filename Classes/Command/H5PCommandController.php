<?php

namespace Sandstorm\NeosH5P\Command;

use Neos\Flow\Cli\CommandController;

class H5PCommandController extends CommandController
{

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
        $this->outputLine($machineName);
    }

}
