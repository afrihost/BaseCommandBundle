<?php
namespace Afrihost\BaseCommandBundle\Helper\UI;

use Afrihost\BaseCommandBundle\Helper\Config\RuntimeConfig;

/**
 * Class UnicodeIcon
 * @package Afrihost\BaseCommandBundle\Helper\UI
 * @see http://www.fileformat.info/info/unicode/block/dingbats/list.htm
 */
class UnicodeIcon
{
    const HTML_ICON_HEAVY_CHECK_MARK = "&#x2714;";
    const HTML_ICON_HEAVY_BALLOT_X = "&#x2718;";
    const HTML_ICON_HEAVY_EXCLAMATION_MARK_SYMBOL = "&#x2757;";
    const HTML_ICON_HEAVY_LEFT_POINTING_ANGLE_BRACKET_ORNAMENT = "&#x2770;";
    const HTML_ICON_HEAVY_RIGHT_POINTING_ANGLE_BRACKET_ORNAMENT = "&#x2771;";
    const HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_ONE = "&#x2780;";
    const HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_TWO = "&#x2781;";
    const HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_THREE = "&#x2782;";
    const HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_FOUR = "&#x2783;";
    const HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_FIVE = "&#x2784;";
    const HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_SIX = "&#x2785;";
    const HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_SEVEN = "&#x2786;";
    const HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_EIGHT = "&#x2787;";
    const HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_NINE = "&#x2788;";
    const HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_NUMBER_TEN = "&#x2789;";
    const HTML_ICON_ENVELOPE = "&#x2709;";
    const HTML_ICON_SKULL_AND_CROSSBONES = "&#x2620;";
    const HTML_ICON_NO_ENTRY = "&#x26D4;";
    const HTML_ICON_ALARM_CLOCK = "&#x23F0;";
    const HTML_ICON_LEFT_ARROW = "&#x2190;";
    const HTML_ICON_UP_ARROW = "&#x2191;";
    const HTML_ICON_RIGHT_ARROW = "&#x2192;";
    const HTML_ICON_DOWN_ARROW = "&#x2193;";
    const HTML_ICON_LEFT_RIGHT_ARROW = "&#x2194;";
    const HTML_ICON_UP_DOWN_ARROW = "&#x2195;";
    const HTML_ICON_SMILEY_POO = "&#x1f4a9;";
    const HTML_ICON_BEERS = "&#x1f37b;";
    const HTML_ICON_CHICKEN = "&#x1f414;";
    const HTML_ICON_BOMB = "&#x1f4a3;";
    const HTML_ICON_ZZZ = "	&#x1f4a4;";
    const HTML_ICON_LOCK = "&#x1f512;";
    const HTML_ICON_PRAY = "&#x1f64f;";

    /**
     * @var RuntimeConfig
     */
    private $runtimeConfig;

    /**
     * UnicodeIcon constructor.
     * @param RuntimeConfig $runtimeConfig
     */
    public function __construct(RuntimeConfig $runtimeConfig)
    {
        $this->runtimeConfig = $runtimeConfig;
    }

    /**
     * @return RuntimeConfig
     */
    public function getRuntimeConfig()
    {
        return $this->runtimeConfig;
    }


    public function icon($v)
    {
        $icon = '';

        if (!$this->getRuntimeConfig()->hasUnicodeIconSupport()) {
            return $icon;
        }


        $icon = trim(html_entity_decode($v, ENT_NOQUOTES, 'UTF-8'));

        if (empty($icon)) {
            return '';
        }

        return $icon;
    }

    /**
     * HEAVY CHECK MARK
     * @return string
     */
    public function tick()
    {
        return $this->icon(self::HTML_ICON_HEAVY_CHECK_MARK);
    }

    /**
     * Alias for tick()
     * @see tick
     * @return string
     */
    public function check()
    {
        return self::tick();
    }

    /**
     * HEAVY BALLOT X
     * @return string
     */
    public function error()
    {
        return $this->icon(self::HTML_ICON_HEAVY_BALLOT_X);
    }

    /**
     * Alias for error()
     * @see error
     * @return string
     */
    public function crossMark()
    {
        return self::error();
    }

    /**
     * HEAVY EXCLAMATION MARK SYMBOL
     * @return string
     */
    public function exclamation()
    {
        return $this->icon(self::HTML_ICON_HEAVY_EXCLAMATION_MARK_SYMBOL);
    }

    /**
     * HEAVY LEFT-POINTING ANGLE QUOTATION MARK ORNAMENT
     * @return string
     */
    public function lt()
    {
        return $this->icon(self::HTML_ICON_HEAVY_LEFT_POINTING_ANGLE_BRACKET_ORNAMENT);
    }

    /**
     * HEAVY RIGHT-POINTING ANGLE QUOTATION MARK ORNAMENT
     * @return string
     */
    public function gt()
    {
        return $this->icon(self::HTML_ICON_HEAVY_RIGHT_POINTING_ANGLE_BRACKET_ORNAMENT);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT ONE
     * @return string
     */
    public function one()
    {
        return $this->icon(self::HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_ONE);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT TWO
     * @return string
     */
    public function two()
    {
        return $this->icon(self::HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_TWO);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT THREE
     * @return string
     */
    public function three()
    {
        return $this->icon(self::HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_THREE);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT FOUR
     * @return string
     */
    public function four()
    {
        return $this->icon(self::HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_FOUR);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT FIVE
     * @return string
     */
    public function five()
    {
        return $this->icon(self::HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_FIVE);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT SIX
     * @return string
     */
    public function six()
    {
        return $this->icon(self::HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_SIX);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT SEVEN
     * @return string
     */
    public function seven()
    {
        return $this->icon(self::HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_SEVEN);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT EIGHT
     * @return string
     */
    public function eight()
    {
        return $this->icon(self::HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_EIGHT);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT NINE
     * @return string
     */
    public function nine()
    {
        return $this->icon(self::HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_NINE);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT TEN
     * @return string
     */
    public function ten()
    {
        return $this->icon(self::HTML_ICON_DINGBAT_CIRCLED_SANS_SERIF_NUMBER_TEN);
    }

    /**
     * ENVELOPE
     * @return string
     */
    public function envelope()
    {
        return $this->icon(self::HTML_ICON_ENVELOPE);
    }

    /**
     * SKULL AND CROSSBONES
     * @return string
     */
    public function skullAndCrossBones()
    {
        return $this->icon(self::HTML_ICON_SKULL_AND_CROSSBONES);
    }

    /**
     * Alias for skullAndCrossBones()
     * @see skullAndCrossBones
     * @return string
     */
    public function dead()
    {
        return self::skullAndCrossBones();
    }

    /**
     * NO ENTRY
     * @return string
     */
    public function noEntry()
    {
        return $this->icon(self::HTML_ICON_NO_ENTRY);
    }

    /**
     * ALARM CLOCK
     * @return string
     */
    public function alarmClock()
    {
        return $this->icon(self::HTML_ICON_ALARM_CLOCK);
    }

    /**
     * LEFT ARROW
     * @return string
     */
    public function leftArrow()
    {
        return $this->icon(self::HTML_ICON_LEFT_ARROW);
    }

    /**
     * UP ARROW
     * @return string
     */
    public function upArrow()
    {
        return $this->icon(self::HTML_ICON_UP_ARROW);
    }

    /**
     * RIGHT ARROW
     * @return string
     */
    public function rightArrow()
    {
        return $this->icon(self::HTML_ICON_RIGHT_ARROW);
    }

    /**
     * DOWN ARROW
     * @return string
     */
    public function downArrow()
    {
        return $this->icon(self::HTML_ICON_DOWN_ARROW);
    }

    /**
     * LEFT RIGHT ARROW
     * @return string
     */
    public function leftRightArrow()
    {
        return $this->icon(self::HTML_ICON_LEFT_RIGHT_ARROW);
    }

    /**
     * UP DOWN ARROW
     * @return string
     */
    public function upDownArrow()
    {
        return $this->icon(self::HTML_ICON_UP_DOWN_ARROW);
    }

    /**
     * SMILEY POO
     * @return string
     */
    public function smileyPoo()
    {
        return $this->icon(self::HTML_ICON_SMILEY_POO);
    }

    /**
     * BEERS
     * @return string
     */
    public function beers()
    {
        return $this->icon(self::HTML_ICON_BEERS);
    }

    /**
     * CHICKEN
     * @return string
     */
    public function chicken()
    {
        return $this->icon(self::HTML_ICON_CHICKEN);
    }

    /**
     * BOMB
     * @return string
     */
    public function bomb()
    {
        return $this->icon(self::HTML_ICON_BOMB);
    }

    /**
     * SNOOZE
     * @return string
     */
    public function snooze()
    {
        return $this->icon(self::HTML_ICON_ZZZ);
    }

    /**
     * LOCK
     * @return string
     */
    public function lock()
    {
        return $this->icon(self::HTML_ICON_LOCK);
    }

    /**
     * LOCK
     * @return string
     */
    public function pray()
    {
        return $this->icon(self::HTML_ICON_PRAY);
    }
}