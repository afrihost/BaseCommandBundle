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
            array(array('--icon' => 'tick'), 'e29c940a'),
            array(array('--icon' => 'check'), 'e29c940a'),
            array(array('--icon' => 'error'), 'e29c980a'),
            array(array('--icon' => 'crossMark'), 'e29c980a'),
            array(array('--icon' => 'exclamation'), 'e29d970a'),
            array(array('--icon' => 'lt'), 'e29db00a'),
            array(array('--icon' => 'gt'), 'e29db10a'),
            array(array('--icon' => 'one'), 'e29e800a'),
            array(array('--icon' => 'two'), 'e29e810a'),
            array(array('--icon' => 'three'), 'e29e820a'),
            array(array('--icon' => 'four'), 'e29e830a'),
            array(array('--icon' => 'five'), 'e29e840a'),
            array(array('--icon' => 'six'), 'e29e850a'),
            array(array('--icon' => 'seven'), 'e29e860a'),
            array(array('--icon' => 'eight'), 'e29e870a'),
            array(array('--icon' => 'nine'), 'e29e880a'),
            array(array('--icon' => 'ten'), 'e29e890a'),
            array(array('--icon' => 'envelope'), 'e29c890a'),
            array(array('--icon' => 'skullAndCrossBones'), 'e298a00a'),
            array(array('--icon' => 'dead'), 'e298a00a'),
            array(array('--icon' => 'noEntry'), 'e29b940a'),
            array(array('--icon' => 'alarmClock'), 'e28fb00a'),
            array(array('--icon' => 'leftArrow'), 'e286900a'),
            array(array('--icon' => 'upArrow'), 'e286910a'),
            array(array('--icon' => 'rightArrow'), 'e286920a'),
            array(array('--icon' => 'downArrow'), 'e286930a'),
            array(array('--icon' => 'leftRightArrow'), 'e286940a'),
            array(array('--icon' => 'upDownArrow'), 'e286950a'),
            array(array('--icon' => 'smileyPoo'), 'f09f92a90a'),
            array(array('--icon' => 'beers'), 'f09f8dbb0a'),
            array(array('--icon' => 'chicken'), 'f09f90940a'),
            array(array('--icon' => 'bomb'), 'f09f92a30a'),
            array(array('--icon' => 'snooze'), 'f09f92a40a'),
            array(array('--icon' => 'lock'), 'f09f94920a'),
            array(array('--icon' => 'pray'), 'f09f998f0a'),
        );
    }

}