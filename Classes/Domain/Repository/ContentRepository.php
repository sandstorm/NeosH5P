<?php
namespace Sandstorm\NeosH5P\Domain\Repository;

use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryInterface;
use Sandstorm\NeosH5P\Domain\Model\Library;

/**
 * @Flow\Scope("singleton")
 */
class ContentRepository extends Repository {

    /**
     * @var array
     */
    protected $defaultOrderings = ['contentId' => QueryInterface::ORDER_DESCENDING];

    /**
     * @param Library $library
     * @return \Neos\Flow\Persistence\QueryResultInterface
     */
    public function findFirstTenContentsByLibrary(Library $library)
    {
        $query = $this->createQuery();

        $query->getQueryBuilder()
            ->where('e.library = ?0')->setMaxResults(10)
            ->setParameters([$library]);

        return $query->execute();
    }

    /**
     * @param $id
     */
    public function removeByContentId($id) {
        $content = $this->findOneByContentId($id);
        if ($content !== null) {
            $this->remove($content);
        }
    }

    /**
     * @param Library $library
     */
    public function countContents(Library $library)
    {
        $query = $this->createQuery();

        $query->getQueryBuilder()
            ->where('e.library = ?0')
            ->setParameters([$library]);

        return $query->execute()->count();
    }

}
