<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogPreInitCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('test:log_pre_init')
            ->setDescription('This command logs a message to the preinit queue before the logger is initialized');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->pushLogMessageOnPreInitQueue(Logger::EMERGENCY, 'This was logged in before parent::initialize()');
        parent::initialize($input, $output);
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Do nothing
    }
}