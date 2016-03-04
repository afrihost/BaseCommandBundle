<?php

/**
 * Copyright (c) 2015 Afrihost Internet Services (Pty) Ltd
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Afrihost\BaseCommandBundle\EventListener;

use Afrihost\BaseCommandBundle\Event\ConsoleEvents as CustomEvents;
use Afrihost\BaseCommandBundle\Exceptions\BaseCommandException;
use Afrihost\BaseCommandBundle\Exceptions\LockAcquireException;
use Afrihost\BaseCommandBundle\Helper\Locking\LockingEnhancement;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LockingEvent implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $commands;

    /**
     * @var LockingEnhancement
     */
    private $lockingHandler;

    /**
     * @var bool
     */
    private $lockingEnabled;

    /**
     * @param array              $commands
     * @param bool               $lockingEnabled
     * @param LockingEnhancement $lockingHandler
     */
    public function __construct(array $commands, $lockingEnabled, LockingEnhancement $lockingHandler)
    {
        $this->commands = $commands;
        $this->lockingHandler = $lockingHandler;
        $this->lockingEnabled = $lockingEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ConsoleEvents::COMMAND => 'onConsoleCommand',
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',
            CustomEvents::INITIALIZE => 'onConsoleInitialize',
        );
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $input = new ArgvInput();
        $input->bind($event->getCommand()->getApplication()->getDefinition());

        $command = $event->getCommand();

        // Locking Settings
        $defaultValue = in_array($command->getName(), $this->commands, true) ?: $this->lockingEnabled;

        $this->setLockingFromCommandOptions($input, $defaultValue);
    }

    /**
     * @param ConsoleEvent $event
     *
     * @throws LockAcquireException
     */
    public function onConsoleInitialize(ConsoleEvent $event)
    {
        $this->lockingHandler->initialize($event->getInput(), $event->getOutput());
    }

    /**
     * This event is called whenever a console command is terminated, whether it is when the command is done executing
     * or when an exception occurs. This is so that we can release the lock at all times, and not have dangling lock files
     * in the case a command did not complete
     *
     * @param ConsoleTerminateEvent $event
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        $this->lockingHandler->postRun($event->getInput(), $event->getOutput(), $event->getExitCode());
    }

    /**
     * @param InputInterface $input
     * @param bool           $defaultValue
     *
     * @throws BaseCommandException
     */
    private function setLockingFromCommandOptions(InputInterface $input, $defaultValue)
    {
        // Locking parameters
        if ($input->getOption('locking') !== null) {
            $lockingInput = strtolower($input->getOption('locking'));

            $validLockingOptions = array('on', 'off');
            if (!in_array($lockingInput, $validLockingOptions, true)) {
                throw new BaseCommandException(
                    'Invalid value for \'--locking\' parameter. ' . 'You specified "' . $lockingInput . '". ' .
                    'Valid values are: ' . implode(',', $validLockingOptions));
            }

            $this->lockingHandler->setLocking($lockingInput === 'on');
        } else {
            $this->lockingHandler->setLocking($defaultValue);
        }
    }
}