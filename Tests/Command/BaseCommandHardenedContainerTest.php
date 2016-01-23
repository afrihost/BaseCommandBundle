<?php

require_once('AbstractContainerTest.php');

use Afrihost\BaseCommandBundle\Tests\Fixtures\ConfigDuringExecuteCommand;

/**
 * The BaseCommand has functionality (such as configuring the PHP memory limit at runtime) that is is not common in other
 * 'normal' code and thus may not perform as expected on systems that have extensive security restrictions or hardening.
 * As this hardening sits outside the control of most users, the BaseCommand attempts to detect and alert this unusual
 * behaviour. The tests in this class are intended to be run on such a hardened system to test this detection code.
 * This is partially simulated by the ./test_hardened.sh script.
 *
 * RUNNING THESE TESTS ON A NON-HARDENED ENVIRONMENT WILL CAUSE THEM TO FAIL (I.E FALSE POSITIVE)
 */
class BaseCommandHardenedContainerTest extends AbstractContainerTest
{

    public  function testLogNoIniSetForMemoryLimit()
    {
        $command = $this->registerCommand(new ConfigDuringExecuteCommand());
        $commandTester = $this->executeCommand($command, array(), true);

        $this->assertRegExp(
            '/CANNOT SET MEMORY LIMIT/',
            $commandTester->getDisplay(),
            'If the PHP memory_limit setting cannot be changed due to the ini_set function being unavailable, this should '.
                'be noted in the log'
        );
    }

    public  function testLogNoIniSetForDisplayErrors()
    {
        $command = $this->registerCommand(new ConfigDuringExecuteCommand());
        $commandTester = $this->executeCommand($command, array(), true);

        $this->assertRegExp(
            '/CANNOT SET DISPLAY ERRORS',
            $commandTester->getDisplay(),
            'If the PHP display_errors setting cannot be changed due to the ini_set function being unavailable, this should '.
                'be noted in the log'
        );
    }
}