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

}
