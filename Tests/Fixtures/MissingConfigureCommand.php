<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;

use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MissingConfigureCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('test:no_parent_configure')
            ->setDescription('This command overrides the configure function without calling the parent version of the function');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}