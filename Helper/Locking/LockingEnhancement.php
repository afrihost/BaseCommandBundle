<?php

namespace Afrihost\BaseCommandBundle\Helper\Locking;

use Afrihost\BaseCommandBundle\Exceptions\BaseCommandException;
use Afrihost\BaseCommandBundle\Exceptions\LockAcquireException;
use Afrihost\BaseCommandBundle\Helper\AbstractEnhancement;
use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Afrihost\BaseCommandBundle\Helper\EnhancementInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
     * @var
     */
    private $lockFileFolder;

    /**
     * LockingHandler constructor.
     *
     * @param string $lockFileFolder
     */
    public function __construct($lockFileFolder)
    {
        $this->lockFileFolder = $lockFileFolder;
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
            $this->lockHandler = new LockHandler($input->getFirstArgument(), $this->lockFileFolder);

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
}