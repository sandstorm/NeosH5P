<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * @Flow\Entity
 */
class ConfigSetting {

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $value;

}
