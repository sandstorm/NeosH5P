<?php

namespace Sandstorm\NeosH5P\Command;

use Neos\Flow\Cli\CommandController;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\Request;
use Neos\Flow\Cli\Response;
use Neos\Flow\Exception;
use Neos\Flow\Mvc\Dispatcher;
use Neos\Utility\Files;
use Sandstorm\NeosH5P\Domain\Model\EditorTempfile;
use Sandstorm\NeosH5P\Domain\Repository\CachedAssetRepository;
use Sandstorm\NeosH5P\Domain\Repository\ConfigSettingRepository;
use Sandstorm\NeosH5P\Domain\Repository\EditorTempfileRepository;
use Sandstorm\NeosH5P\H5PAdapter\Core\H5PFramework;

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
     * @var ConfigSettingRepository
     * @Flow\Inject
     */
    protected $configSettingRepository;

    /**
     * @var H5PFramework
     * @Flow\Inject
     */
    protected $h5pFramework;

    /**
     * @Flow\Inject
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @Flow\Inject
     * @var CachedAssetRepository
     */
    protected $cachedAssetRepository;

    /**
     * @Flow\Inject
     * @var EditorTempfileRepository
     */
    protected $editorTempfileRepository;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="configSettings")
     */
    protected $configSettings;

    /**
     * Clears all EditorTempfiles from the database and file system.
     */
    public function clearEditorTempFilesCommand()
    {
        /** @var EditorTempfile $editorTempFile */
        foreach ($this->editorTempfileRepository->findAll() as $editorTempFile) {
            $this->outputLine("Removing " . $editorTempFile->getResource()->getFilename());
            $this->editorTempfileRepository->remove($editorTempFile);
        }
        $this->outputLine("Done.");
    }

    /**
     * Installs a library via the H5P Hub.
     *
     * @param string $machineName
     * @throws Exception
     */
    public function installLibraryCommand(string $machineName)
    {
        // Setup, needed for CLI request
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Start the library installation
        $this->h5peditor->ajax->action(\H5PEditorEndpoints::LIBRARY_INSTALL, 'dummy', $machineName);

        $this->outputLine("");
        $this->outputLine("=======================================");
        $this->outputLine("Done installing libraries.");

        // Publish the h5p library collection so the library files are made available
        $cliRequest = new Request();
        $cliRequest->setControllerObjectName('Neos\Flow\Command\ResourceCommandController');
        $cliRequest->setControllerCommandName('publish');
        $cliRequest->setArguments([
            'collection' => 'h5p-libraries'
        ]);
        $cliResponse = new Response();
        $this->dispatcher->dispatch($cliRequest, $cliResponse);
    }

    /**
     * Uploads a H5P into the system.
     *
     * @param string $fileName
     * @throws Exception
     */
    public function importFileCommand(string $fileName)
    {
        if (! file_exists($fileName)) {
            $this->outputLine('The file "' . $fileName . '" does not exist.');
            $this->quit(1);
        }

        // Setup, needed for CLI request
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Copy the file to a temporary location, as the h5p core actually deletes it after import.
        $copyPath = FLOW_PATH_DATA . 'Temporary/H5P/__import';
        $copyFilename = $copyPath . '/currentimport.h5p';
        Files::createDirectoryRecursively($copyPath);
        copy($fileName, $copyFilename);

        // Start the library import
        $this->h5peditor->ajax->action(\H5PEditorEndpoints::LIBRARY_UPLOAD, 'dummy', $copyFilename, null);

        $this->outputLine("");
        $this->outputLine("=======================================");
        $this->outputLine("Done importing H5P file.");

        // Publish the h5p library collection so the library files are made available
        $cliRequest = new Request();
        $cliRequest->setControllerObjectName('Neos\Flow\Command\ResourceCommandController');
        $cliRequest->setControllerCommandName('publish');
        $cliRequest->setArguments([
            'collection' => 'h5p-libraries'
        ]);
        $cliResponse = new Response();
        $this->dispatcher->dispatch($cliRequest, $cliResponse);

        // Publish the h5p content collection so the library files are made available
        $cliRequest = new Request();
        $cliRequest->setControllerObjectName('Neos\Flow\Command\ResourceCommandController');
        $cliRequest->setControllerCommandName('publish');
        $cliRequest->setArguments([
            'collection' => 'h5p-content'
        ]);
        $cliResponse = new Response();
        $this->dispatcher->dispatch($cliRequest, $cliResponse);
    }

    /**
     * Refreshes the library cache from the H5P hub
     */
    public function refreshContentTypeCacheCommand()
    {
        $this->h5pCore->updateContentTypeCache();
        $this->outputLine('Content Type Cache update finished.');
    }

    /**
     * Generates config values into the database from the ones given in Settings.yaml.
     */
    public function generateConfigCommand()
    {
        $this->outputLine('Making the following config settings:');
        foreach ($this->configSettings as $key => $value) {
            $this->h5pFramework->setOption($key, $value);
            $this->outputLine("<b>$key:</b> $value");
        }
    }

    /**
     * Removes all CachedAssets
     */
    public function clearCachedAssetsCommand()
    {
        $this->cachedAssetRepository->removeAll();
        $this->outputLine('Removed all cached assets.');
    }
}
