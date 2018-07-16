<?php

namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Security\Account;

/**
 * @Flow\Entity
 */
class ContentResult
{

    /**
     * @var Content
     * @ORM\ManyToOne(inversedBy="contentResults")
     * @ORM\Column(nullable=false)
     */
    protected $content;

    /**
     * @var Account
     * @ORM\ManyToOne
     * @ORM\Column(nullable=false)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $account;

    /**
     * @var int
     * @ORM\Column(nullable=false)
     */
    protected $score;

    /**
     * @var int
     * @ORM\Column(nullable=false)
     */
    protected $maxScore;

    /**
     * @var int
     * @ORM\Column(nullable=false)
     */
    protected $opened;

    /**
     * @var int
     * @ORM\Column(nullable=false)
     */
    protected $finished;

    /**
     * @var int
     * @ORM\Column(nullable=false)
     */
    protected $time;

    public function __construct(Content $content, Account $account, int $score, int $maxScore, int $opened, int $finished, int $time)
    {
        $this->setContent($content);
        $this->setAccount($account);
        $this->setScore($score);
        $this->setMaxScore($maxScore);
        $this->setOpened($opened);
        $this->setFinished($finished);
        $this->setTime($time);
    }

    /**
     * @return Content
     */
    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * @param Content $content
     */
    public function setContent(Content $content): void
    {
        $this->content = $content;
    }

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param Account $account
     */
    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @param int $score
     */
    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    /**
     * @return int
     */
    public function getMaxScore(): int
    {
        return $this->maxScore;
    }

    /**
     * @param int $maxScore
     */
    public function setMaxScore(int $maxScore): void
    {
        $this->maxScore = $maxScore;
    }

    /**
     * @return int
     */
    public function getOpened(): int
    {
        return $this->opened;
    }

    /**
     * @param int $opened
     */
    public function setOpened(int $opened): void
    {
        $this->opened = $opened;
    }

    /**
     * @return int
     */
    public function getFinished(): int
    {
        return $this->finished;
    }

    /**
     * @param int $finished
     */
    public function setFinished(int $finished): void
    {
        $this->finished = $finished;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @param int $time
     */
    public function setTime(int $time): void
    {
        $this->time = $time;
    }

}
