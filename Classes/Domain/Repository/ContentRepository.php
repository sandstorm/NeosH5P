<?php
namespace Sandstorm\NeosH5P\Domain\Repository;

use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class ContentRepository extends Repository {

    /**
     * @param $id
     */
    public function removeByContentId($id) {
        $content = $this->findOneByContentId($id);
        if ($content !== null) {
            $this->remove($content);
        }
    }

}
