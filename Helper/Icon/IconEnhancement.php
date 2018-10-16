<?php

namespace Afrihost\BaseCommandBundle\Helper\Icon;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Afrihost\BaseCommandBundle\Helper\AbstractEnhancement;
use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IconEnhancement extends AbstractEnhancement
{

    private $iconHandler;

    /**
     * IconEnhancement constructor.
     * @param BaseCommand $command
     * @param RuntimeConfig $runtimeConfig
     */
    public function __construct(BaseCommand $command, RuntimeConfig $runtimeConfig)
    {
        parent::__construct($command, $runtimeConfig);
        $this->iconHandler = new UnicodeIcon($this->getRuntimeConfig());
    }


    /**
     * Setup of the enhancement should take place here. This function will be called in the BaseCommand's initialize() function
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * Logic that needs to be hooked in before the command's run() function is invoked (i.e. after construction but before
     * initialization) should be placed here.  The function will be called by the BaseCommand's preRun() function
     *
     * @param OutputInterface $output
     */
    public function preRun(OutputInterface $output)
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->getRuntimeConfig()->setUnicodeIconSupport(false);
            return;
        }

        $this->getRuntimeConfig()->setUnicodeIconSupport(true);
        return;


    }

    /**
     * Cleanup logic that is to be executed after the command has been run should be implemented here. This function will
     * be called BaseCommand's postRun() function
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param int|null $exitCode
     */
    public function postRun(InputInterface $input, OutputInterface $output, $exitCode)
    {
        // TODO: Implement postRun() method.
    }

    /**
     * @return UnicodeIcon
     */
    private function getIconHandler()
    {
        return $this->iconHandler;
    }

    /**
     * @return Icon
     */
    public function createIcon()
    {
        return new Icon($this->getIconHandler());
    }


}