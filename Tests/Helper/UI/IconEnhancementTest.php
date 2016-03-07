<?php
use Afrihost\BaseCommandBundle\Tests\Fixtures\IconCommand;

/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2016/03/03
 * Time: 7:07 AM
 */
class IconEnhancementTest extends AbstractContainerTest
{
    /**
     * @dataProvider dataProvider
     * @param $input
     * @param $expected
     */
    public function testIconOutput($input, $expected)
    {
        $command = $this->registerCommand(new IconCommand());
        $tester = $this->executeCommand($command, $input);

        $output = $tester->getDisplay();

        $decoded = unpack('H*', $output);
        $checksum = array_shift($decoded);

        $this->assertEquals($expected, $checksum);
    }

    public function dataProvider()
    {
        return array(
            array(array('--icon' => 'tick'), 'e29c94'),
            array(array('--icon' => 'check'), 'e29c94'),
            array(array('--icon' => 'error'), 'e29c98'),
            array(array('--icon' => 'crossMark'), 'e29c98'),
            array(array('--icon' => 'exclamation'), 'e29d97'),
            array(array('--icon' => 'lt'), 'e29db0'),
            array(array('--icon' => 'gt'), 'e29db1'),
            array(array('--icon' => 'one'), 'e29e80'),
            array(array('--icon' => 'two'), 'e29e81'),
            array(array('--icon' => 'three'), 'e29e82'),
            array(array('--icon' => 'four'), 'e29e83'),
            array(array('--icon' => 'five'), 'e29e84'),
            array(array('--icon' => 'six'), 'e29e85'),
            array(array('--icon' => 'seven'), 'e29e86'),
            array(array('--icon' => 'eight'), 'e29e87'),
            array(array('--icon' => 'nine'), 'e29e88'),
            array(array('--icon' => 'ten'), 'e29e89'),
            array(array('--icon' => 'envelope'), 'e29c89'),
            array(array('--icon' => 'skullAndCrossBones'), 'e298a0'),
            array(array('--icon' => 'dead'), 'e298a0'),
            array(array('--icon' => 'noEntry'), 'e29b94'),
            array(array('--icon' => 'alarmClock'), 'e28fb0'),
            array(array('--icon' => 'leftArrow'), 'e28690'),
            array(array('--icon' => 'upArrow'), 'e28691'),
            array(array('--icon' => 'rightArrow'), 'e28692'),
            array(array('--icon' => 'downArrow'), 'e28693'),
            array(array('--icon' => 'leftRightArrow'), 'e28694'),
            array(array('--icon' => 'upDownArrow'), 'e28695'),
            array(array('--icon' => 'smileyPoo'), 'f09f92a9'),
            array(array('--icon' => 'beers'), 'f09f8dbb'),
            array(array('--icon' => 'chicken'), 'f09f9094'),
            array(array('--icon' => 'bomb'), 'f09f92a3'),
            array(array('--icon' => 'snooze'), 'f09f92a4'),
            array(array('--icon' => 'lock'), 'f09f9492'),
        );
    }

}