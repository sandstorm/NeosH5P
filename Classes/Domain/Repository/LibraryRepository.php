<?php
namespace Sandstorm\NeosH5P\Domain\Repository;

use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\Library;

/**
 * @Flow\Scope("singleton")
 */
class LibraryRepository extends Repository
{

    /**
     * @param $id
     */
    public function removeByLibraryId($id)
    {
        $library = $this->findOneByLibraryId($id);
        if ($library !== null) {
            $this->remove($library);
        }
    }

    public function findLibrariesWithNewerVersion(Library $library)
    {
        $query = $this->createQuery();

        $query->getQueryBuilder()
            ->where('e.name = ?0 AND e.libraryId != ?1 AND (e.majorVersion > ?2 OR (e.majorVersion = ?2 AND e.minorVersion > ?3))')
            ->setParameters([
                $library->getName(),
                $library->getLibraryId(),
                $library->getMajorVersion(),
                $library->getMinorVersion()
            ]);

        return $query->execute();
    }

    /**
     * @param string $libraryName
     * @param int $majorVersion
     * @param int $minorVersion
     * @return Library
     */
    public function findOneByNameMajorVersionAndMinorVersion(string $libraryName, int $majorVersion, int $minorVersion)
    {
        return $this->findOneBy([
            'name' => $libraryName,
            'majorVersion' => $majorVersion,
            'minorVersion' => $minorVersion
        ]);
    }

    /**
     * @return array
     */
    public function getLibraryAddons()
    {
        $query = $this->createQuery();

        // Load addons
        // If there are several versions of the same addon, pick the newest one
        $query->getQueryBuilder()
            ->select('l1.libraryId, l1.name AS machineName, l1.majorVersion, l1.minorVersion, l1.patchVersion, l1.addTo, l1.preloadedJs, l1.preloadedCss')
            ->from(Library::class, 'l1')
            ->leftJoin(Library::class, 'l2', 'ON', 'ON l1.name = l2.name AND
            (l1.majorVersion < l2.majorVersion OR
              (l1.majorVersion = l2.majorVersion AND
               l1.majorVersion < l2.mimajorVersionnor_version))')
            ->where('l1.addTo IS NOT NULL AND l2.name IS NULL');

        return $query->execute()->toArray();
    }

}
