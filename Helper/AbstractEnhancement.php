<?php

namespace Afrihost\BaseCommandBundle\Helper;

use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;

/**
 * Most enhancements will need access to the command that they augment and its config. This base class provides this
 */
abstract class AbstractEnhancement implements EnhancementInterface
{
    /**
     * @var BaseCommand
     */
    protected $command;

    /**
     * @var RuntimeConfig
     */
    protected $runtimeConfig;

    /**
     * AbstractEnhancement constructor.
     *
     * @param BaseCommand   $command
     * @param RuntimeConfig $runtimeConfig
     */
    public function __construct(BaseCommand $command, RuntimeConfig $runtimeConfig)
    {
        $this->command = $command;
        $this->runtimeConfig = $runtimeConfig;
    }

    /**
     * @return BaseCommand
     */
    protected function getCommand()
    {
        return $this->command;
    }

    /**
     * @return RuntimeConfig
     */
    protected function getRuntimeConfig()
    {
        return $this->runtimeConfig;
    }

    /**
     * Returns the name of the PHP file in which the User of the library's Command is defined
     *
     * @return string
     */
    protected function getUserCommandClassFilename()
    {
        $reflectionClass = new \ReflectionClass($this->getCommand());
        return basename($reflectionClass->getFileName());
    }

}