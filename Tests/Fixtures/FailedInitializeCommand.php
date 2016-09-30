<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FailedInitializeCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('test:failed_initialize')
            ->setDescription('This command advances the Execution Phase to PHASE_INITIALIZE_FAILED to simulate an exception '.
                'being thrown in INITIALIZE phase');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        EncapsulationViolator::invokeMethod($this, 'advanceExecutionPhase', array(RuntimeConfig::PHASE_INITIALIZE_FAILED));
    }

}