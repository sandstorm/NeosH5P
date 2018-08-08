<?php

namespace Sandstorm\NeosH5P\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Library;
use Sandstorm\NeosH5P\H5PAdapter\Core\H5PFramework;

/**
 * @Flow\Scope("singleton")
 */
class LibraryUpgradeService
{

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
     * @param Library $library
     * @return bool
     */
    public function upgradeAvailable(Library $library)
    {
        if ($library->getContents()->count() === 0) return false;

        $installedLibraries = $this->h5pFramework->loadLibraries();
        $availableUpgrades = $this->h5pCore->getUpgrades($library->toStdClass(), $installedLibraries[$library->getName()]);

        return count($availableUpgrades) > 0;
    }

}
