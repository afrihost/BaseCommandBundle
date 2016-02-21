<?php


use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Afrihost\BaseCommandBundle\Helper\Logging\LoggingEnhancement;
use Afrihost\BaseCommandBundle\Tests\Fixtures\ConfigDuringExecuteCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\LoggingCommand;

class LoggingEnhancementTest extends AbstractContainerTest
{
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
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Cannot access logger. It is not yet initialised.
     */
    public function testExceptionOnAccessingUninitializedLogger()
    {
        $command = new HelloWorldCommand();
        $enhancement = new LoggingEnhancement($command, new RuntimeConfig($command));
        $enhancement->getLogger();
    }

    public function testLoggingToConsole()
    {
        $command = $this->registerCommand(new LoggingCommand());
        $commandTester = $this->executeCommand($command);

        $this->assertRegExp(
            '/The quick brown fox jumps over the lazy dog/',
            $commandTester->getDisplay(),
            'Expected output was not logged to console'
        );
    }

    public function testDefaultLineFormatter()
    {
        $command = $this->registerCommand(new LoggingCommand());
        $commandTester = $this->executeCommand($command);

        $this->assertRegExp(
            '/20\d\d-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d \[WARNING\]: WARNING/',
            $commandTester->getDisplay(),
            'Expected log entry format not found'
        );

        // TODO test default file line format
    }

    // TODO Test setting custom console log line format

    // TODO Test setting custom file log line format

    // TODO Test using monolog default line format for console log

    // TODO Test using monolog default line format for file log

    // TODO Test Set Default File Extension

    /**
     * If a log filename is not explicitly specified, one is generated from the name of the file in which the user's
     * Command is defined
     */
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


        $this->executeCommand($command, array(), true);
        $this->assertEquals(
            $this->application->getKernel()->getLogDir().DIRECTORY_SEPARATOR.$name,
            $command->getLogFilename(),
            'Getter did not return logfile name we just set'
        );

        $this->assertTrue($this->doesLogfileExist($name), 'A logfile with the custom name we set was not created');

        $this->cleanUpLogFile($name);
    }

    // TODO test getting log filename without full path

    public function testLoggingToFile()
    {
        $this->cleanUpLogFile('LoggingCommand.php.log.txt');

        $command = $this->registerCommand(new LoggingCommand());
        $this->executeCommand($command, array(), true);

        $logFileContents = file_get_contents(
            $this->application->getKernel()->getLogDir().DIRECTORY_SEPARATOR.'LoggingCommand.php.log.txt'
        );
        $this->assertRegExp(
            '/The quick brown fox jumps over the lazy dog/',
            $logFileContents,
            'Expected output was not logged to file'
        );

        $this->cleanUpLogFile('LoggingCommand.php.log.txt');
    }

    public function testLoggingOfLogLevelChangeAfterInitialize()
    {
        $command = $this->registerCommand(new ConfigDuringExecuteCommand());
        $commandTester = $this->executeCommand($command);
        $this->assertRegExp(
            '/LOG LEVEL CHANGED:/',
            $commandTester->getDisplay(),
            'If the log level is changed at runtime, this change should be logged'
        );
    }

    // ? TODO test get and set of DefaultLogFileExtension

    // TODO test disabling FileLogging for specific command

    // TODO test disabling Console logging for a specific command

}
