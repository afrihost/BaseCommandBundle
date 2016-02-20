<?php


use Afrihost\BaseCommandBundle\Tests\Fixtures\ConfigDuringExecuteCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\LoggingCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\MissingConfigureCommand;
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
     * @expectedException Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     */
    public function testMissingConfigureException()
    {
        $command = $this->registerCommand(new MissingConfigureCommand());
        $this->executeCommand($command);
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


        $this->executeCommand($command, array(), true);
        $this->assertEquals(
            $this->application->getKernel()->getLogDir().DIRECTORY_SEPARATOR.$name,
            $command->getLogFilename(),
            'Getter did not return logfile name we just set'
        );

        $this->assertTrue($this->doesLogfileExist($name), 'A logfile with the custom name we set was not created');

        $this->cleanUpLogFile($name);
    }

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
            '/LOG LEVEL CHANGED:/',
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

    public function testSetLockFileFolderRelative()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        EncapsulationViolator::invokeMethod($command, 'setLockFileFolder', array('externals/storage'));
        $this->executeCommand($command);

        $expectedFolder = $this->application->getKernel()->getRootDir() . '/externals/storage';
        $this->assertEquals($expectedFolder, EncapsulationViolator::invokeMethod($command, 'getLockFileFolder'));

        // Cleanup:
        $fs = new Filesystem();
        $fs->remove($expectedFolder);
    }

    public function testSetLockFileFolderStaticSlash()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $slashFolderName = $this->application->getKernel()->getRootDir() . '/externals/slash/storage';
        EncapsulationViolator::invokeMethod($command, 'setLockFileFolder', array($slashFolderName));
        $this->executeCommand($command);

        $this->assertEquals($slashFolderName, EncapsulationViolator::invokeMethod($command, 'getLockFileFolder'));

        // Cleanup:
        $fs = new Filesystem();
        $fs->remove($slashFolderName);
    }

    public function testSetLockFileFolderStaticTilde()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        EncapsulationViolator::invokeMethod($command, 'setLockFileFolder', array('~/storage'));
        $this->executeCommand($command);

        $this->assertEquals('~/storage', EncapsulationViolator::invokeMethod($command, 'getLockFileFolder'));

        // Cleanup:
        $fs = new Filesystem();
        $fs->remove('~/storage');
    }

    public function testSetMemoryLimit()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        EncapsulationViolator::invokeMethod($command, 'setMemoryLimit', array('1024M'));
        $this->executeCommand($command);

        $this->assertEquals('1024M', ini_get('memory_limit'));
    }
}
