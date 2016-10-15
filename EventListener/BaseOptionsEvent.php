<?php
/**
 * Copyright (c) 2015 Afrihost Internet Services (Pty) Ltd
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Afrihost\BaseCommandBundle\EventListener;

use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class BaseOptionsEvent
{
    use ContainerAwareTrait;

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();

        /** @var Application $application */
        $application = $command->getApplication();
        $inputDefinition = $command->getDefinition();

        if ($command instanceof HelpCommand) {
            $input = new ArgvInput();
            $input->bind($inputDefinition);

            $command = $application->find($input->getFirstArgument());
        }

        if ($command instanceof BaseCommand) {
            $command->setLogLevel($this->container->getParameter('afrihost_base_command.logger.log_level'));
            $this->setInputDefinition($inputDefinition);
        }
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