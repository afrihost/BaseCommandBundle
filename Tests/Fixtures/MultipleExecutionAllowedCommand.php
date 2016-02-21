<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MultipleExecutionAllowedCommand  extends BaseCommand
{

    private $runCount = 0;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('test:multiple_execution_allowed')
            ->setDescription('An object of this class is allowed to have its run fucntion called multiple times. '.
                'Each time it is run it will output a successively higher number starting a one ');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setAllowMultipleExecution(true);
        parent::initialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runCount++;
        $output->write($this->runCount);
    }

}