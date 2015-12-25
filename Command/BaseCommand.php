<?php
/**
 * Copyright (c) 2015 Afrihost Internet Services (Pty) Ltd
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Afrihost\BaseCommandBundle\Command;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Base class that commands in other bundles can extend from in order to get generic functionality (such as logging)
 */
abstract class BaseCommand extends ContainerAwareCommand
{

    /**
     * @var Logger
     */
    private $logger = null;

    /**
     * @var int
     */
    private $logLevel = Logger::WARNING;

    /**
     * @var bool
     */
    private $logToConsole = true;

    /**
     * @var string
     */
    private $logFilename = null;

    /**
     * @var string
     */
    private $filename = null;

    /**
     * @var LockHandler
     */
    private $lockhandler;

    /**
     * Provides default options for all commands. This function should be called explicitly (i.e. parent::configure())
     * if the configure function is overridden.
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->addOption('log-level', 'l', InputOption::VALUE_REQUIRED,
                'Override the Monolog logging level for this execution of the command. Valid values: ' . implode(',', array_keys(Logger::getLevels())))
            ->addOption('locking', null, InputOption::VALUE_REQUIRED, 'Switches locking on/off')
        ;
    }

    /**
     * Function that will be called at the start of initialize, to validate the standard input given
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function validate(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('locking') !== null) {
            $validLockingOptions = array('on', 'off');
            if (!in_array(strtolower($input->getOption('locking')), $validLockingOptions)) {
                throw new ValidatorException(
                    'Validation failed for input option \'locking\'. ' . PHP_EOL .
                    'You specified "' . $input->getOption('locking') . '". ' . PHP_EOL .
                    'Valid options are: ' . implode(',', $validLockingOptions));
            }
        }
    }

    /**
     * Initialises the features of this class. This function should be called explicitly (i.e. parent::initialize())
     * if the initialize function is overridden.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->validate($input, $output);

        // Override production settings of not showing errors
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Reflect to get leaf-class:
        if (empty($this->filename)) {
            $reflectionClass = new \ReflectionClass($this);
            $this->filename = basename($reflectionClass->getFileName());
        }

        // Lock handler:
        if ($input->getOption('locking') !== 'off') {
            if (($input->getOption('locking') == 'on') || ($this->getContainer()->getParameter('afrihost_base_command.locking.enabled'))) {
                $this->lockhandler = new LockHandler($this->filename);
                if (!$this->lockhandler->lock()) {
                    $output->writeln('<error>Sorry, can\'t get the lock. Bailing out!</error>');
                    exit;
                }
            }
        }

        //Initialize logger
        if (empty($this->logFilename)) {
            $this->setLogFilename($this->filename . $this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.default.file_extention'));
        }

        // Create formatter modelled after the Tools::SaveToLog()
        $formatter = new LineFormatter('%datetime% [%level_name%]: %message%' . PHP_EOL);
        // Log to file
        $fileHandler = new StreamHandler($this->getLogFilename(), $this->getLogLevel());
        $fileHandler->setFormatter($formatter);
        $this->logger = new Logger(basename(__FILE__));
        $this->logger->pushHandler($fileHandler);
        // Log to console
        if ($this->isLogToConsole()) {
            $consoleHandler = new StreamHandler('php://stdout', $this->getLogLevel());
            $consoleHandler->setFormatter($formatter);
            $this->logger->pushHandler($consoleHandler);
        }

        // Override LogLevel to the once provided at runtime
        if ($input->hasOption('log-level')) {
            $overrideLevel = strtoupper($input->getOption('log-level'));
            if ($overrideLevel) {
                $loggerLevels = Logger::getLevels();
                $this->setLogLevel($loggerLevels[$overrideLevel]);
            }
        }
    }

    /**
     * Provides access to the logger object while maintaining its encapsulation so that all initialisation logic is done
     * in this class
     *
     * @return Logger
     * @throws \Exception
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            throw new \Exception('The logger is not yet initialised. Did you override the initialise function without calling the parent?');
        }

        return $this->logger;
    }

    /**
     * Provide a specific logfile name rather than using one automatically generated by the name strategy. This configuration
     * should be done before BaseCommand::initialize() is invoked
     *
     * @param string $logFilename
     *
     * @return BaseCommand
     * @throws \Exception
     */
    public function setLogFilename($logFilename)
    {
        if (!is_null($this->logger)) {
            throw new \Exception('Cannot set manual logfile name. Logger is already initialised');
        }

        $this->logFilename = $this->getContainer()->get('kernel')->getLogDir() . DIRECTORY_SEPARATOR . $logFilename;

        return $this;
    }

    /**
     * Returns the configured logfile name
     *
     * @return string
     */
    public function getLogFilename()
    {
        return $this->logFilename;
    }

    /**
     * Get the current integer Log level configured for this command
     *
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * Returns the RFC 5424 string name of the current log level
     *
     * @return string
     */
    public function getLevelName()
    {
        return Logger::getLevelName($this->logLevel);
    }

    /**
     * Set the log level for this command. If the log level is changed on the fly (i.e after the logger has been initialised)
     * he change in level will also be automatically logged
     *
     * @param int $logLevel a log level constant defined in Logger
     *
     * @throws \Exception
     */
    protected function setLogLevel($logLevel)
    {
        if (!in_array($logLevel, Logger::getLevels())) {
            $message = "'" . $logLevel . "' is not a valid LOGLEVEL. Valid values are: " . implode(',', array_keys(Logger::getLevels()));
            throw new \Exception($message);
        }

        $this->logLevel = $logLevel;

        // Note in log that log level has been changed if the logger has already been initialised
        if (!is_null($this->logger)) {
            /* @var $handler AbstractHandler */
            foreach ($this->getLogger()->getHandlers() as $handler) {
                $handler->setLevel($logLevel);
            }
            $this->getLogger()->emergency('LOG LEVEL CHANGED: ' . Logger::getLevelName($logLevel));
        }
    }

    /**
     * Whether or not this  command is configure to send a copy of the log output to STDOUT
     *
     * @return boolean
     */
    protected function isLogToConsole()
    {
        return $this->logToConsole;
    }

    /**
     * Configure whether to send a copy of the log output to STDOUT. This configuration should be done before
     * BaseCommand::initialize() is invoked
     *
     * @param boolean $logToConsole
     *
     * @throws \Exception
     */
    protected function setLogToConsole($logToConsole)
    {
        if (!is_null($this->logger)) {
            throw new \Exception('Cannot ' . (($logToConsole) ? 'enable' : 'disable') . ' console logging. Logger is already initialised');
        }

        if (!is_bool($logToConsole)) {
            throw new \Exception('LogToConsole setting must be a boolean');
        }

        $this->logToConsole = $logToConsole;
    }
}
