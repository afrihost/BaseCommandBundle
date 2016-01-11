<?php
namespace Afrihost\BaseCommandBundle\Helper\Logging\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This handler writes log entries to a provided Symfony OutputInterface object. It replaces our old approach of writing
 * directly to the STDOUT or output buffer streams using the StreamHandler. This approach is more compatible with the
 * rest of the tooling in the Symfony Framework
 */
class ConsoleHandler extends AbstractProcessingHandler
{

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output,  $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->output = $output;
    }

    /**
     * Writes the log entry via the Symfony OutputInterface object configured for the handler
     *
     * @param  array $record
     *
     * @return void
     */
    protected function write(array $record)
    {
        $this->output->write((string) $record['formatted']);
    }
}