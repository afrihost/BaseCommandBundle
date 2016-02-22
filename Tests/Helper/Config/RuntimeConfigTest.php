<?php

use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\LoggingCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\MultipleExecutionAllowedCommand;
use Monolog\Logger;

/**
 * Although this class contains some functional tests, it focuses only on logic implemented within the RuntimeConfig
 * class. Other test classes (notably those for enhancement classes) may have similar functional tests that ensure that
 * the functionality of this class is executed through their interface
 */
class RuntimeConfigTest extends AbstractContainerTest
{

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage You are attempting execute the same command object twice
     */
    public function testExceptionOnDoubleExecution()
    {
        $command = $this->registerCommand(new LoggingCommand());
        $this->executeCommand($command);
        $commandTester = $this->executeCommand($command);
    }

    public function testAllowMultipleExecutionDefaultFalse()
    {
        $runtimeConfig = new RuntimeConfig(new HelloWorldCommand());
        $this->assertFalse(
            $runtimeConfig->isMultipleExecutionAllowed(),
            'Multiple Execution should not be allowed by default'
        );
    }

    public function testGetAndSetAllowMultipleExecution()
    {
        $runtimeConfig = new RuntimeConfig(new HelloWorldCommand());
        $runtimeConfig->setAllowMultipleExecution(true);
        $this->assertTrue(
            $runtimeConfig->isMultipleExecutionAllowed(),
            'The value for allowMultipleExecution that we just set is different the the value we read'
        );
    }

    /**
     * The actual test here is that once multiple execution has been explicitly allowed, no exceptions are thrown to prevent
     * it. This tests the opposite case to RuntimeConfigTest::testExceptionOnDoubleExecution()
     */
    public function testAllowingMultipleExecutions(){
        $command = $this->registerCommand(new MultipleExecutionAllowedCommand());

        try{
            $commandTester = $this->executeCommand($command);
            $this->assertEquals('1', $commandTester->getDisplay(), 'First execution did not output correct execution count');

            $commandTester = $this->executeCommand($command);
            $this->assertEquals('2', $commandTester->getDisplay(), 'Second execution did not output correct execution count');
        } catch (\Exception $e){
            $this->fail("Multiple Execution was not achievable. Exception thrown with the following message: ".$e->getMessage());
        }
    }

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
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage must be a boolean
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
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Logger is already initialised
     */
    public function testSetLogfileNameAfterInitializeException()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        EncapsulationViolator::invokeMethod($command, 'setLogFilename', array('foo.log.txt'));
    }

    // TODO Test getLogFilename() without path

    // TODO Test Default of Default File Extension

    // TODO Test Exception when setting default file extension after initialize

    /**
     * Invoking the setLogToConsole after the handler has been initialised has not affect and thus an exception should
     * be thrown
     *
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Logger is already initialised
     */
    public function testSetLogToConsoleAfterInitializeException()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        EncapsulationViolator::invokeMethod($command, 'setLogToConsole', array(false));
    }

    // TODO test setLogToFile after initialise exception

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage is not a valid LOGLEVEL
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
