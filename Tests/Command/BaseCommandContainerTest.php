<?php

use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\LoggingCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Afrihost\BaseCommandBundle\Tests\Fixtures\App\TestKernel;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * This class performs tests on BaseCommand that depend on a full Symfony application (together with a service container)
 * being being bootstrapped. These are generally functional tests
 */
class BaseCommandContainerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Application
     */
    protected $application;

    protected function setUp()
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $this->application = new Application($kernel);
    }

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

    public function testGetLoggerReturnsLogger()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);

        $this->assertInstanceOf(
            'Monolog\Logger',
            $command->getLogger(),
            'BaseCommand::getLogger() should return an instance of Monolog\Logger'
        );
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

    public function testDefaultLogFileName()
    {
        $this->cleanUpLogFile('LoggingCommand.php.log.txt');

        $command = $this->registerCommand(new LoggingCommand());
        $this->executeCommand($command, array(), true);
        $this->assertTrue(
            $this->doesLogfileExist('LoggingCommand.php.log.txt'),
            "Logfile called 'LoggingCommand.php.log.txt' not created"
        );

        $this->cleanUpLogFile('LoggingCommand.php.log.txt');
    }

    public function testSetLogfileName()
    {
        $name = 'foo.log';
        $this->cleanUpLogFile($name);

        $command = $this->registerCommand(new LoggingCommand());
        $command->setLogFilename($name);

        $this->assertEquals(
            $this->application->getKernel()->getLogDir().DIRECTORY_SEPARATOR.$name,
            $command->getLogFilename(),
            'Getter did not return logfile name we just set'
        );

        $this->executeCommand($command, array(), true);
        $this->assertTrue($this->doesLogfileExist($name), 'A logfile with the custom name we set was not created');

        $this->cleanUpLogFile($name);
    }

    /* ################################################################# *
     * Test protected methods intended for user that overrides the class *
     * ################################################################# */

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

    /**
     * Invoking the setLocking after the lock handler has been initialised has not affect and thus an exception should
     * be thrown
     *
     * @expectedException \Exception
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



    /* ################ *
     *  Helper Methods  *
     * ################ */

    /**
     * Registers the provided command with the with the Application and then returns this registered instantiation
     *
     * @param BaseCommand $command
     *
     * @return BaseCommand
     */
    protected function registerCommand(BaseCommand $command)
    {
        $this->application->add($command);
        return $this->application->find($command->getName());
    }

    /**
     * Runs the provided command using a CommandTester
     *
     * @param BaseCommand $command
     * @param array       $input
     * @param bool|false  $swallowOutput if phpunit leaks the command output, use this to contain it in its own buffer
     *
     * @return CommandTester
     */
    protected function executeCommand(BaseCommand $command, array $input = array(), $swallowOutput = false)
    {
        $commandTester = new CommandTester($command);

        if($swallowOutput){
            ob_start();
        }
        $commandTester->execute($input);
        if($swallowOutput){
            ob_end_clean();
        }

        return $commandTester;
    }

    /**
     * Deletes a file with the provided name from the test application's log directory if it exists. This is used to
     * isolate tests on logfile handlers
     *
     * @param string $name relative to the application's log directory
     *
     * @return bool whether a file was deleted
     */
    protected function cleanUpLogFile($name)
    {
        $fullName = $this->application->getKernel()->getLogDir().DIRECTORY_SEPARATOR.$name;
        if (file_exists($fullName)) {
            return unlink($fullName);
        }
        return false;
    }

    /**
     * Checks if a file relative to the test application's log directory exists
     *
     * @param $name
     *
     * @return bool
     */
    protected function doesLogfileExist($name){
        return file_exists($this->application->getKernel()->getLogDir().DIRECTORY_SEPARATOR.$name);
    }
}
