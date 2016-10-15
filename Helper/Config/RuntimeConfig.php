<?php
namespace Afrihost\BaseCommandBundle\Helper\Config;

use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Afrihost\BaseCommandBundle\Exceptions\BaseCommandException;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class encapsulates the configuration for each specific command execution
 */
class RuntimeConfig
{
    const PHASE_NOT_STARTED = 0;
    const PHASE_CONSTRUCT = 10;
    const PHASE_POST_CONSTRUCT = 20;
    const PHASE_PRE_RUN = 30;
    const PHASE_RUN = 40;
    const PHASE_LOAD_PARAMETERS = 45;
    const PHASE_INITIALISE = 50;
    const PHASE_POST_INITIALISE = 60;
    const PHASE_POST_RUN = 70;
    const PHASE_INITIALIZE_FAILED = 1000;

    /**
     * Flag representing the stage of execution in order to determine if a config may still be manipulated
     * @var int
     */
    protected $executionPhase;

    /**
     * @var BaseCommand
     */
    protected $command;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var bool
     */
    protected $allowMultipleExecution = false;

    /**
     * @var int
     */
    private $logLevel;

    /**
     * @var bool
     */
    private $logToConsole;

    /**
     * @var bool
     */
    private $logToFile;

    /**
     * @var string
     */
    private $logFilename = null;

    /**
     * @var string
     */
    private $defaultLogFileExtension;

    /**
     * @var string
     */
    private $fileLogLineFormat = 'log_line_format_undefined';

    /**
     * @var string
     */
    private $consoleLogLineFormat = 'log_line_format_undefined';

    /**
     * @var boolean
     */
    private $consoleLogLineBreaks;

    /**
     * @var boolean
     */
    private $fileLogLineBreaks;

    /**
     * @var boolean
     */
    private $locking;

    private $lockFileFolder = 'lock_file_folder_undefined';

    /**
     * @var boolean
     */
    private $unicodeIconSupport;

    /**
     * RuntimeConfig constructor.
     *
     * @param BaseCommand        $command that this config belongs to
     */
    public function __construct(BaseCommand $command)
    {
        $this->executionPhase = self::PHASE_NOT_STARTED;
        $this->command = $command;
    }

    /**
     * Set the current phase of execution to a higher value indicating that we are in the next phase
     * @param $phase
     *
     * @return RuntimeConfig
     * @throws \Exception
     * @throws BaseCommandException
     */
    public function advanceExecutionPhase($phase)
    {
        if($phase < $this->executionPhase){
            if($this->executionPhase == self::PHASE_POST_RUN && $phase == self::PHASE_PRE_RUN){
                if(!$this->isMultipleExecutionAllowed()){
                    throw new BaseCommandException('You are attempting execute the same command object twice! This may be undesirable as '.
                        'the object\'s configuration and class member variables have not been reset since the last execution. If you are '.
                        'certain that this is what you want to do, call the CommandBase::setAllowMultipleExecution() function during '.
                        'initialization');
                }
            } else{
                throw new \Exception('Execution phase can only be advanced forward. Attempting to go from Phase '.$this->executionPhase.
                    ' to Phase '.$phase);
            }
        }
        $this->executionPhase = $phase;
        return $this;
    }

    /**
     * @return int
     */
    public function getExecutionPhase()
    {
        return $this->executionPhase;
    }

    /**
     * This function makes a copy of the configuration of the bundle for this this specific execution of this specific command
     *
     * @param ContainerInterface $container
     *
     * @throws BaseCommandException
     */
    public function loadGlobalConfigFromContainer(ContainerInterface $container)
    {
        // File Logging Settings
        if(is_null($this->defaultLogFileExtension)){
            $this->setDefaultLogFileExtension($container->getParameter('afrihost_base_command.logger.handler_strategies.file_stream.file_extension'));
        }
        if(is_null($this->logToFile)){
            $this->setLogToFile($this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.file_stream.enabled'));
        }
        if($this->fileLogLineFormat === 'log_line_format_undefined'){
            $this->setFileLogLineFormat($this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.file_stream.line_format'));
        }
        if(is_null($this->fileLogLineBreaks)){
            $this->setFileLogLineBreaks($this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.file_stream.allow_line_breaks'));
        }

        // Console Logging Settings
        if(is_null($this->logToConsole)){
            $this->setLogToConsole($this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.console_stream.enabled'));
        }
        if($this->consoleLogLineFormat === 'log_line_format_undefined'){
            $this->setConsoleLogLineFormat($this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.console_stream.line_format'));
        }
        if(is_null($this->consoleLogLineBreaks)){
            $this->setConsoleLogLineBreaks($this->getContainer()->getParameter('afrihost_base_command.logger.handler_strategies.console_stream.allow_line_breaks'));
        }

        // Locking Settings
        if (is_null($this->locking)) {
            $this->setLocking($this->getContainer()->getParameter('afrihost_base_command.locking.enabled'));
        }
        if ($this->lockFileFolder === 'lock_file_folder_undefined') {
            $this->lockFileFolder = $this->getContainer()->getParameter('afrihost_base_command.locking.lock_file_folder');
        }
    }

    /**
     * Some config options can be overridden at runtime via command line parameters. This function is run at the start
     * of the BaseCommand initialize, after the global settings have been loaded from the container, and implements this
     * overriding logic.
     *
     * @param InputInterface $input
     *
     * @throws BaseCommandException
     */
    public function loadConfigFromCommandParameters(InputInterface $input)
    {
        // Logging parameters
        if ($input->getOption('log-level')) {
                $loggerLevels = Logger::getLevels();
                $this->setLogLevel($loggerLevels[strtoupper($input->getOption('log-level'))]);
        }
        if ($input->getOption('log-filename')) {
            $this->setLogFilename($input->getOption('log-filename'));
        }

        // Locking parameters
        if ($input->getOption('locking') !== null) {
            $lockingInput = strtolower($input->getOption('locking'));

            $validLockingOptions = array('on', 'off');
            if (!in_array($lockingInput, $validLockingOptions)) {
                throw new BaseCommandException(
                    'Invalid value for \'--locking\' parameter. ' . 'You specified "' . $lockingInput . '". ' .
                    'Valid values are: ' . implode(',', $validLockingOptions));
            }

            $this->setLocking((($lockingInput == 'on') ? true : false));
        }

    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * The loglevel may be changed after the log handlers are initialised. If this is the case, this function will
     * update the handlers and note the chance in the log
     *
     * @param int $logLevel
     *
     * @return RuntimeConfig
     * @throws BaseCommandException
     */
    public function setLogLevel($logLevel)
    {
        if (!in_array($logLevel, Logger::getLevels())) {
            $message = "'" . $logLevel . "' is not a valid LOGLEVEL. ".
                "Valid values as command line parameters are: " . implode(',', array_keys(Logger::getLevels())).
                " and valid values when updating the log level in code are: ".implode(',', Logger::getLevels());
            throw new BaseCommandException($message);
        }

        $this->logLevel = $logLevel;


        if($this->getExecutionPhase() == self::PHASE_LOAD_PARAMETERS){
            // LogLevel changed at RunTime via parameter
            $this->logConfigDebug('LOG LEVEL CHANGED VIA PARAMETER: '.Logger::getLevelName($logLevel));

        } elseif ($this->getExecutionPhase() >= self::PHASE_INITIALISE) {
            // LogLevel changed  after the log handlers have been initialised

            /* @var $handler AbstractHandler */
            foreach ($this->getCommand()->getLogger()->getHandlers() as $handler) {
                $handler->setLevel($logLevel);
            }

            // Note in log that log level has been changed so that the new verbosity in the log is understood
            $this->logConfigDebug('LOG LEVEL CHANGED: ' . Logger::getLevelName($logLevel));
        }

        return $this;
    }

    /**
     * Whether or not this  command is configure to send a copy of the log output to STDOUT
     *
     * @return boolean
     */
    public function isLogToConsole()
    {
        return $this->logToConsole;
    }

    /**
     * Configure whether to send a copy of the log output to STDOUT. This can only be done before initialisation
     *
     * @param boolean $logToConsole
     *
     * @return RuntimeConfig
     * @throws BaseCommandException
     */
    public function setLogToConsole($logToConsole)
    {
        if ($this->getExecutionPhase() >= self::PHASE_INITIALISE) {
            throw new BaseCommandException('Cannot ' . (($logToConsole) ? 'enable' : 'disable') . ' console logging. '.
                'Logger is already initialised');
        }

        if (!is_bool($logToConsole)) {
            throw new BaseCommandException('LogToConsole setting must be a boolean');
        }

        $this->logToConsole = $logToConsole;

        return $this;
    }

    /**
     * Whether or not this  command is configure to send a copy of the log to a file on disk
     *
     * @return boolean
     */
    public function isLogToFile()
    {
        return $this->logToFile;
    }

    /**
     * Configure whether to send a copy of the log output to a file on disk. This can only be done before initialisation
     *
     * @param boolean $logToFile
     *
     * @return RuntimeConfig
     * @throws BaseCommandException
     */
    public function setLogToFile($logToFile)
    {
        if ($this->getExecutionPhase() >= self::PHASE_INITIALISE) {
            throw new BaseCommandException('Cannot ' . (($logToFile) ? 'enable' : 'disable') . ' file logging. '.
                'Logger is already initialised');
        }

        if (!is_bool($logToFile)) {
            throw new BaseCommandException('LogToFile setting must be a boolean');
        }

        $this->logToFile = $logToFile;

        return $this;
    }

    /**
     * Get the format string passed to the Monolog LineFormatter for the file log
     *
     * If a value is specified, add a newline character to the end else return null to have the Monolog default used
     *
     * @return string
     */
    public function getFileLogLineFormat()
    {
        return (is_null($this->fileLogLineFormat))? null : $this->fileLogLineFormat.PHP_EOL;
    }

    /**
     * Configure the format string passed to the Monolog LineFormatter for the file log. This can only be done before
     * initialisation
     *
     * @param string $format
     *
     * @return RuntimeConfig
     * @throws BaseCommandException
     */
    public function setFileLogLineFormat($format)
    {
        if ($this->getExecutionPhase() >= self::PHASE_INITIALISE) {
            throw new BaseCommandException('Cannot set new line format for file logging. Logger is already initialised');
        }

        $this->fileLogLineFormat = $format;
        return $this;
    }

    /**
     * Get the format string passed to the Monolog LineFormatter for the console log
     *
     * If a value is specified, add a newline character to the end else return null to have the Monolog default used
     *
     * @return string
     */
    public function getConsoleLogLineFormat()
    {
        return (is_null($this->consoleLogLineFormat))? null : $this->consoleLogLineFormat.PHP_EOL;
    }

    /**
     * Configure the format string passed to the Monolog LineFormatter for the console log. This can only be done before
     * initialisation
     *
     * @param string $format
     *
     * @return RuntimeConfig
     * @throws BaseCommandException
     */
    public function setConsoleLogLineFormat($format)
    {
        if ($this->getExecutionPhase() >= self::PHASE_INITIALISE) {
            throw new BaseCommandException('Cannot set new line format for console logging. Logger is already initialised');
        }

        $this->consoleLogLineFormat = $format;
        return $this;
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
    public function setLogFilename($logFilename)
    {
        if($this->getExecutionPhase() == self::PHASE_LOAD_PARAMETERS){
            $this->logConfigDebug('LOG FILENAME CHANGED VIA PARAMETER: '.$logFilename);
        } elseif ($this->getExecutionPhase() > self::PHASE_INITIALISE) {
            throw new BaseCommandException('Cannot set manual logfile name. Logger is already initialised');
        }

        $this->logFilename = $logFilename;

        return $this;
    }

    /**
     * Returns the full configured logfile name (including path)
     *
     * @param bool               $fullPath whether to return just the filename or include the directory that the log sits in
     *
     * @return null|string
     */
    public function getLogFilename($fullPath)
    {
        if($fullPath){
           return $this->getContainer()->get('kernel')->getLogDir() . DIRECTORY_SEPARATOR . $this->logFilename;
        }
        return $this->logFilename;
    }

    /**
     * If no logFilename is explicitly defined, the name that is automatically generated will have this file extension
     *
     * @return string
     */
    public function getDefaultLogFileExtension()
    {
        return $this->defaultLogFileExtension;
    }

    /**
     * If no logFilename is explicitly defined, the name that is automatically generated will have this file extension
     *
     * @param string $defaultLogFileExtension
     *
     * @return RuntimeConfig
     * @throws BaseCommandException
     */
    public function setDefaultLogFileExtension($defaultLogFileExtension)
    {
        if ($this->getExecutionPhase() > self::PHASE_INITIALISE) {
            throw new BaseCommandException('Cannot set default logfile extension. Logger is already initialised');
        }
        $this->defaultLogFileExtension = $defaultLogFileExtension;
        return $this;
    }

    /**
     * Whether or not newline characters in log records will be outputted or stripped when logging to console
     *
     * @return boolean
     */
    public function getConsoleLogLineBreaks()
    {
        return $this->consoleLogLineBreaks;
    }

    /**
     * Configure if newline characters in log records should be outputted when logging to console. By default, Monolog strips
     * out line breaks so that one line equates to one log entry. This is useful for logs parsed by machines but not
     * for logs intended to be read by humans
     *
     * @param boolean $consoleLogLineBreaks
     *
     * @throws BaseCommandException
     */
    public function setConsoleLogLineBreaks($consoleLogLineBreaks)
    {
        if (!is_bool($consoleLogLineBreaks)) {
            throw new BaseCommandException('ConsoleLogLineBreaks setting must be a boolean');
        }

        if ($this->getExecutionPhase() > self::PHASE_INITIALISE) {
            throw new BaseCommandException('Cannot set Log Line Breaks option for console. Logger is already initialised');
        }

        $this->consoleLogLineBreaks = $consoleLogLineBreaks;
    }

    /**
     * Configure if newline characters in log records should be outputted when logging to file. By default, Monolog strips
     * out line breaks so that one line equates to one log entry. This is useful for logs parsed by machines but not
     * for logs intended to be read by humans
     *
     * Whether or not newline characters in log records will be outputted or stripped when logging to file
     *
     * @return boolean
     */
    public function getFileLogLineBreaks()
    {
        return $this->fileLogLineBreaks;
    }

    /**
     * @param boolean $fileLogLineBreaks
     *
     * @throws BaseCommandException
     */
    public function setFileLogLineBreaks($fileLogLineBreaks)
    {
        if (!is_bool($fileLogLineBreaks)) {
            throw new BaseCommandException('FileLogLineBreaks setting must be a boolean');
        }

        if ($this->getExecutionPhase() > self::PHASE_INITIALISE) {
            throw new BaseCommandException('Cannot set Log Line Breaks option for file. Logger is already initialised');
        }

        $this->fileLogLineBreaks = $fileLogLineBreaks;
    }


    /**
     * Used to notify that an invalid configuration has been attempted (such as setting a value during execution that
     * cannot be changed after initialisation)
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     * @throws BaseCommandException
     */
    protected function logConfigWarning($message, array $context = array())
    {
        // TODO Make LogLevel Configurable
        if($this->getExecutionPhase() < self::PHASE_INITIALISE){
            $this->getCommand()->pushLogMessageOnPreInitQueue(Logger::EMERGENCY, $message, $context);
        } else {
            $this->getCommand()->getLogger()->emerg($message, $context);
        }
        return $this;
    }

    /**
     * Used to confirm important configuration has taken place (such as that the lock has been acquired or loglevel has
     * been changed at runtime)
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     * @throws BaseCommandException
     */
    protected function logConfigDebug($message, array $context = array())
    {
        // TODO Make LogLevel Configurable
        if($this->getExecutionPhase() < self::PHASE_INITIALISE){
            $this->getCommand()->pushLogMessageOnPreInitQueue(Logger::EMERGENCY, $message, $context);
        } else {
            $this->getCommand()->getLogger()->emerg($message, $context);
        }
        return $this;
    }

    /**
     * @return BaseCommand
     */
    protected function getCommand()
    {
        return $this->command;
    }

    /**
     * @return ContainerInterface
     * @throws BaseCommandException
     */
    protected function getContainer()
    {
        if(is_null($this->container)){
            throw new BaseCommandException('Cannot access the Container yet. It has not yet been initialised and set');
        }
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Whether or not the run() function may be called more than once on the same Command object. This is generally not
     * desirable in most cases as the class member variables and the BaseCommand config is not reset between executions.
     * If however this is the functionality that you want, you can call  setAllowMultipleExecution(true) in your initialize()
     * function to override this protection mechanism
     *
     * @return bool
     */
    public function isMultipleExecutionAllowed()
    {
        return $this->allowMultipleExecution;
    }

    /**
     * Configure if the run() function may be called more than once on the same Command object. The default setting is FALSE.
     * Running the same object twice is generally not desirable in most cases as the class member variables and the BaseCommand
     * config is not reset between executions. This function is here to allow you to make the conscious decision that running
     * the same object more than once is what you want to do
     *
     * @param boolean $allowMultipleExecution
     */
    public function setAllowMultipleExecution($allowMultipleExecution)
    {
        $this->allowMultipleExecution = $allowMultipleExecution;
    }

    /**
     * @return boolean
     */
    public function hasUnicodeIconSupport()
    {
        return $this->unicodeIconSupport;
    }

    /**
     * @param boolean $unicodeIconSupport
     */
    public function setUnicodeIconSupport($unicodeIconSupport)
    {
        $this->unicodeIconSupport = $unicodeIconSupport;

        if($unicodeIconSupport === false) {
            $this->logConfigWarning('Unicode support disabled');
        }
    }

    /**
     *
     * command from being executed more than once at the same time
     *
     * @param bool $value whether locking functionality should be enabled or disabled
     *
     * @return RuntimeConfig
     * @throws BaseCommandException
     */
    public function setLocking($value)
    {
        if (!is_bool($value)) {
            throw new BaseCommandException('Value passed to ' . __FUNCTION__ . ' should be of type boolean');
        }

        if ($this->getExecutionPhase() > self::PHASE_INITIALISE) {
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
    public function isLocking()
    {
        return $this->locking;
    }

    /**
     * Used to override the default folder where your lock-files are stored.
     *
     * Providing a value of NULL with result in the Symfony default of creating the lock file in the temporary directory of the system
     * If an absolute path is provided, the directory must already exist and be accessible to the PHP process.
     * In POSIX environments, paths can be provided that start with ~/. This will be expanded using the $HOME environment
     * variable and the full path subjected to he same constraints as absolute paths.
     * or ~/ we assume you have a static location for it.
     * All other values will be considered to be relative to the Symfony Kernel root directory of your application.
     *
     * @param string $lockFileFolder
     *
     * @return $this
     * @throws BaseCommandException
     */
    public function setLockFileFolder($lockFileFolder)
    {
        if ($this->getExecutionPhase() > self::PHASE_INITIALISE) {
            throw new BaseCommandException('Cannot change the location of the lock file. Lock handler is already initialised');
        }

        $this->lockFileFolder = $lockFileFolder;
        return $this;
    }

    /**
     * Gets the folder where the lock files will be stored, performing any necessary expansions and validations in the process
     *
     * @return string
     * @throws BaseCommandException
     */
    public function getLockFileFolder()
    {
        if($this->lockFileFolder == 'lock_file_folder_undefined') {
            return null;  // TODO add warning when attempting to access value before it is set
        }

        $fs = new Filesystem();
        if(is_null($this->lockFileFolder)){
             return null; // Null will result in the temporary directory of the system being used by the handler

        } elseif(substr($this->lockFileFolder, 0, 2) === '~/'){ // Contains tilde
            // Expand tilde to user's home directory
            $homeDir = getenv('HOME');
            if($homeDir !== false){
                $lockDirectory = $homeDir.DIRECTORY_SEPARATOR.substr($this->lockFileFolder, 2);
                $realPath = realpath($lockDirectory);
                if($realPath === false){
                    throw new BaseCommandException('Lock file folders outside of the project directory will not be created '.
                        'automatically. The provided directory does not exist or is not accessible: '.$lockDirectory);
                }
                return $realPath;
            } else {
                throw new BaseCommandException('Could not resolve tilde (~) to the user\'s home directory for the lock '.
                    ' file folder. Please check the $HOME environment variable of the user executing the command or consider '.
                    'using an absolute path'
                );

            }

        } elseif($fs->isAbsolutePath($this->lockFileFolder)){ // Is absolute path (Cross-platform check that works on windows)
            $realPath = realpath($this->lockFileFolder);
            if( $realPath === false){
                throw new BaseCommandException('Lock file folders outside of the project directory will not be created '.
                    'automatically. The provided directory does not exist or is not accessible: '.$this->lockFileFolder);
            }
            return $realPath;

        } else { // Relative path
            // Prepend Kernel Root directory
            return $this->getContainer()->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . $this->lockFileFolder;
        }
    }

}