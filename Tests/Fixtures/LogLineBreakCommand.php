<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogLineBreakCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('test:log_line_break')
            ->setDescription('This command makes one log entry with a line break in it');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Console LineBreaks are '.(($this->getConsoleLogLineBreaks())?'on':'off'));
        $output->writeln('File LineBreaks are '.(($this->getFileLogLineBreaks())?'on':'off'));
        $this->getLogger()->emerg('first line'.PHP_EOL.'second line');
    }

}