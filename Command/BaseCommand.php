<?php
/**
 * Copyright (c) 2015 Afrihost Internet Services (Pty) Ltd
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Afrihost\BaseCommandBundle\Command;

use Afrihost\BaseCommandBundle\Exceptions\BaseCommandException;
use Afrihost\BaseCommandBundle\Exceptions\LockAcquireException;
use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Afrihost\BaseCommandBundle\Helper\Logging\LoggingEnhancement;
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
     * @var RuntimeConfig
     */
    private $runtimeConfig;

    /**
     * @var LoggingEnhancement
     */
    private $loggingEnhancement;

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
        $this->runtimeConfig = new RuntimeConfig($this);

        $this->advanceExecutionPhase(RuntimeConfig::PHASE_CONFIGURE);

        parent::configure();

        $this
            ->addOption('log-level', 'l', InputOption::VALUE_REQUIRED,
                'Override the Monolog logging level for this execution of the command. Valid values: ' . implode(',', array_keys(Logger::getLevels())))
            ->addOption('locking', null, InputOption::VALUE_REQUIRED, 'Switches locking on/off');

        $this->advanceExecutionPhase(RuntimeConfig::PHASE_POST_CONFIGURE);
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
     * @throws BaseCommandException
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->getRuntimeConfig()->loadGlobalConfigFromContainer($this->getContainer());

        $this->advanceExecutionPhase(RuntimeConfig::PHASE_LOAD_PARAMETERS);
        $this->getRuntimeConfig()->loadConfigFromCommandParameters($input);

        $this->advanceExecutionPhase(RuntimeConfig::PHASE_INITIALISE);

        parent::initialize($input, $output);

        $this->getLoggingEnhancement()->initialize($input, $output);

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

        // Override production settings of not showing errors
        error_reporting(E_ALL);
        $this->setDisplayErrors(true);

        // PHP Memory Limit:
        if ($this->getMemoryLimit() !== null) {
            $this->setMemoryLimit($this->getMemoryLimit());
        }

        $this->advanceExecutionPhase(RuntimeConfig::PHASE_POST_INITIALISE);
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
     * @throws BaseCommandException
     *
     * @see setCode()
     * @see execute()
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        if(is_null($this->runtimeConfig)){
            throw new BaseCommandException("If you override the 'configure()' function, you need to call parent::configure()".
                " in your overridden method in order for the BaseCommand to function correctly");
        }

        $this->advanceExecutionPhase(RuntimeConfig::PHASE_PRE_RUN);
        $this->preRun($output);
        $this->advanceExecutionPhase(RuntimeConfig::PHASE_RUN);
        $exitCode =  parent::run($input, $output);

        if($this->getRuntimeConfig()->getExecutionPhase() !== RuntimeConfig::PHASE_POST_INITIALISE){
            throw new BaseCommandException('BaseCommand not initialized. Did you override the initialize() function '.
            'without calling parent::initialize() ?');
        }

        $this->advanceExecutionPhase(RuntimeConfig::PHASE_POST_RUN);
        $this->postRun($input, $output, $exitCode);

        return $exitCode;
    }


    protected function preRun(OutputInterface $output)
    {
        $this->getRuntimeConfig()->setContainer($this->getContainer());
        $this->loggingEnhancement = new LoggingEnhancement($this, $this->runtimeConfig);

        $this->getLoggingEnhancement()->preRun($output);
    }

    protected function postRun(InputInterface $input, OutputInterface $output, $exitCode)
    {
        $this->getLoggingEnhancement()->postRun($input, $output, $exitCode);

        // Release lock if set
        if(!is_null($this->lockHandler)){
            $this->lockHandler->release();
        }
    }

    /**
     * Provides access to the logger object while maintaining its encapsulation
     *
     * @return Logger
     * @throws BaseCommandException
     */
    public function getLogger()
    {
        return $this->getLoggingEnhancement()->getLogger();
    }

    /**
     * There are cases where log messages are generated prior to the the log handler being initialized. This function
     * allows such messages to be queued up. The queue is then automatically flushed straight after the log handlers are
     * configured
     *
     * @param int    $logLevel The Monolog logging level
     * @param string $message The log message
     * @param array  $context The log context
     *
     * @return BaseCommand
     * @throws BaseCommandException
     */
    public function pushLogMessageOnPreInitQueue($logLevel, $message, array $context = array())
    {
        if($this->getRuntimeConfig()->getExecutionPhase() >= RuntimeConfig::PHASE_INITIALISE){
            throw  new BaseCommandException('Log Messages can only be pushed on the preInit queue prior to initialization. '.
                'Your log entry ('.$message.') should be written directly to the logger');
        } elseif ($this->getRuntimeConfig()->getExecutionPhase() <= RuntimeConfig::PHASE_POST_CONFIGURE){
            throw new BaseCommandException('The experimental functionality of logging messages prior to the logger being '.
            'initialized is not yet available in or before the configure phase of execution');
        }
        $this->getLoggingEnhancement()->pushLogMessageOnPreInitQueue($logLevel, $message, $context);
        return $this;
    }

    /**
     * Whether or not the run() function may be called more than once on the same Command object. This is generally not
     * desirable in most cases as the class member variables and the BaseCommand config is not reset between executions.
     * If however this is the functionality that you want, you can call  setAllowMultipleExecution(true) in your initialize()
     * function to override this protection mechanism
     *
     * @return bool
     */
    protected function isMultipleExecutionAllowed()
    {
        return $this->getRuntimeConfig()->isMultipleExecutionAllowed();
    }

    /**
     * Configure if the run() function may be called more than once on the same Command object. The default setting is FALSE.
     * Running the same object twice is generally not desirable in most cases as the class member variables and the BaseCommand
     * config is not reset between executions. This function is here to allow you to make the conscious decision that running
     * the same object more than once is what you want to do
     *
     * @param boolean $allowMultipleExecution
     */
    protected function setAllowMultipleExecution($allowMultipleExecution)
    {
        $this->getRuntimeConfig()->setAllowMultipleExecution($allowMultipleExecution);
    }

    /**
     * Provide a specific logfile name rather than using one automatically generated by the name strategy. This configuration
     * should be done before BaseCommand::initialize() is invoked
     *
     * @param string $logFilename
     *
     * @return BaseCommand
     * @throws BaseCommandException
     */
    protected function setLogFilename($logFilename)
    {
        $this->getRuntimeConfig()->setLogFilename($logFilename);
        return $this;
    }

    /**
     * Returns the full configured logfile name (including path)
     *
     * @param bool $fullPath whether to return just the filename or include the directory that the log sits in
     *
     * @return string
     */
    public function getLogFilename($fullPath = true)
    {
        return $this->getRuntimeConfig()->getLogFilename($fullPath);
    }

    /**
     * If no logFilename is explicitly defined, the name that is automatically generated will have this file extension
     *
     * @return string
     */
    protected function getDefaultLogFileExtension()
    {
        return $this->getRuntimeConfig()->getDefaultLogFileExtension();
    }

    /**
     * If no logFilename is explicitly defined, the name that is automatically generated will have this file extension
     *
     * @param string $defaultLogFileExtension
     *
     * @return BaseCommand
     * @throws BaseCommandException
     */
    protected function setDefaultLogFileExtension($defaultLogFileExtension)
    {
        $this->getRuntimeConfig()->setDefaultLogFileExtension($defaultLogFileExtension);
        return $this;
    }

    /**
     * Get the current integer Log level configured for this command
     *
     * @return int
     */
    public function getLogLevel()
    {
        return $this->getRuntimeConfig()->getLogLevel();
    }

    /**
     * Returns the RFC 5424 string name of the current log level
     *
     * @return string
     */
    public function getLevelName()
    {
        return Logger::getLevelName($this->getLogLevel());
    }

    /**
     * Set the log level for this command. If the log level is changed on the fly (i.e after the logger has been initialised)
     * the change in level will also be automatically logged
     *
     * @param int $logLevel a log level constant defined in Logger
     *
     * @return $this
     * @throws BaseCommandException
     */
    protected function setLogLevel($logLevel)
    {
        $this->getRuntimeConfig()->setLogLevel($logLevel);
        return $this;
    }

    /**
     * Whether or not this  command is configure to send a copy of the log output to STDOUT
     *
     * @return boolean
     */
    protected function isLogToConsole()
    {
        return $this->getRuntimeConfig()->isLogToConsole();
    }

    /**
     * Configure whether to send a copy of the log output to STDOUT. This configuration should be done before
     * BaseCommand::initialize() is invoked
     *
     * @param boolean $logToConsole
     *
     * @return $this
     * @throws BaseCommandException
     */
    protected function setLogToConsole($logToConsole)
    {
        $this->getRuntimeConfig()->setLogToConsole($logToConsole);
        return $this;
    }

    /**
     * Whether or not this  command is configure to send a copy of the log to a file on disk
     *
     * @return bool
     * @throws BaseCommandException
     */
    public function isLogToFile()
    {
        return $this->getRuntimeConfig()->isLogToFile();
    }

    /**
     * Configure whether to send a copy of the log output to a file on disk. This can only be done before initialisation
     * 
     * @param $logToFile
     *
     * @return $this
     * @throws BaseCommandException
     */
    public function setLogToFile($logToFile)
    {
        $this->getRuntimeConfig()->setLogToFile($logToFile);
        return $this;
    }

    /**
     * Get the format string passed to the Monolog LineFormatter for the file log
     *
     * @return string
     */
    protected function getFileLogLineFormat()
    {
        return $this->getRuntimeConfig()->getFileLogLineFormat();
    }

    /**
     * Configure the format string passed to the Monolog LineFormatter for the file log. This can only be done before
     * initialisation
     *
     * @param $format
     *
     * @return BaseCommand
     * @throws BaseCommandException
     */
    protected function setFileLogLineFormat($format)
    {
        $this->getRuntimeConfig()->setFileLogLineFormat($format);
        return $this;
    }

    /**
     * Get the format string passed to the Monolog LineFormatter for the console log
     *
     * @return string
     */
    protected function getConsoleLogLineFormat()
    {
        return $this->getRuntimeConfig()->getConsoleLogLineFormat();
    }

    /**
     * Configure the format string passed to the Monolog LineFormatter for the console log. This can only be done before
     * initialisation
     *
     * @param $format
     *
     * @return BaseCommand
     * @throws BaseCommandException
     */
    protected function setConsoleLogLineFormat($format)
    {
        $this->getRuntimeConfig()->setConsoleLogLineFormat($format);
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
     * @throws BaseCommandException
     */
    protected function setLocking($value)
    {
        if (!is_bool($value)) {
            throw new BaseCommandException('Value passed to ' . __FUNCTION__ . ' should be of type boolean');
        }

        if (!is_null($this->lockHandler)) {
            throw new BaseCommandException('Cannot ' . (($value) ? 'enable' : 'disable') . ' locking. Lock handler is already initialised');
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
    protected function setLockFileFolder($lockFileFolder)
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
     * Set the display_errors runtime configuration of PHP
     * @link http://php.net/manual/en/errorfunc.configuration.php#ini.display-errors
     *
     * @param bool|string $value
     *
     * @return $this
     * @throws BaseCommandException
     */
    protected function setDisplayErrors($value)
    {
        if (!is_bool($value) && $value != 'stderr' && !in_array($value, array(1, 2))) {
            throw new BaseCommandException('Invalid value passed to setDisplayErrors. Value must be a boolean or the string \'stderr\'');
        }

        if ($value != 'stderr') {
            $value = ($value == true) ? '1' : '0'; // ini_get uses these for variations of on, off, true or false
        }

        $currentValue = ini_get('display_errors');
        if ($currentValue === (string)$value) {
            return $this; // don't do anything if th required value is already set
        }

        if (!function_exists('ini_set')) {
            $this->getLogger()->emergency('CANNOT SET DISPLAY ERRORS. PHP ini_set function is disabled in your environment.');

            return $this;
        }

        // Actually set the value
        ini_set('display_errors', $value);

        if (ini_get('display_errors') == $currentValue) {
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
    protected function setMemoryLimit($memoryLimit)
    {
        if (!function_exists('ini_set')) {
            $this->getLogger()->emergency('CANNOT SET MEMORY LIMIT. PHP ini_set function is disabled in your environment. Limit unchanged!');

            return $this;
        }

        // TODO if no value is set in the config, the logging will not happen when we first use the function after initialisation
        if (isset($this->memoryLimit)) {
            $this->getLogger()->emergency('PHP MEMORY LIMIT CHANGING: from ' . $this->memoryLimit . ' to ' . $memoryLimit);
        }

        $this->memoryLimit = $memoryLimit;

        // Now actually set the php memory limit:
        if ($this->getMemoryLimit() !== null) {
            ini_set('memory_limit', $this->getMemoryLimit());
        }

        // Check if the limit was successfully set:
        if (($this->getMemoryLimit() != ini_get('memory_limit'))) {
            // TODO Test this using an environment on TravisCI with the Suhosin extension
            $this->getLogger()->emergency('PHP Memory Limit was not set. Expected: ' . $this->getMemoryLimit() . '. Check: ' . ini_get('memory_limit'));
        }

        // TODO log if memory limit changed after initialisation

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

    /**
     * This function is private on purpose. The user should not access the RuntimeConfig directly
     *
     * @return RuntimeConfig
     * @throws BaseCommandException
     */
    private function getRuntimeConfig()
    {
        if(is_null($this->runtimeConfig)){
            throw new BaseCommandException('Runtime Config not yet initialized. Make sure that you call parent::configure() '.
                'in your own configure() function before making any configuration changes');
        }
        return $this->runtimeConfig;
    }

    /**
     * Set the current phase of execution to a higher value indicating that we are in the next phase
     * 
     * @param $phase
     *
     * @throws BaseCommandException
     * @throws \Exception
     */
    private function advanceExecutionPhase($phase)
    {
        $this->getRuntimeConfig()->advanceExecutionPhase($phase);
    }

    /**
     * This function is private on purpose. The user should not access the LoggingEnhancement directly
     *
     * @return LoggingEnhancement
     */
    private function getLoggingEnhancement()
    {
        return $this->loggingEnhancement;
    }

}
