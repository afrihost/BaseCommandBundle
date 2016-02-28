<?php
namespace Afrihost\BaseCommandBundle\Helper\Locking;

use Afrihost\BaseCommandBundle\Exceptions\BaseCommandException;
use Afrihost\BaseCommandBundle\Exceptions\LockAcquireException;
use Afrihost\BaseCommandBundle\Helper\AbstractEnhancement;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

class LockingEnhancement extends AbstractEnhancement
{
    /**
     * @var LockHandler
     */
    private $lockHandler;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws LockAcquireException
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($this->getRuntimeConfig()->isLocking()) {
            $this->lockHandler = new LockHandler($this->getUserCommandClassFilename(), $this->getRuntimeConfig()->getLockFileFolder());
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
        // TODO: Implement preRun() method.
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param int|null        $exitCode
     */
    public function postRun(InputInterface $input, OutputInterface $output, $exitCode)
    {
        // Release lock if set
        if(!is_null($this->lockHandler)){
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
}