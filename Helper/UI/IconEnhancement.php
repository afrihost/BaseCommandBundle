<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2016/03/02
 * Time: 9:18 PM
 */

namespace Afrihost\BaseCommandBundle\Helper\UI;


use Afrihost\BaseCommandBundle\Command\BaseCommand;
use Afrihost\BaseCommandBundle\Helper\AbstractEnhancement;
use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IconEnhancement extends AbstractEnhancement
{

    private $data = array('icon' => null, 'options' => null, 'colour' => null, 'bgColour' => null);

    private $iconHandler;

    /**
     * IconEnhancement constructor.
     * @param BaseCommand $command
     * @param RuntimeConfig $runtimeConfig
     */
    public function __construct(BaseCommand $command, RuntimeConfig $runtimeConfig)
    {
        parent::__construct($command, $runtimeConfig);
        $this->iconHandler = new UnicodeIcon($this->getRuntimeConfig());
    }


    /**
     * Setup of the enhancement should take place here. This function will be called in the BaseCommand's initialize() function
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * Logic that needs to be hooked in before the command's run() function is invoked (i.e. after construction but before
     * initialization) should be placed here.  The function will be called by the BaseCommand's preRun() function
     *
     * @param OutputInterface $output
     */
    public function preRun(OutputInterface $output)
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->getRuntimeConfig()->setUnicodeIconSupport(false);
            return;
        }

        if (function_exists('json_decode')) {
            $this->getRuntimeConfig()->setUnicodeIconSupport(true);
            $this->getRuntimeConfig()->setUnicodeMultiCharacterSupport(true);
            $this->getRuntimeConfig()->setUnicodeDecodingMethod(UnicodeIcon::UNICODE_DECODE_JSON);
            return;
        }

        $this->getRuntimeConfig()->setUnicodeIconSupport(true);
        $this->getRuntimeConfig()->setUnicodeMultiCharacterSupport(false);
        $this->getRuntimeConfig()->setUnicodeDecodingMethod(UnicodeIcon::UNICODE_DECODE_HTML);
        return;


    }

    /**
     * Cleanup logic that is to be executed after the command has been run should be implemented here. This function will
     * be called BaseCommand's postRun() function
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param int|null $exitCode
     */
    public function postRun(InputInterface $input, OutputInterface $output, $exitCode)
    {
        // TODO: Implement postRun() method.
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this->iconHandler, $name)){
            $this->data['icon'] = $this->iconHandler->$name();
        }

        return $this;
    }

    protected function setColour($v){
        if (!$this->getRuntimeConfig()->hasUnicodeIconSupport()) {
            return $this;
        }

        $this->data['colour'] = $v;
        return $this;
    }

    protected function setBackgroundColour($v){
        if (!$this->getRuntimeConfig()->hasUnicodeIconSupport()) {
            return $this;
        }

        $this->data['bgColour'] = $v;
        return $this;
    }

    public function defaultColour()
    {
        return $this->setColour('default');
    }

    public function black()
    {
        return $this->setColour('black');
    }

    public function white()
    {
        return $this->setColour('white');
    }

    public function red()
    {
        return $this->setColour('red');
    }

    public function blue()
    {
        return $this->setColour('blue');
    }

    public function green()
    {
        return $this->setColour('green');
    }

    public function yellow()
    {
        return $this->setColour('yellow');
    }

    public function magenta()
    {
        return $this->setColour('magenta');
    }

    public function cyan()
    {
        return $this->setColour('cyan');
    }

    public function bgDefaultColour()
    {
        return $this->setBackgroundColour('default');
    }

    public function bgBlack()
    {
        return $this->setBackgroundColour('black');
    }

    public function bgWhite()
    {
        return $this->setBackgroundColour('white');
    }

    public function bgRed()
    {
        return $this->setBackgroundColour('red');
    }

    public function bgBlue()
    {
        return $this->setBackgroundColour('blue');
    }

    public function bgGreen()
    {
        return $this->setBackgroundColour('green');
    }

    public function bgYellow()
    {
        return $this->setBackgroundColour('yellow');
    }

    public function bgMagenta()
    {
        return $this->setBackgroundColour('magenta');
    }

    public function bgCyan()
    {
        return $this->setBackgroundColour('cyan');
    }

    protected function addOption($v){
        if (!$this->getRuntimeConfig()->hasUnicodeIconSupport()) {
            return $this;
        }

        $this->data['options'][] = $v;
        return $this;
    }

    /**
     * Add the bold option
     * @return $this
     */
    public function bold()
    {
        return $this->addOption('bold');
    }

    /**
     * Adds the underscore option
     * @return $this
     */
    public function underscore()
    {
        return $this->addOption('underscore');
    }

    /**
     * Add the reverse option
     * @return $this|IconEnhancement
     */
    public function reverse(){
        return $this->addOption('reverse');
    }

    /**
     * @return array
     */
    public function getData(){
        return $this->data;
    }

    /**
     * Resets the settings for the next icon
     */
    protected function flushData(){
        $this->data = array('icon' => null, 'options' => array(), 'colour' => null, 'bgColour' => null);
    }

    /**
     * @return string
     */
    public function render(){
        $icon = (string) $this;

        $this->flushData();

        return $icon;
    }

    public function __toString()
    {
        if (!$this->getRuntimeConfig()->hasUnicodeIconSupport()) {
            return '';
        }

        $colour = null;
        $backgroundColour = null;
        $options = array();

        if (!is_null($this->data['colour'])) {
            $colour = $this->data['colour'];
        }

        if (!is_null($this->data['bgColour'])) {
            $backgroundColour = $this->data['bgColour'];
        }

        if (!empty($this->data['options'])) {
            $options = $this->data['options'];
        }

        $style = new OutputFormatterStyle($colour, $backgroundColour, $options);
        $output = $style->apply(' ' . $this->data['icon'] . ' ');

        return $output;
    }
}