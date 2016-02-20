<?php
namespace Afrihost\BaseCommandBundle\Helper;

use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The implementation for each type of feature that augments the Symfony command should be separated in to a Enhancement
 * class that implements this interface
 */
interface EnhancementInterface
{
    /**
     * Setup of the enhancement should take place here. This function will be called in the BaseCommand's initialize() function
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output);

    /**
     * Logic that needs to be hooked in before the command's run() function is invoked (i.e. after construction but before
     * initialization) should be placed here.  The function will be called by the BaseCommand's preRun() function
     *
     * @param OutputInterface $output
     */
    public function preRun(OutputInterface $output);

    /**
     * Cleanup logic that is to be executed after the command has been run should be implemented here. This function will
     * be called BaseCommand's postRun() function
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param int|null        $exitCode
     */
    public function postRun(InputInterface $input, OutputInterface $output, $exitCode);
}