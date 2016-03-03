<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IconCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('test:icon')
            ->setDescription('This command displays icons');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setAllowMultipleExecution(true);
        parent::initialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $icon = $this->getIcon()->error()->red()->bold()->render();
        $output->writeln($icon);
    }
}