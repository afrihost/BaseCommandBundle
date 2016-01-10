<?php


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Monolog\Logger;

/**
 * This class performs standalone tests on BaseCommand that are not dependant on a Symfony application or container.
 * These tend to be more unit tests
 */
class BaseCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BaseCommand
     */
    protected $command;

    protected function setUp()
    {
        $this->command = new HelloWorldCommand();
    }

    public function testDefaultLogLevel()
    {
        $this->assertEquals(Logger::WARNING, $this->command->getLogLevel());
    }

    public function testDefaultLogLevelName()
    {
        $this->assertEquals('WARNING', $this->command->getLevelName());
    }

    /* ################################################################# *
     * Test protected methods intended for user that overrides the class *
     * ################################################################# */

    public function testDefaultIsLogToConsoleTrue()
    {
        $this->assertTrue(
            EncapsulationViolator::invokeMethod($this->command, 'isLogToConsole'),
            'Logging to console should be on by default'
        );
    }

    /**
     * Invoking the setLogToConsole method with a parameter that is not a boolean should throw an exception
     *
     * @expectedException \Exception
     */
    public function testSetLogToConsoleNonBooleanException()
    {
        EncapsulationViolator::invokeMethod($this->command, 'setLogToConsole', array(42));
    }

    public function testSetLogToConsole()
    {
        EncapsulationViolator::invokeMethod($this->command, 'setLogToConsole', array(false));
        $this->assertFalse(EncapsulationViolator::invokeMethod($this->command, 'isLogToConsole'));
    }

    /**
     * Invoking the setLocking method with a parameter that is not a boolean should throw an exception
     *
     * @expectedException \Exception
     */
    public function testSetLockingNonBooleanException()
    {
        EncapsulationViolator::invokeMethod($this->command, 'setLocking', array(42));
    }

    /**
     * @expectedException \Exception
     */
    public function testSetInvalidLogLevelException()
    {
        EncapsulationViolator::invokeMethod($this->command, 'setLogLevel', array('INVALID'));
    }






}
