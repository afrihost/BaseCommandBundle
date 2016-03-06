<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Afrihost\BaseCommandBundle\Exceptions\LockAcquireException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class LockCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('test:lock')
            ->addOption('child', null, InputOption::VALUE_REQUIRED, 'Whether this is the version of the command invoked by itself', 0)
            ->addOption('child-lock-location', null, InputOption::VALUE_REQUIRED, 'This allows the child\'s lock location '.
                'to match that if the parent')
            ->setDescription('This command calls itself in another process in order to have two processes attempt to acquire '.
                ' the same lock. If this child process throws a LockAcquireException, it ends its execution early and outputs '.
                ' the contents of the exception. The child process\'s output is then re-outputted by the parent process');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setLocking(true);
        if($input->getOption('child')){
            $this->setLockFileFolder($input->getOption('child-lock-location'));
        } elseif(strpos($this->getLockFileFolder(), 'lock_file_folder_undefined') !== false || $this->getLockFileFolder() == null){
            $this->setLockFileFolder('/tmp'); // Default to the system's temporary directory if no directory set
        }
        try{
            parent::initialize($input, $output);
        } catch (LockAcquireException $e){
            if($input->getOption('child')){
                $output->writeln($e->getMessage());
                exit(0);
            }
            throw $e;
        }
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Executing in the '.(($input->getOption('child') ? 'CHILD' : 'PARENT' )));
        $output->writeln('Configured lock file folder:'. $this->getLockFileFolder());
        $output->writeln('PID:'.getmypid());

        if(!$input->getOption('child')){
            $output->writeln('Starting Child');
            $process = new Process('php console test:lock --child=1 --child-lock-location='.$this->getLockFileFolder());
            $process->setWorkingDirectory($this->getApplication()->getKernel()->getRootDir());
            $process->setTimeout(2);
            $process->run();


            if($process->isSuccessful()){
                $output->writeln('CHILD OUTPUT: '.$process->getOutput());
            } else {
                $output->writeln('CHILD EXECUTION FAILED. Child error output: '.$process->getErrorOutput());
                $output->writeln('CHILD OUTPUT: '.$process->getOutput());
            }

        } else {
            $output->writeln('Execution of the child process should have failed in the initialize() function');
        }
    }
}