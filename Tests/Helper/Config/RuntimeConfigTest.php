<?php


use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Monolog\Logger;

/**
 * Although this class contains some functional tests, it focuses only on logic implemented within the RuntimeConfig
 * class. Other test classes (notably those for enhancement classes) may have similar functional tests that ensure that
 * the functionality of this class is executed through their interface
 */
abstract class RuntimeConfigTest extends AbstractContainerTest
{

    // TODO Test Exception on Double Execute

    // TODO Test Exception on Execution Phase Advanced Backwards

    // TODO Test Get and Set Execution Phase

    public function testDefaultIsLogToConsoleTrue()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        $this->assertTrue(
            EncapsulationViolator::invokeMethod($command, 'isLogToConsole'),
            'Logging to console should be on by default'
        );
    }

    // TODO Test default is log to file is true

    /**
     * Invoking the setLogToConsole method with a parameter that is not a boolean should throw an exception
     *
     * @expectedException \Exception
     */
    public function testSetLogToConsoleNonBooleanException()
    {
        $command = new HelloWorldCommand();
        EncapsulationViolator::invokeMethod($command, 'setLogToConsole', array(42));
    }

    public function testSetLogToConsole()
    {
        $command = new HelloWorldCommand();
        EncapsulationViolator::invokeMethod($command, 'setLogToConsole', array(false));
        $this->assertFalse(EncapsulationViolator::invokeMethod($command, 'isLogToConsole'));
    }

    /**
     * @expectedException \Exception
     */
    public function testSetLogfileNameAfterInitializeException()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        $command->setLogFilename('foo.log.txt');
    }

    // TODO Test getLogFilename() without path

    // TODO Test Default of Default File Extension

    // TODO Test Exception when setting default file extension after initialize

    /**
     * Invoking the setLogToConsole after the handler has been initialised has not affect and thus an exception should
     * be thrown
     *
     * @expectedException \Exception
     */
    public function testSetLogToConsoleAfterInitializeException()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        EncapsulationViolator::invokeMethod($command, 'setLogToConsole', array(false));
    }

    // TODO test setLogToFile after initialise exception

    /**
     * @expectedException \Exception
     */
    public function testSetInvalidLogLevelException()
    {
        $command = new HelloWorldCommand();
        EncapsulationViolator::invokeMethod($command, 'setLogLevel', array('INVALID'));
    }

    public function testSetLogLevelBeforeInitialize()
    {
        $command = new HelloWorldCommand();
        EncapsulationViolator::invokeMethod($command, 'setLogLevel', array(Logger::DEBUG));
        $this->assertEquals(
            Logger::DEBUG,
            $command->getLogLevel(),
            'Log level does not seem to have been changed to DEBUG'
        );
    }

    // TODO Test exception when setFileLogLineFormat after initialize

    // TODO Test exception when setConsoleLogLineFormat after initialize

    // TODO Test using NULL as line format setting in config file (to use Monolog's default line formatter)

    // TODO Test friendly exception if container accessed be for initialised

}
