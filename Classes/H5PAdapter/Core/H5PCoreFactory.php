<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Core;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class H5PCoreFactory
{
    /**
     * @var H5PFramework
     * @Flow\Inject(lazy=false)
     */
    protected $h5pFrameworkInterface;
    /**
     * @var boolean
     * @Flow\InjectConfiguration(path="aggregateAssets")
     */
    protected $aggregateAssets;
    /**
     * @var boolean
     * @Flow\InjectConfiguration(path="enableExport")
     */
    protected $enableExport;

    public function getCore(string $h5pPublicFolderUrl): \H5PCore
    {
        $core = new \H5PCore(
            $this->h5pFrameworkInterface,
            new FileAdapter(),
            $h5pPublicFolderUrl,
            'en', // We only support english for now
            $this->enableExport);

        // control asset aggregation
        $core->aggregateAssets = $this->aggregateAssets;

        return $core;
    }
}
