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

}
