<?php
namespace Sandstorm\NeosH5P\Domain\Repository;

use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Library;

/**
 * @Flow\Scope("singleton")
 */
class LibraryRepository extends Repository {
    /**
     * Finds a library from a string like
     * "H5P.MultiChoice 1.12"
     *
     * @param string $nameAndVersionString
     * @return Library
     */
    public function findOneByNameAndVersionString(string $nameAndVersionString){
        $parts = explode(' ', $nameAndVersionString);
        $libraryName = $parts[0];
        $versionParts = explode('.', $parts[1]);
        $majorVersion = $versionParts[0];
        $minorVersion = $versionParts[1];

        return $this->findOneBy([
            'name' => $libraryName,
            'majorVerion' => $majorVersion,
            'minorVersion' => $minorVersion
        ]);
    }
}
