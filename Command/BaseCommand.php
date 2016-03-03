<?php
/**
 * Copyright (c) 2015 Afrihost Internet Services (Pty) Ltd
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Afrihost\BaseCommandBundle\Command;

use Afrihost\BaseCommandBundle\Exceptions\BaseCommandException;
use Afrihost\BaseCommandBundle\Exceptions\LockAcquireException;
use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Afrihost\BaseCommandBundle\Helper\Locking\LockingEnhancement;
use Afrihost\BaseCommandBundle\Helper\Logging\LoggingEnhancement;
use Afrihost\BaseCommandBundle\Helper\UI\IconEnhancement;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var LockingEnhancement
     */
    private $lockingEnhancement;

    /**
     * @var string
     */
    private $memoryLimit;

    /**
     * @var IconEnhancement
     */
    private $icon;

    /**
     * Provides default options for all commands. This function should be called explicitly (i.e. parent::configure())
     * if the configure function is overridden.
     */
    protected function configure()
    {
        $this->runtimeConfig = new RuntimeConfig($this);

        $this->advanceExecutionPhase(RuntimeConfig::PHASE_CONFIGURE);

        parent::configure();

        $this->addOption('log-level', 'l', InputOption::VALUE_REQUIRED,
                'Override the Monolog logging level for this execution of the command. Valid values: ' .
                implode(', ', array_keys(Logger::getLevels())))
            ->addOption('log-filename', null, InputOption::VALUE_REQUIRED, 'Specify a different file (relative to the '.
                'kernel log directory) to send file logs to')
            ->addOption('locking', null, InputOption::VALUE_REQUIRED, 'Whether or not this execution needs to acquire a '.
                ' lock that ensures that the command is only being run once concurrently. Valid values: on, off');

        $this->advanceExecutionPhase(RuntimeConfig::PHASE_POST_CONFIGURE);
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
     * @throws LockAcquireException
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        try{
            $this->getRuntimeConfig()->loadGlobalConfigFromContainer($this->getContainer());

            $this->advanceExecutionPhase(RuntimeConfig::PHASE_LOAD_PARAMETERS);
            $this->getRuntimeConfig()->loadConfigFromCommandParameters($input);

            $this->advanceExecutionPhase(RuntimeConfig::PHASE_INITIALISE);
            parent::initialize($input, $output);
            $this->getLoggingEnhancement()->initialize($input, $output);
            $this->getLockingEnhancement()->initialize($input, $output);


            // Override production settings of not showing errors
            error_reporting(E_ALL);
            $this->setDisplayErrors(true);

            // PHP Memory Limit:
            if ($this->getMemoryLimit() !== null) {
                $this->setMemoryLimit($this->getMemoryLimit());
            }

            $this->advanceExecutionPhase(RuntimeConfig::PHASE_POST_INITIALISE);
        } catch (\Exception $e ){
            $this->getRuntimeConfig()->advanceExecutionPhase(RuntimeConfig::PHASE_INITIALIZE_FAILED);
            throw $e;
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
            if($this->getRuntimeConfig()->getExecutionPhase() == RuntimeConfig::PHASE_INITIALIZE_FAILED){
                throw new BaseCommandException('It appears that you tried to continue execution despite the initialization '.
                    'of the BaseCommand failing. This is a very dangerous idea as the behaviour is undefined');
            } else {
                throw new BaseCommandException('BaseCommand not initialized. Did you override the initialize() function '.
                    'without calling parent::initialize() ?');
            }
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

        $this->icon = new IconEnhancement($this, $this->runtimeConfig);
        $this->getIcon()->preRun($output);

        $this->lockingEnhancement = new LockingEnhancement($this, $this->runtimeConfig);
        $this->getLockingEnhancement()->preRun($output);
    }

    protected function postRun(InputInterface $input, OutputInterface $output, $exitCode)
    {
        $this->getLoggingEnhancement()->postRun($input, $output, $exitCode);
        $this->getLockingEnhancement()->postRun($input, $output, $exitCode);
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
     * Configure whether commands should attempt to acquire a local lock before execution, thereby preventing the same
     * command from being executed more than once at the same time
     *
     * @param bool $value
     *
     * @return $this
     * @throws BaseCommandException
     */
    protected function setLocking($value)
    {
        $this->getRuntimeConfig()->setLocking($value);

        return $this;
    }

    /**
     * Whether locking is enabled for this command
     *
     * @return bool
     */
    protected function isLocking()
    {
        return $this->getRuntimeConfig()->isLocking();
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
        $this->getRuntimeConfig()->setLockFileFolder($lockFileFolder);

        return $this;
    }

    /**
     * Gets the folder where the lockfiles will be stored.
     *
     * @return string
     * @throws BaseCommandException
     */
    protected function getLockFileFolder()
    {
        return $this->getRuntimeConfig()->getLockFileFolder();
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

    /**
     * @return IconEnhancement
     */
    public function getIcon(){
        return $this->icon;
    }

    /**
     * This function is private on purpose. The user should not access the LockingEnhancement directly
     *
     * @return LockingEnhancement
     */
    private function getLockingEnhancement()
    {
        return $this->lockingEnhancement;
    }
}
