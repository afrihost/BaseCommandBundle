<?php
namespace Afrihost\BaseCommandBundle\Helper\Config;

use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Afrihost\BaseCommandBundle\Exceptions\BaseCommandException;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class encapsulates the configuration for each specific command execution
 */
class RuntimeConfig
{
    const PHASE_NOT_STARTED = 0;
    const PHASE_CONFIGURE = 10;
    const PHASE_POST_CONFIGURE = 20;
    const PHASE_RUN = 30;
    const PHASE_INITIALISE = 40;
    const PHASE_POST_INITIALISE = 50;
    const PHASE_POST_RUN = 60;

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
    private $defaultLogFileExtension;

    /**
     * RuntimeConfig constructor.
     *
     * @param BaseCommand $command that this config belongs to
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
     */
    public function advanceExecutionPhase($phase)
    {
        if($phase < $this->executionPhase){
            if($this->executionPhase == self::PHASE_POST_RUN && $phase == self::PHASE_RUN){
                $this->logConfigWarning('You are attempting execute the same command twice. This may be undesirable as '.
                    'its configuration and class member variables have not been reset since the last execution');
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
        // TODO Fix spelling mistake of file extension
        $this->setDefaultLogFileExtension($container->getParameter('afrihost_base_command.logger.handler_strategies.default.file_extention'));
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

        // Check if updating loglevel after the log handlers have been initialised
        if ($this->getExecutionPhase() >= self::PHASE_INITIALISE) {

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
     * @throws \Exception
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
        if ($this->getExecutionPhase() > self::PHASE_INITIALISE) {
            throw new \Exception('Cannot set manual logfile name. Logger is already initialised');
        }

        $this->logFilename = $logFilename;

        return $this;
    }

    /**
     * Returns the full configured logfile name (including path)
     *
     * @param bool               $fullPath whether to return just the filename or include the directory that the log sits in
     * @param ContainerInterface $container Symfony application container. Used to get the logfile directory
     *
     * @return null|string
     */
    public function getLogFilename($fullPath, ContainerInterface $container )
    {
        if($fullPath){
           return $container->get('kernel')->getLogDir() . DIRECTORY_SEPARATOR . $this->logFilename;
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
     * Used to notify that an invalid configuration has been attempted (such as setting a value during execution that
     * cannot be changed after initialisation)
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     * @throws \Exception
     */
    protected function logConfigWarning($message, array $context = array())
    {
        // TODO Make LogLevel Configurable
        $this->getCommand()->getLogger()->emerg($message, $context);
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
     * @throws \Exception
     */
    protected function logConfigDebug($message, array $context = array())
    {
        // TODO Make LogLevel Configurable
        $this->getCommand()->getLogger()->emerg($message, $context);
        return $this;
    }

    protected function getCommand()
    {
        return $this->command;
    }

}