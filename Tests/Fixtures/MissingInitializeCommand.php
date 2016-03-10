<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;

use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MissingInitializeCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('test:no_parent_initialize')
            ->setDescription('This command overrides the initialize function without calling the parent version of the function');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
       // parent::initialize() not called
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $output->writeln('executing');
    }

}