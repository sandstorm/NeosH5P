<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class ConfigSetting {

    /**
     * @ORM\Id
     * @var string
     */
    protected $configKey;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $configValue;

    /**
     * ConfigSetting constructor.
     * @param string $key
     * @param string $value
     */
    public function __construct(string $key, string $value)
    {
        $this->configKey = $key;
        $this->configValue = $value;
    }

    /**
     * @return string
     */
    public function getConfigKey(): string
    {
        return $this->configKey;
    }

    /**
     * @param string $configKey
     */
    public function setConfigKey(string $configKey)
    {
        $this->configKey = $configKey;
    }

    /**
     * @return string
     */
    public function getConfigValue(): string
    {
        return $this->configValue;
    }

    /**
     * @param string $configValue
     */
    public function setConfigValue(string $configValue): void
    {
        $this->configValue = $configValue;
    }
}
