<?php

namespace Sandstorm\NeosH5P\Domain\Repository;

use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;
use Sandstorm\NeosH5P\Domain\Model\Content;

/**
 * @Flow\Scope("singleton")
 */
class ContentUserDataRepository extends Repository
{
    public function findOneByContentAccountAndDataId(Content $content, Account $account, string $dataId)
    {
        return $this->findOneBy(['content' => $content, 'account' => $account, 'dataId' => $dataId]);
    }

    public function findByContentAndAccount(Content $content, Account $account)
    {
        return $this->findBy(['content' => $content, 'account' => $account]);
    }
}
