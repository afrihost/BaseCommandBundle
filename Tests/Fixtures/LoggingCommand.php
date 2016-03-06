<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoggingCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('test:logging')
            ->setDescription('This command makes one log entry for each logging level');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->debug('DEBUG');
        $this->getLogger()->info('INFO');
        $this->getLogger()->notice('NOTICE');
        $this->getLogger()->warn('WARNING');
        $this->getLogger()->error('ERROR');
        $this->getLogger()->crit('CRITICAL');
        $this->getLogger()->alert('ALERT');
        $this->getLogger()->emerg('EMERGENCY');

        // Likely unique phrase that can be asserted against
        $this->getLogger()->emerg('The quick brown fox jumps over the lazy dog');
    }
}