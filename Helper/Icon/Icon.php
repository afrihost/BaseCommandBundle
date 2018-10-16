<?php

namespace Afrihost\BaseCommandBundle\Helper\Icon;


use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Icon
{
    private $data = array('icon' => null, 'options' => null, 'colour' => null, 'bgColour' => null);

    /**
     * @var UnicodeIcon
     */
    private $iconHandler;

    /**
     * Icon constructor.
     * @param UnicodeIcon $iconHandler
     */
    public function __construct(UnicodeIcon $iconHandler)
    {
        $this->iconHandler = $iconHandler;
    }

    /**
     * @return UnicodeIcon
     */
    protected function getIconHandler()
    {
        return $this->iconHandler;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->getIconHandler(), $name)) {
            $this->data['icon'] = $this->iconHandler->$name();
        }

        return $this;
    }

    protected function setColour($v)
    {
        if (!$this->getIconHandler()->getRuntimeConfig()->hasUnicodeIconSupport()) {
            return $this;
        }

        $this->data['colour'] = $v;
        return $this;
    }

    protected function setBackgroundColour($v)
    {
        if (!$this->getIconHandler()->getRuntimeConfig()->hasUnicodeIconSupport()) {
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

    protected function addOption($v)
    {
        if (!$this->getIconHandler()->getRuntimeConfig()->hasUnicodeIconSupport()) {
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
    public function reverse()
    {
        return $this->addOption('reverse');
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Resets the settings for the next icon
     */
    protected function flushData()
    {
        $this->data = array('icon' => null, 'options' => array(), 'colour' => null, 'bgColour' => null);
    }

    /**
     * @return string
     */
    public function render()
    {
        $icon = (string)$this;

        $this->flushData();

        return $icon;
    }

    public function __toString()
    {
        if (!$this->getIconHandler()->getRuntimeConfig()->hasUnicodeIconSupport()) {
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
        $output = $style->apply($this->data['icon']);

        return $output;
    }
}