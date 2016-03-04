<?php

namespace Afrihost\BaseCommandBundle\Helper\Locking;

use Afrihost\BaseCommandBundle\Exceptions\BaseCommandException;
use Afrihost\BaseCommandBundle\Exceptions\LockAcquireException;
use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Afrihost\BaseCommandBundle\Helper\EnhancementInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\LockHandler;

class LockingEnhancement implements EnhancementInterface
{
    /**
     * @var LockHandler
     */
    private $lockHandler;

    /**
     * @var false
     */
    private $locking;

    /**
     * @var string
     */
    private $lockFileFolder;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param string $lockFileFolder
     * @param string $rootDir
     */
    public function __construct($lockFileFolder, $rootDir)
    {
        $this->lockFileFolder = $lockFileFolder;
        $this->rootDir = $rootDir;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws LockAcquireException
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($this->isLocking()) {
            $this->lockHandler = new LockHandler($input->getFirstArgument(), $this->getLockFileFolder());

            if (!$this->lockHandler->lock()) {
                throw new LockAcquireException('Sorry, can\'t get the lock. Bailing out!');
            }

            // TODO Decide on output option here (possibly option to log instead of polluting STDOUT)
            //$output->writeln('<info>LOCK Acquired</info>');
        }
    }

    /**
     * Logic that needs to be hooked in before the command's run() function is invoked (i.e. after construction but before
     * initialization) should be placed here.  The function will be called by the BaseCommand's preRun() function
     *
     * @param OutputInterface $output
     */
    public function preRun(OutputInterface $output)
    {
        // noop
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param int|null        $exitCode
     */
    public function postRun(InputInterface $input, OutputInterface $output, $exitCode)
    {
        // Release lock if set
        if (null !== $this->lockHandler) {
            $this->lockHandler->release();
        }
    }

    /**
     * Provides access to the LockHandler object while maintaining its encapsulation so that all initialisation logic is done
     * in this class
     *
     * @return LockHandler
     * @throws BaseCommandException
     */
    public function getLockHandler()
    {
        if (is_null($this->lockHandler)) {
            throw new BaseCommandException('Cannot access LockHandler. It is not yet initialised.');
        }

        return $this->lockHandler;
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
     * Configure whether commands should attempt to acquire a local lock before execution, thereby preventing the same
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

        $this->locking = $value;

        return $this;
    }

    /**
     * Gets the folder where the lock files will be stored, performing any necessary expansions and validations in the process
     *
     * @return string
     * @throws BaseCommandException
     */
    private function getLockFileFolder()
    {
        if (null === $this->lockFileFolder) {
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
            return $this->rootDir . DIRECTORY_SEPARATOR . $this->lockFileFolder;
        }
    }

    /**
     * @param string $locking
     */
    public function setLockFileFolder($locking)
    {
        $this->lockFileFolder = $locking;
    }
}