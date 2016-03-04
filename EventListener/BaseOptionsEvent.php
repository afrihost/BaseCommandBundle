<?php
/**
 * Copyright (c) 2015 Afrihost Internet Services (Pty) Ltd
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Afrihost\BaseCommandBundle\EventListener;

use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class BaseOptionsEvent
{
    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        /** @var Application $application */
        $application = $event->getCommand()->getApplication();

        $this->setCommandDefinition($application);
    }

    /**
     * @param Application $application
     *
     * @return ArgvInput
     */
    private function setCommandDefinition(Application $application)
    {
        $inputDefinition = $application->getDefinition();

        $this->setInputDefinition($inputDefinition);

        $application->setDefinition($inputDefinition);
    }

    /**
     * @param InputDefinition $inputDefinition
     */
    private function setInputDefinition(InputDefinition $inputDefinition)
    {
        $inputDefinition->addOption(
            new InputOption(
                'log-level',
                'l',
                InputOption::VALUE_REQUIRED,
                'Override the Monolog logging level for this execution of the command. Valid values: ' .
                implode(', ', array_keys(Logger::getLevels()))
            )
        );

        $inputDefinition->addOption(
            new InputOption(
                'log-filename',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify a different file (relative to the kernel log directory) to send file logs to'
            )
        );

        $inputDefinition->addOption(
            new InputOption(
                'locking',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether or not this execution needs to acquire a '.
                ' lock that ensures that the command is only being run once concurrently. Valid values: on, off'
            )
        );
    }
}