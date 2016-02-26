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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Execution phase can only be advanced forward
     */
    public function testExceptionOnAdvanceExecutionPhaseBackwards()
    {
        $config = new RuntimeConfig(new HelloWorldCommand());
        $config->advanceExecutionPhase(RuntimeConfig::PHASE_POST_INITIALISE);
        $config->advanceExecutionPhase(RuntimeConfig::PHASE_INITIALISE);
    }

    public function testAdvanceAndGetExecutionPhase()
    {
        $config = new RuntimeConfig(new HelloWorldCommand());
        $config->advanceExecutionPhase(RuntimeConfig::PHASE_POST_INITIALISE);
        $this->assertEquals(
            RuntimeConfig::PHASE_POST_INITIALISE,
            $config->getExecutionPhase(),
            'The execution phase that we just set was not returned'
        );
    }

    public function testDefaultIsLogToConsoleTrue()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        $this->assertTrue(
            EncapsulationViolator::invokeMethod($command, 'isLogToConsole'),
            'Logging to console should be on by default'
        );
    }

    public function testDefaultIsLogToFileTrue()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        $this->assertTrue(
            EncapsulationViolator::invokeMethod($command, 'isLogToFile'),
            'Logging to file should be on by default'
        );
    }

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

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Logger is already initialised
     */
    public function testSetDefaultLogFileExceptionAfterInitializeException()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        EncapsulationViolator::invokeMethod($command, 'setDefaultLogFileExtension', array('.junk'));
    }

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

    /**
     * Invoking the setLogToFile after the handler has been initialised has not affect and thus an exception should
     * be thrown
     *
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Logger is already initialised
     */
    public function testExceptionOnSetLogToFileAfterInitialize()
    {
        $config = new RuntimeConfig(new HelloWorldCommand());
        $config->advanceExecutionPhase(RuntimeConfig::PHASE_POST_INITIALISE);
        $config->setLogToFile(false);
    }

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

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Cannot set new line format for file logging. Logger is already initialise
     */
    public function testExceptionOnSetFileLogLineFormatAfterInitialize()
    {
        $config = new RuntimeConfig(new HelloWorldCommand());
        $config->advanceExecutionPhase(RuntimeConfig::PHASE_POST_INITIALISE);
        $config->setFileLogLineFormat('some other format');
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Cannot set new line format for console logging. Logger is already initialised
     */
    public function testExceptionOnSetConsoleLogLineFormatAfterInitialize()
    {
        $config = new RuntimeConfig(new HelloWorldCommand());
        $config->advanceExecutionPhase(RuntimeConfig::PHASE_POST_INITIALISE);
        $config->setConsoleLogLineFormat('some other format');
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Cannot access the Container yet. It has not yet been initialised and set
     */
    public function testFriendlyExceptionWhenAccessingContainerBeforeItIsSet()
    {
        $config = new RuntimeConfig(new HelloWorldCommand());
        EncapsulationViolator::invokeMethod($config, 'getContainer');
    }
}
