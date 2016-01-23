<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigDuringExecuteCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('test:execute_config')
            ->setDescription('This command calls all the methods that allow the config of the BaseCommand to be changed '.
                'during execution');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setLogLevel(Logger::DEBUG);

        // Set memory limit to value unlikely to be the existing configuration
        $this->setMemoryLimit('126 M');

        $this->setDisplayErrors(false);
    }
}