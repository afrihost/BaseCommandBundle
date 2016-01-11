<?php
namespace Afrihost\BaseCommandBundle\Tests\Fixtures;

use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class HelloWorldCommand extends BaseCommand
{

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('test:hello_world')
            ->setDescription('This command makes does not explicitly make use of any of the features of the bundle. '.
                'It simply out puts a traditional "Hello World" string.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write("Hello World");
    }
}