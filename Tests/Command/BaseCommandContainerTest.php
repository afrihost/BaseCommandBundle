<?php

use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\MissingInitializeCommand;
use Monolog\Logger;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class performs tests on BaseCommand that depend on a full Symfony application (together with a service container)
 * being bootstrapped. These are generally functional tests
 */
class BaseCommandContainerTest extends AbstractContainerTest
{

    /**
     * This test ensure that the BaseCommand has not added any unintentional output to the output buffer
     */
    public  function testNoErroneousOutput()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $commandTester = $this->executeCommand($command);

        $this->assertEquals(
            'Hello World',
            $commandTester->getDisplay(),
            'Command console output contains unexpected content'
        );
    }

    /**
     * Invoking the setLocking after the lock handler has been initialised has not affect and thus an exception should
     * be thrown
     *
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Lock handler is already initialised
     */
    public function testSetLockingAfterInitializeException()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        EncapsulationViolator::invokeMethod($command, 'setLocking', array(false));
    }

    public function testDefaultLockingTrue()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        $this->assertTrue(
            EncapsulationViolator::invokeMethod($command, 'isLocking'),
            'Locking should be enabled by default'
        );
    }

    public function testSetLocking(){
        $command = $this->registerCommand(new HelloWorldCommand());
        EncapsulationViolator::invokeMethod($command, 'setLocking', array(false));
        $this->executeCommand($command);

        $this->assertFalse(EncapsulationViolator::invokeMethod($command, 'isLocking'));
    }

    public function testSetMemoryLimit()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        EncapsulationViolator::invokeMethod($command, 'setMemoryLimit', array('1024M'));
        $this->executeCommand($command);

        $this->assertEquals('1024M', ini_get('memory_limit'));
    }

    public function testGetAndSetAllowMultipleExecution()
    {
        $command = new HelloWorldCommand();
        EncapsulationViolator::invokeMethod($command, 'setAllowMultipleExecution', array(true));
        $this->assertTrue(
            EncapsulationViolator::invokeMethod($command, 'isMultipleExecutionAllowed'),
            'The value for allowMultipleExecution that we just set is different the the value we read'
        );
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage BaseCommand not initialized
     */
    public function testExceptionForNotCallingParentInitialize()
    {
        $command = $this->registerCommand(new MissingInitializeCommand());
        $this->executeCommand($command);
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Log Messages can only be pushed on the preInit queue prior to initialization.
     */
    public function testExceptionOnPushLogMessageOnPreInitQueueAfterInitialize()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);
        $command->pushLogMessageOnPreInitQueue(Logger::EMERGENCY, 'Logging this should cause an exception');
    }
}
