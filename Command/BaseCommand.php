<?php
/**
 * Copyright (c) 2015 Afrihost Internet Services (Pty) Ltd
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Afrihost\BaseCommandBundle\Command;

use Afrihost\BaseCommandBundle\Exceptions\LockAcquireException;
use Afrihost\BaseCommandBundle\Helper\Logging\Handler\ConsoleHandler;
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
    private $lockHandler;

    /**
     * @var bool
     */
    private $locking;

    /**
     * @var string
     */
    private $lockFileFolder;

    /**
     * @var string
     */
    private $memoryLimit;

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
            ->addOption('locking', null, InputOption::VALUE_REQUIRED, 'Switches locking on/off');
    }

    /**
     * Function that will be called at the start of initialize, to validate the standard input given
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function validate(InputInterface $input, OutputInterface $output)
    {
        // TODO calling getOption will error if parent::configure() not called in user's overridden function
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

        // Reflect to get leaf-class:
        if (empty($this->filename)) {
            $reflectionClass = new \ReflectionClass($this);
            $this->filename = basename($reflectionClass->getFileName());
        }

        // Lock handler:
        if ($input->getOption('locking') !== 'off') {
            if (($input->getOption('locking') == 'on') || ($this->isLocking())) {
                $this->lockHandler = new LockHandler($this->filename, $this->getLockFileFolder());
                if (!$this->lockHandler->lock()) {
                    throw new LockAcquireException('Sorry, can\'t get the lock. Bailing out!');
                }
                // TODO Decide on output option here (possibly option to log instead of polluting STDOUT)
                //$output->writeln('<info>LOCK Acquired</info>');
            }
        }

        //Initialize logger
        if (empty($this->logFilename)) {
            // TODO Add test coverage for file extension
            $this->setLogFilename($this->filename . $this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.default.file_extention'));
        }

        // The logger is always going to be available, whether we have handlers or not:
        $this->logger = new Logger(basename(__FILE__));
        $this->setupFileLogHandler();
        $this->setupConsoleLogHandler($output);

        // Override LogLevel to the once provided at runtime
        if ($input->hasOption('log-level')) {
            $overrideLevel = strtoupper($input->getOption('log-level'));
            if ($overrideLevel) {
                $loggerLevels = Logger::getLevels();
                $this->setLogLevel($loggerLevels[$overrideLevel]);
            }
        }

        // Override production settings of not showing errors
        error_reporting(E_ALL);
        $this->setDisplayErrors(true);

        // PHP Memory Limit:
        if ($this->getMemoryLimit() !== null) {
            $this->setMemoryLimit($this->getMemoryLimit());
        }
    }

    /**
     * Override framework function to add pre and post hooks around the parent functionality
     *
     * @param InputInterface  $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return int The command exit code
     *
     * @throws \Exception
     *
     * @see setCode()
     * @see execute()
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->preRun($output);
        $exitCode =  parent::run($input, $output);
        $this->postRun($input, $output, $exitCode);

        return $exitCode;
    }


    protected function preRun(OutputInterface $output)
    {

    }

    protected function postRun(InputInterface $input, OutputInterface $output, $exitCode)
    {
        // Release lock if set
        if(!is_null($this->lockHandler)){
            $this->lockHandler->release();
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
     * Returns the full configured logfile name (including path)
     * TODO decide whether this should return the full path or if it should be symmetric with setLogFilename
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
     * @return $this
     * @throws \Exception
     */
    protected function setLogLevel($logLevel)
    {
        if (!in_array($logLevel, Logger::getLevels())) {
            // TODO The values provided here are only valid on command line, but not when the command is manually invoked
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

            //TODO make this log entry configurable (turn off and choose log level)
            $this->getLogger()->emergency('LOG LEVEL CHANGED: ' . Logger::getLevelName($logLevel));
        }

        return $this;
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
     * @return $this
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

        return $this;
    }

    /**
     * Used to override the default locking as configured in config.yml.
     * This is used when the user has, for example, locking off by default in config.yml for his/her entire application
     * but wishes to have the default on for this particular command.
     *
     * @param bool $value
     *
     * @return $this
     * @throws \Exception
     */
    public function setLocking($value)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException('Value passed to ' . __FUNCTION__ . ' should be of type boolean');
        }

        if (!is_null($this->lockHandler)) {
            throw new \Exception('Cannot ' . (($value) ? 'enable' : 'disable') . ' locking. Lock handler is already initialised');
        }

        $this->locking = $value;

        return $this;
    }

    /**
     * Whether locking is enabled for this command
     *
     * @return bool
     */
    protected function isLocking()
    {
        if (!isset($this->locking)) {
            $this->locking = $this->getContainer()->getParameter('afrihost_base_command.locking.enabled');
        }

        return $this->locking;
    }

    /**
     * Used to override the default folder where your lock-files are stored. Suggestion: app/storage/lockfiles.
     * The default will go to the system folder for this purpose.
     * If the folder starts with / or ~/ we assume you have a static location for it.
     * If the folder doesn't start with / or ~/ we will assume the folder is relative to your symfony app root directory.
     *
     * @param string $lockFileFolder
     * @return $this
     */
    public function setLockFileFolder($lockFileFolder)
    {
        $this->lockFileFolder = $lockFileFolder;

        return $this;
    }

    /**
     * Gets the folder where the lockfiles will be stored.
     *
     * @return string
     */
    protected function getLockFileFolder()
    {
        if (!isset($this->lockFileFolder)) {
            $this->lockFileFolder = $this->getContainer()->getParameter('afrihost_base_command.locking.lock_file_folder');
        }

        // Empty / Null - lockfiles will go to system default location:
        if (is_null($this->lockFileFolder) || empty($this->lockFileFolder)) {
            return $this->lockFileFolder;
        }

        // Relative path handling:
        if (substr($this->lockFileFolder, 0, 1) !== '/' && substr($this->lockFileFolder, 0, 2) !== '~/') {
            $this->lockFileFolder = $this->getContainer()->get('kernel')->getRootDir() . '/' . $this->lockFileFolder;
        }

        return $this->lockFileFolder;
    }

     /**
     * Sets up the StreamHandler - if it is enabled
     *
     * @return $this
     */
    private function setupFileLogHandler()
    {
        // Put in place the File StreamHandler:
        if (($this->getContainer()->hasParameter('afrihost_base_command.logger.handler_strategies.default.enabled')) &&
            ($this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.default.enabled') === true)
        ) {
            $fileHandler = new StreamHandler($this->getLogFilename(), $this->getLogLevel());
            $formatter = new LineFormatter($this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.default.line_format') . PHP_EOL);
            $fileHandler->setFormatter($formatter);
            $this->logger->pushHandler($fileHandler);

        }

        return $this;
    }

    /**
     * Sets up LogToConsole handler
     *
     * @param OutputInterface $output
     * @return $this
     */
    private function setupConsoleLogHandler(OutputInterface $output)
    {
        // Log to console
        if ($this->isLogToConsole()) {
            $consoleHandler = new ConsoleHandler($output, $this->getLogLevel());
            $formatter = new LineFormatter($this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.console_stream.line_format') . PHP_EOL);
            $consoleHandler->setFormatter($formatter);
            $this->logger->pushHandler($consoleHandler);

        }

        return $this;
    }

    /**
     * Set the display_errors runtime configuration of PHP
     * @link http://php.net/manual/en/errorfunc.configuration.php#ini.display-errors
     *
     * @param bool|string $value
     *
     * @return $this
     * @throws \Exception
     */
    public function setDisplayErrors($value)
    {
        if (!is_bool($value) && $value != 'stderr' && !in_array($value, array(1, 2))) {
            throw new \Exception('Invalid value passed to setDisplayErrors. Value must be a boolean or the string \'stderr\'');
        }

        if ($value != 'stderr') {
            $value = ($value == true) ? '1' : '0'; // ini_get uses these for variations of on, off, true or false
        }

        $currentValue = ini_get('display_errors');
        if ($currentValue === (string)$value) {
            return $this; // don't do anything if th required value is already set
        }

        if ((!function_exists('ini_set') && (!is_null($this->logger)))) {
            $this->getLogger()->emergency('CANNOT SET DISPLAY ERRORS. PHP ini_set function is disabled in your environment.');

            return $this;
        }

        // TODO log if memory limit changed after initialisation

        // Actually set the value
        ini_set('display_errors', $value);

        if (ini_get('display_errors') == $currentValue && (!is_null($this->logger))) {
            $this->getLogger()->emergency('PHP display_errors setting could not be updated. This is likely as a result ' .
                'of the security configuration of your system');

        }

        return $this;
    }

    /**
     * Set the memory limit to use for PHP.
     * Integer in bytes, but shorthand is allowed: @link http://php.net/manual/en/faq.using.php#faq.using.shorthandbytes
     * To set unlimited memory, use -1
     * You may use anything valid at @link http://php.net/manual/en/ini.core.php#ini.memory-limit
     *
     * @param string $memoryLimit
     * @return BaseCommand
     */
    public function setMemoryLimit($memoryLimit)
    {
        if ((!function_exists('ini_set') && (!is_null($this->logger)))) {
            $this->getLogger()->emergency('CANNOT SET MEMORY LIMIT. PHP ini_set function is disabled in your environment. Limit unchanged!');

            return $this;
        }

        // TODO if no value is set in the config, the logging will not happen when we first use the function after initialisation
        if (isset($this->memoryLimit) && (!is_null($this->logger))) {
            $this->getLogger()->emergency('PHP MEMORY LIMIT CHANGING: from ' . $this->memoryLimit . ' to ' . $memoryLimit);
        }

        $this->memoryLimit = $memoryLimit;

        // Now actually set the php memory limit:
        if ($this->getMemoryLimit() !== null) {
            ini_set('memory_limit', $this->getMemoryLimit());
        }

        // Check if the limit was successfully set:
        if (($this->getMemoryLimit() != ini_get('memory_limit')) && (!is_null($this->logger))) {
            // TODO Test this using an environment on TravisCI with the Suhosin extension
            $this->getLogger()->emergency('PHP Memory Limit was not set. Expected: ' . $this->getMemoryLimit() . '. Check: ' . ini_get('memory_limit'));
        }

        return $this;
    }

    /**
     * Get the memory limit set either via config.yml, or via $this->setMemoryLimit()
     *
     * @return string
     */
    protected function getMemoryLimit()
    {
        if (!isset($this->memoryLimit)) {
            if ($this->getContainer()->hasParameter('afrihost_base_command.php.memory_limit')) {
                $this->memoryLimit = $this->getContainer()->getParameter('afrihost_base_command.php.memory_limit');
            }
        }

        return $this->memoryLimit;
    }

}
