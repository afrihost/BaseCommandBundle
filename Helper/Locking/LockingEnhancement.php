<?php
namespace Afrihost\BaseCommandBundle\Helper\Locking;

use Afrihost\BaseCommandBundle\Exceptions\BaseCommandException;
use Afrihost\BaseCommandBundle\Exceptions\LockAcquireException;
use Afrihost\BaseCommandBundle\Helper\AbstractEnhancement;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Component\Lock\Factory;

class LockingEnhancement extends AbstractEnhancement
{
    /**
     * @var LockInterface
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

            // If a custom logfile name has been provided,  use this as the lockfile name
            $lockfileName = $this->getRuntimeConfig()->getLogFilename(false);
            if(is_null($lockfileName)){
                $lockfileName = $this->getUserCommandClassFilename();
            }

            if (class_exists('Symfony\Component\Lock\LockFactory')) {
                // Symfony 4.4+
                $store = new FlockStore($this->getRuntimeConfig()->getLockFileFolder());
                $factory = new LockFactory($store);

                $this->lockHandler = $factory->createLock($lockfileName);
                if (!$this->lockHandler->acquire()) {
                    throw new LockAcquireException('Sorry, can\'t get the lock. Bailing out!');
                }
            } elseif (class_exists('Symfony\Component\Lock\Factory')) {
                // Symfony 4.0, 4.1, 4,2, 4.3
                $store = new FlockStore($this->getRuntimeConfig()->getLockFileFolder());
                $factory = new Factory($store);

                $this->lockHandler = $factory->createLock($lockfileName);
                if (!$this->lockHandler->acquire()) {
                    throw new LockAcquireException('Sorry, can\'t get the lock. Bailing out!');
                }
            } else {
                // Symfony 3
                $this->lockHandler = new LockHandler($lockfileName, $this->getRuntimeConfig()->getLockFileFolder());
                if (!$this->lockHandler->lock()) {
                    throw new LockAcquireException('Sorry, can\'t get the lock. Bailing out!');
                }
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
}