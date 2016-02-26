<?php


use Afrihost\BaseCommandBundle\Tests\Fixtures\ConfigDuringExecuteCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\LoggingCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\MissingConfigureCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\MissingInitializeCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\UninitializedRuntimeConfigCommand;
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
     * @expectedException Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage need to call parent::configure()
     */
    public function testMissingConfigureException()
    {
        $command = $this->registerCommand(new MissingConfigureCommand());
        $this->executeCommand($command);
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

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Make sure that you call parent::configure()
     */
    public function testFriendlyExceptionForUninitializedRuntimeConfig()
    {
        $this->registerCommand(new UninitializedRuntimeConfigCommand());
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
}
