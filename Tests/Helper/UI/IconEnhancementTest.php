<?php
use Afrihost\BaseCommandBundle\Tests\Fixtures\IconCommand;

/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2016/03/03
 * Time: 7:07 AM
 */
class IconEnhancementTest extends AbstractContainerTest
{
    public function testIconOutput()
    {
        $command = $this->registerCommand(new IconCommand());
        $this->executeCommand($command);
        $commandTester = $this->executeCommand($command);
    }
}