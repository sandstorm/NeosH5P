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
     * @param $id
     */
    public function removeByLibraryId($id) {
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
            ->setParameters([$library->getName(), $library->getLibraryId(), $library->getMajorVersion(), $library->getMinorVersion()]);

        return $query->execute();
    }

}
