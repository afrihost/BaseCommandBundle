<?php


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Afrihost\BaseCommandBundle\Tests\Fixtures\App\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * This class bootstraps a minimal Symfony application to support tests that depend on a full Symfony application (together
 * with a service container) being available. Such tests are generally functional tests. Furthermore this class provides
 * helper functions to facilitate writing tests that interact with this test application. Tests requiring this functionality
 * should be implemented in a subclass of this class
 */
abstract class AbstractContainerTest extends PHPUnit_Framework_TestCase
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

        // Without this, the application will call the PHP exit() function
        $this->application->setAutoExit(false);
    }

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
    protected function doesLogfileExist($name)
    {
        return file_exists($this->application->getKernel()->getLogDir().DIRECTORY_SEPARATOR.$name);
    }

    /**
     * Wrapper function to get whole contents of a particular log file as a string
     *
     * @param $name
     *
     * @return string
     * @throws Exception is the logfile does not exist
     */
    protected function getLogfileContents($name)
    {
        if(! $this->doesLogfileExist($name)){
            throw new \Exception('Could not file logfile with name \''.$name.'\' to read contents from for test');
        }

        return file_get_contents($this->application->getKernel()->getLogDir().DIRECTORY_SEPARATOR.$name);
    }

}