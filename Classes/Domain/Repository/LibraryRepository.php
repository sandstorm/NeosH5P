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
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e.libraryId, e.name AS machineName, e.majorVersion, e.minorVersion, e.patchVersion, e.addTo, e.preloadedJs, e.preloadedCss')
            ->from(Library::class, 'e')
            ->leftJoin(
                Library::class,
                'l2',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'e.name = l2.name AND
                    (e.majorVersion < l2.majorVersion OR
                        (e.majorVersion = l2.majorVersion AND e.minorVersion < l2.minorVersion)
                    )'
            )
            ->where('e.addTo IS NOT NULL AND l2.name IS NULL');

        return $qb->getQuery()->execute();
    }

}
