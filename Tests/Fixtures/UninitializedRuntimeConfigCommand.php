<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UninitializedRuntimeConfigCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setLogToConsole(false); // This should throw the exception that we are looking for

        parent::configure();

        $this
            ->setName('test:uninitialized_runtime_config')
            ->setDescription('This command should throw an exception because we have used a method that relies on the '.
                'RuntimeConfig before the RuntimeConfig object is initialised in parent::configure()');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->emerg('This statement should never actually get run because an exception should have been '.
            'thrown in the configure function');
    }
}