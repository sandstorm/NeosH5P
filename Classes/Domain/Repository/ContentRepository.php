<?php
namespace Sandstorm\NeosH5P\Domain\Repository;

use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;
use Sandstorm\NeosH5P\Domain\Model\Library;

/**
 * @Flow\Scope("singleton")
 */
class ContentRepository extends Repository
{

    /**
     * @var array
     */
    protected $defaultOrderings = ['contentId' => QueryInterface::ORDER_DESCENDING];

    /**
     * @param Library $library
     * @return QueryResultInterface
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
     * @param string $title
     * @param string $orderBy
     * @param string $orderDirection
     * @return QueryResultInterface
     */
    public function findByContainsTitle($title, $orderBy, $orderDirection)
    {
        $query = $this->createQuery();

        $query->getQueryBuilder()
            ->where('e.title LIKE :title')
            ->orderBy('e.' . $orderBy, $orderDirection)
            ->setParameters(['title' => '%' . $title . '%']);

        return $query->execute();
    }

    /**
     * @param $id
     */
    public function removeByContentId($id)
    {
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
