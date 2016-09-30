<?php


use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Afrihost\BaseCommandBundle\Helper\Logging\LoggingEnhancement;
use Afrihost\BaseCommandBundle\Tests\Fixtures\ConfigDuringExecuteCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\LoggingCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\LogLineBreakCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\LogPreInitCommand;
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
        EncapsulationViolator::invokeMethod($command, 'setLogFilename', array($logfileName));
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
        EncapsulationViolator::invokeMethod($command, 'setLogFilename', array($logfileName));
        EncapsulationViolator::invokeMethod($command, 'setConsoleLogLineFormat', array('Writing message to console: %message%'));
        EncapsulationViolator::invokeMethod($command, 'setFileLogLineFormat', array('Writing message to log file: %message%'));
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
        EncapsulationViolator::invokeMethod($command, 'setLogFilename', array($logfileName));
        EncapsulationViolator::invokeMethod($command, 'setConsoleLogLineFormat', array(null));
        EncapsulationViolator::invokeMethod($command, 'setFileLogLineFormat', array(null));
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

    public function testDefaultLogFileExtensionDefault()
    {

        $logFilename = 'LoggingCommand.php.log.txt';
        $this->cleanUpLogFile($logFilename);

        $command = $this->registerCommand(new LoggingCommand());
        $commandTester = $this->executeCommand($command);

        $this->assertEquals(
            '.log.txt',
            EncapsulationViolator::invokeMethod($command, 'getDefaultLogFileExtension'),
            'If no default log file extension is defined, it should default to .log.txt'
        );

        $this->assertTrue(
            (strpos($command->getLogFilename(false), '.log.txt') !== false),
            'If no log filename is specified then the automatically generated filename should end in the DefaultLogFileExtension'
        );

        $this->assertTrue(
            $this->doesLogfileExist($logFilename),
            'A log file with the expected name (and extension) was not created'
        );

        $this->cleanUpLogFile($logFilename);
    }

    public function testCustomDefaultLogFileExtension()
    {
        $logFilename = 'LoggingCommand.php.junk';
        $this->cleanUpLogFile($logFilename);

        $command = $this->registerCommand(new LoggingCommand());
        EncapsulationViolator::invokeMethod($command, 'setDefaultLogFileExtension', array('.junk'));
        $commandTester = $this->executeCommand($command);

        $this->assertTrue(
            (strpos($command->getLogFilename(false), '.junk') !== false),
            'If no log filename is specified and a custom default extension is supplied then the automatically generated '.
            'filename should end in the extension provided'
        );

        $this->assertTrue(
            $this->doesLogfileExist($logFilename),
            'A log file with the expected name (and extension) was not created'
        );

        $this->cleanUpLogFile($logFilename);
    }

    public function testGetAndSetDefaultLogFileExtension()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        EncapsulationViolator::invokeMethod($command, 'setDefaultLogFileExtension', array('.log'));
        $this->assertEquals(
            '.log',
            EncapsulationViolator::invokeMethod($command, 'getDefaultLogFileExtension'),
            'The DefaultLogFileExtension that we just set was not returned'
        );
    }

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
        EncapsulationViolator::invokeMethod($command, 'setLogFilename', array($name));

        $this->executeCommand($command, array(), true);
        $this->assertEquals(
            $this->application->getKernel()->getLogDir().DIRECTORY_SEPARATOR.$name,
            $command->getLogFilename(),
            'Getter did not return logfile name we just set'
        );

        $this->assertTrue($this->doesLogfileExist($name), 'A logfile with the custom name we set was not created');

        $this->cleanUpLogFile($name);
    }

    public function testGetLogFilenameWithoutPath()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);
        $this->assertTrue(
            strpos(EncapsulationViolator::invokeMethod($command, 'getLogFilename', array(false)), DIRECTORY_SEPARATOR) === false,
            'The filename without the path should not contain directory separators'
        );
    }

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

    public function testChangeLogLevelViaParameter()
    {
        $command = $this->registerCommand(new HelloWorldCommand());

        // Test with long option name
        $commandTester = $this->executeCommand($command, array('--log-level'=>'DEBUG'));
        $this->assertEquals(
            Logger::DEBUG,
            $command->getLogLevel(),
            'Log level does not appear to have been changed to DEBUG by the commandline parameter');

        $this->assertRegExp(
            '/LOG LEVEL CHANGED VIA PARAMETER:/',
            $commandTester->getDisplay(),
            'Log level change not outputted to console'
        );

        // Test with shortcut option
        $command = $this->registerCommand(new HelloWorldCommand());
        $commandTester = $this->executeCommand($command, array('-l'=>'INFO'));
        $this->assertEquals(
            Logger::INFO,
            $command->getLogLevel(),
            'Log level does not appear to have been changed to INFO by the commandline shortcut parameter');
    }

    public function testDisableFileLogging()
    {
        $logFilename = 'LoggingCommand.php.log.txt';
        $this->cleanUpLogFile($logFilename);

        $command = $this->registerCommand(new LoggingCommand());
        EncapsulationViolator::invokeMethod($command, 'setLogToFile', array(false));

        $this->assertFalse(
            $this->doesLogfileExist($logFilename),
            'Log to file was disabled but a log file was still created'
        );
    }

    public function testDisableConsoleLogging()
    {
        $command = $this->registerCommand(new LoggingCommand());
        EncapsulationViolator::invokeMethod($command, 'setLogToConsole', array(false));
        $commandTester = $this->executeCommand($command);

        $this->assertEmpty(
            $commandTester->getDisplay(),
            'Logging to console was disabled so there should have been no output'
        );
    }

    public function testGetAndSetLogToFile()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        EncapsulationViolator::invokeMethod($command, 'setLogToFile', array(false));
        $this->assertFalse(
            EncapsulationViolator::invokeMethod($command, 'isLogToFile'),
            'The the value that we just set for LogToFile was not returned'
        );
    }

    public function testGetAndSetLogToConsole()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        EncapsulationViolator::invokeMethod($command, 'setLogToConsole', array(false));
        $this->assertFalse(
            EncapsulationViolator::invokeMethod($command, 'isLogToConsole'),
            'The the value that we just set for LogToFile was not returned'
        );
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Logger is already initialised
     */
    public function testExceptionOnSetLogToFileAfterInitialize()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);
        EncapsulationViolator::invokeMethod($command, 'setLogToFile', array(false));
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Logger is already initialised
     */
    public function testExceptionOnSetLogToConsoleAfterInitialize()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);
        EncapsulationViolator::invokeMethod($command, 'setLogToConsole', array(false));
    }

    public function testPushLogMessageOnPreInitQueue()
    {
        $command = $this->registerCommand(new LogPreInitCommand());
        $commandTester = $this->executeCommand($command);
        $this->assertContains(
            'This was logged in before parent::initialize()',
            $commandTester->getDisplay(),
            'The log entry that was put on the preinit queue does not seem to have been outputted'
        );
    }

    public function testChangeLogFilenameViaParameter()
    {
        $logFilename = 'customName.log';
        $command = $this->registerCommand(new LoggingCommand());


        $commandTester = $this->executeCommand($command, array('--log-filename'=>$logFilename));
        $this->assertEquals(
            $logFilename,
            $command->getLogFilename(false),
            'Log level does not appear to have been changed to '.$logFilename.' by the commandline parameter');

        $this->assertTrue(
            $this->doesLogfileExist($logFilename),
            'A log file with the name '.$logFilename.' does not seem to have been created'
        );

        $this->assertContains(
            'LOG FILENAME CHANGED VIA PARAMETER:',
            $commandTester->getDisplay(),
            'Log filename change was not outputted to console'
        );

    }

    public function testConsoleLineBreakDefaultOn()
    {
        $command = $this->registerCommand(new LogLineBreakCommand());
        $commandTester = $this->executeCommand($command);

        $this->assertTrue(
            EncapsulationViolator::invokeMethod($command, 'getConsoleLogLineBreaks'),
            'Line Beaks should be enabled for the console log by default'
        );

        $this->assertContains(
            'first line'.PHP_EOL.'second line',
            $commandTester->getDisplay(),
            'The outputted log entry does not contain a new line character'
        );
    }

    public function testFileLineBreaksDefaultOff()
    {
        $this->cleanUpLogFile('LogLineBreakCommand.php.log.txt');

        $command = $this->registerCommand(new LogLineBreakCommand());
        $this->executeCommand($command);

        $this->assertFalse(
            EncapsulationViolator::invokeMethod($command, 'getFileLogLineBreaks'),
            'Line Breaks should be disabled for the file log by default'
        );

        $this->assertContains(
            'first line second line',
            $this->getLogfileContents('LogLineBreakCommand.php.log.txt'),
            'The outputted log does not contain the entry with the new line character stripped'
        );

        $this->cleanUpLogFile('LogLineBreakCommand.php.log.txt');
    }

    public function testFileLineBreaksEnable()
    {
        $this->cleanUpLogFile('LogLineBreakCommand.php.log.txt');

        $command = $this->registerCommand(new LogLineBreakCommand());
        EncapsulationViolator::invokeMethod($command, 'setFileLogLineBreaks', array(true));
        $this->executeCommand($command);

        $this->assertContains(
            'first line'.PHP_EOL.'second line',
            $this->getLogfileContents('LogLineBreakCommand.php.log.txt'),
            'The file log does not contain the entry with the new line character even though we have just enabled line breaks'
        );

        $this->cleanUpLogFile('LogLineBreakCommand.php.log.txt');
    }

    public function testConsoleLineBreaksDisable()
    {
        $command = $this->registerCommand(new LogLineBreakCommand());
        EncapsulationViolator::invokeMethod($command, 'setConsoleLogLineBreaks', array(false));
        $commandTester = $this->executeCommand($command);

        $this->assertContains(
            'first line second line',
            $commandTester->getDisplay(),
            'The console log has not stripped the new lin character even though we disabled line breaks'
        );
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Cannot set Log Line Breaks option for file. Logger is already initialised
     */
    public function testExceptionOnSetFileLineBreaksAfterInitialize()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);
        EncapsulationViolator::invokeMethod($command, 'setFileLogLineBreaks', array(true));
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Cannot set Log Line Breaks option for console. Logger is already initialised
     */
    public function testExceptionOnSetConsoleLineBreaksAfterInitialize()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);
        EncapsulationViolator::invokeMethod($command, 'setConsoleLogLineBreaks', array(false));
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage FileLogLineBreaks setting must be a boolean
     */
    public function testExceptionOnSetFileLineBreaksNonBoolean()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);
        EncapsulationViolator::invokeMethod($command, 'setFileLogLineBreaks', array('fish'));
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage ConsoleLogLineBreaks setting must be a boolean
     */
    public function testExceptionOnSetConsoleLineBreaksBreaksNonBoolean()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);
        EncapsulationViolator::invokeMethod($command, 'setConsoleLogLineBreaks', array('fish'));
    }
}
