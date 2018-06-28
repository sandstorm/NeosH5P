<?php

namespace Sandstorm\NeosH5P\Domain\Repository;

use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Annotations as Flow;
use Sandstorm\NeosH5P\Domain\Model\ContentTypeCacheEntry;

/**
 * @Flow\Scope("singleton")
 */
class ContentTypeCacheEntryRepository extends Repository
{
    /**
     * Returns all cache entries as an array of stdObjects, the way the H5P core
     * expects it.
     *
     * @return array
     */
    public function getContentTypeCacheObjects(): array
    {
        $cacheEntries = [];
        /** @var ContentTypeCacheEntry $contentTypeCacheEntry */
        foreach ($this->findAll() as $contentTypeCacheEntry) {
            $cacheEntries[] = $contentTypeCacheEntry->toStdClass();
        }
        return $cacheEntries;
    }
}
