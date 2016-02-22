<?php


use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Afrihost\BaseCommandBundle\Helper\Logging\LoggingEnhancement;
use Afrihost\BaseCommandBundle\Tests\Fixtures\ConfigDuringExecuteCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\LoggingCommand;
use Monolog\Logger;

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
        $logfileName = 'defaultFormatterTest.log';
        $this->cleanUpLogFile($logfileName);

        $command = $this->registerCommand(new LoggingCommand());
        $command->setLogFilename($logfileName);
        $commandTester = $this->executeCommand($command);

        // Test default console format
        $this->assertRegExp(
            '/20\d\d-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d \[WARNING\]: WARNING/',
            $commandTester->getDisplay(),
            'Expected default log entry format for Console Log not found'
        );

        // Test default logfile format
        $this->assertRegExp(
            '/20\d\d-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d \[WARNING\]: WARNING/',
            $this->getLogfileContents($logfileName),
            'Expected default log entry format for Console Log not found'
        );

        $this->cleanUpLogFile($logfileName);
    }

    public function testCustomLogLineFormats()
    {
        $logfileName = 'customFormatterTest.log';
        $this->cleanUpLogFile($logfileName);

        $command = $this->registerCommand(new LoggingCommand());
        $command->setLogFilename($logfileName);
        $command->setConsoleLogLineFormat('Writing message to console: %message%');
        $command->setFileLogLineFormat('Writing message to log file: %message%');
        $commandTester = $this->executeCommand($command);

        // Test  console format
        $this->assertRegExp(
            '/Writing message to console: The quick brown fox jumps over the lazy dog/',
            $commandTester->getDisplay(),
            'Expected custom log entry format for Console Log not found'
        );

        // Test default logfile format
        $this->assertRegExp(
            '/Writing message to log file: The quick brown fox jumps over the lazy dog/',
            $this->getLogfileContents($logfileName),
            'Expected default log entry format for Console Log not found'
        );

        $this->cleanUpLogFile($logfileName);
    }

    public function testProvidingNullLineFormatToGetMonologDefault()
    {
        $logfileName = 'nullFormatterTest.log';
        $this->cleanUpLogFile($logfileName);

        $command = $this->registerCommand(new LoggingCommand());
        $command->setLogFilename($logfileName);
        $command->setConsoleLogLineFormat(null);
        $command->setFileLogLineFormat(null);
        $commandTester = $this->executeCommand($command);

        // Generate what the default format looks like
        $lineFormatter = new \Monolog\Formatter\LineFormatter(null);
        $record = array(
            'message' => 'The quick brown fox jumps over the lazy dog',
            'context' => array(),
            'level' => Logger::EMERGENCY,
            'level_name' => Logger::getLevelName(Logger::EMERGENCY),
            'channel' => $command->getLogger()->getName(),
            'datetime' => new \DateTime('1970-01-01 00:00:00'),
            'extra' => array(),
        );
        $exampleLine = $lineFormatter->format($record);
        $exampleLine = trim(str_replace('[1970-01-01 00:00:00]', '', $exampleLine)); // strip out date as this wont match

        // Test  console format
        $this->assertRegExp(
            '/'.$exampleLine.'/',
            $commandTester->getDisplay(),
            'Console log line format does not seem to match the Monolog default'
        );

        // Test default logfile format
        $this->assertRegExp(
            '/'.$exampleLine.'/',
            $this->getLogfileContents($logfileName),
            'File log line format does not seem to match the Monolog default'
        );

        $this->cleanUpLogFile($logfileName);
    }

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

        $this->assertRegExp(
            '/The quick brown fox jumps over the lazy dog/',
            $this->getLogfileContents($command->getLogFilename(false)),
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
