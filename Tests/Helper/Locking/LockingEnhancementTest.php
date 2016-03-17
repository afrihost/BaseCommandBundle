<?php


use Afrihost\BaseCommandBundle\Tests\Fixtures\App\TestKernel;
use Afrihost\BaseCommandBundle\Tests\Fixtures\EncapsulationViolator;
use Afrihost\BaseCommandBundle\Tests\Fixtures\HelloWorldCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\LockCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class LockingEnhancementTest extends AbstractContainerTest
{
    static protected $homeLocationBackup;
    static protected $testHomeLocation;

    /**
     * Some of the tests in this class attempt to create files relative to the user's home directory. This fixture attempts
     * to override the $HOME environment to a directory within the Test folder to attempt to keep the tests clean
     */
    public static function setUpBeforeClass()
    {
        // Backup the current value to be restored after the tests
        self::$homeLocationBackup = getenv('HOME');

        // Get the location of the test Application
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        self::$testHomeLocation = $kernel->getRootDir().DIRECTORY_SEPARATOR.'externals'.DIRECTORY_SEPARATOR.'home';

        // Override the current value
        putenv('HOME='.self::getTestHomeLocation());
    }

    /**
     * Cleans up the overriding of the $HOME environment variable done by setUpBeforeClass()
     */
    public static function tearDownAfterClass()
    {
        if(!is_null(self::$homeLocationBackup)){
            putenv('HOME='.self::getTestHomeLocation());
        }
    }


    public function testSetLockFileFolderTilde()
    {
        $this->removeAllLockFiles(self::getTestHomeLocation());

        $command = $this->registerCommand(new LockCommand());
        EncapsulationViolator::invokeMethod($command, 'setLockFileFolder', array('~/locks'));
        $commandTester = $this->executeCommand($command);

        $this->assertEquals(
            self::getTestHomeLocation().DIRECTORY_SEPARATOR.'locks',
            EncapsulationViolator::invokeMethod($command, 'getLockFileFolder'),
            'The lock file location that was set relative to the user\'s home directory does not seem to have been returned'
        );

        $this->assertTrue(
            $this->lockFileExists($this->getTestHomeLocation(), 'LockCommand.php'),
            'A lock file does not seem to have been created relative to the user\'s home directory '
        );

        $this->assertContains(
            'Sorry, can\'t get the lock. Bailing out!',
            $commandTester->getDisplay(),
            'The lock does not seem to have been acquired correctly as the same command was run twice at the same time '.
                'without error'
        );

        $this->removeAllLockFiles(self::getTestHomeLocation());
    }

    public function testSetLockFileFolderAbsolute()
    {
        $lockFolderName = $this->application->getKernel()->getRootDir() . '/externals/absolute';
        $this->removeAllLockFiles($lockFolderName);

        $command = $this->registerCommand(new LockCommand());

        EncapsulationViolator::invokeMethod($command, 'setLockFileFolder', array($lockFolderName));
        EncapsulationViolator::invokeMethod($command, 'setLocking', array(true));
        $commandTester = $this->executeCommand($command);

        $this->assertEquals($lockFolderName, EncapsulationViolator::invokeMethod($command, 'getLockFileFolder'));

        $this->assertTrue(
            $this->lockFileExists($lockFolderName, 'LockCommand.php'),
            'A lock file does not seem to have been created relative to the user\'s home directory '
        );

        $this->assertContains(
            'Sorry, can\'t get the lock. Bailing out!',
            $commandTester->getDisplay(),
            'The lock does not seem to have been acquired correctly as the same command was run twice at the same time '.
            'without error'
        );

        $this->removeAllLockFiles($lockFolderName);
    }

    public function testSetLockFileFolderRelative()
    {
        $expectedFolder = $this->application->getKernel()->getRootDir() . '/externals/relative';
        $this->removeAllLockFiles($expectedFolder);

        $command = $this->registerCommand(new LockCommand());
        EncapsulationViolator::invokeMethod($command, 'setLockFileFolder', array('externals/relative'));
        $commandTester = $this->executeCommand($command);

        $this->assertEquals($expectedFolder, EncapsulationViolator::invokeMethod($command, 'getLockFileFolder'));

        $this->assertTrue(
            $this->lockFileExists($expectedFolder, 'LockCommand.php'),
            'A lock file does not seem to have been created relative to the user\'s home directory '
        );

        $this->assertContains(
            'Sorry, can\'t get the lock. Bailing out!',
            $commandTester->getDisplay(),
            'The lock does not seem to have been acquired correctly as the same command was run twice at the same time '.
            'without error'
        );

        $this->removeAllLockFiles($expectedFolder);
    }

    public function testGetAndSetLocking(){
        $command = $this->registerCommand(new HelloWorldCommand());
        EncapsulationViolator::invokeMethod($command, 'setLocking', array(false));
        $this->executeCommand($command);

        $this->assertFalse(
            EncapsulationViolator::invokeMethod($command, 'isLocking'),
            'The locking value that we just set was not returned'
        );
    }

    public function testSetLockingViaParameter()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command, array('--locking'=>'off'));
        $this->assertFalse(
            EncapsulationViolator::invokeMethod($command, 'isLocking'),
            'Locking was not turned off by parameter'
        );

        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command, array('--locking'=>'on'));
        $this->assertTrue(
            EncapsulationViolator::invokeMethod($command, 'isLocking'),
            'Locking was not turned on by parameter'
        );
    }

    /**
     * @expectedException \Afrihost\BaseCommandBundle\Exceptions\BaseCommandException
     * @expectedExceptionMessage Lock handler is already initialised
     */
    public function testSetLockingAfterInitializeException()
    {
        $command = $this->registerCommand(new HelloWorldCommand());
        $this->executeCommand($command);
        EncapsulationViolator::invokeMethod($command, 'setLocking', array(false));
    }

    /**
     * Deletes all files that look like they may have been created by the Symfony LockHandler that are in the provided
     * directory and its sub directories
     *
     * @param string $directory Where to look for the lock files
     * @param string $baseName  The name of the lock passed to the LockHandler's constructor (supports wildcards)
     */
    protected function removeAllLockFiles($directory, $baseName = '*')
    {
        $fs = new Filesystem();
        if($fs->exists($directory)){
            $finder = new Finder();
            $lockFiles = $finder->in($directory)->name('sf.'.$baseName.'.*.lock')->files()->ignoreDotFiles(true);
            $fs->remove($lockFiles);
        }
    }

    /**
     * Confirms if a file exists in the provided directory that looks like is was generated by the Symfony LockHandler.
     * The search is not recursive.
     *
     * @param string $directory Where to look for the lock files
     * @param string $baseName he name of the lock passed to the LockHandler's constructor (supports wildcards)
     *
     * @return bool
     */
    protected function lockFileExists($directory, $baseName)
    {
        $fs = new Filesystem();
        if(!$fs->exists($directory)){
            return false;
        }

        $finder = new Finder();
        $lockFiles = $finder->in($directory)->name('sf.'.$baseName.'.*.lock')->files()->ignoreDotFiles(true)->depth('==0');
        return $fs->exists($lockFiles);
    }

    /**
     * @return string
     */
    protected static function getTestHomeLocation()
    {
        return self::$testHomeLocation;
    }
}
