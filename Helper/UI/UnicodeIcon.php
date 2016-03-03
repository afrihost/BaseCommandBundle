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
    const UNICODE_ICON_LEFT_ARROW = "\u2190";
    const UNICODE_ICON_UP_ARROW = "\u2191";
    const UNICODE_ICON_RIGHT_ARROW = "\u2192";
    const UNICODE_ICON_DOWN_ARROW = "\u2193";
    const UNICODE_ICON_LEFT_RIGHT_ARROW = "\u2194";
    const UNICODE_ICON_UP_DOWN_ARROW = "\u2195";
    const UNICODE_ICON_ALARM_CLOCK = "\u23F0";
    const UNICODE_ICON_NO_ENTRY = "\u26D4";
    const UNICODE_ICON_SKULL_AND_CROSSBONES = "\u2620";
    const UNICODE_ICON_UPPER_BLADE_SCISSORS = "\u2701";
    const UNICODE_ICON_BLACK_SCISSORS = "\u2702";
    const UNICODE_ICON_LOWER_BLADE_SCISSORS = "\u2703";
    const UNICODE_ICON_WHITE_SCISSORS = "\u2704";
    const UNICODE_ICON_WHITE_HEAVY_CHECK_MARK = "\u2705";
    const UNICODE_ICON_TELEPHONE_LOCATION_SIGN = "\u2706";
    const UNICODE_ICON_TAPE_DRIVE = "\u2707";
    const UNICODE_ICON_AIRPLANE = "\u2708";
    const UNICODE_ICON_ENVELOPE = "\u2709";
    const UNICODE_ICON_RAISED_FIST = "\u270A";
    const UNICODE_ICON_RAISED_HAND = "\u270B";
    const UNICODE_ICON_VICTORY_HAND = "\u270C";
    const UNICODE_ICON_WRITING_HAND = "\u270D";
    const UNICODE_ICON_LOWER_RIGHT_PENCIL = "\u270E";
    const UNICODE_ICON_PENCIL = "\u270F";
    const UNICODE_ICON_UPPER_RIGHT_PENCIL = "\u2710";
    const UNICODE_ICON_WHITE_NIB = "\u2711";
    const UNICODE_ICON_BLACK_NIB = "\u2712";
    const UNICODE_ICON_CHECK_MARK = "\u2713";
    const UNICODE_ICON_HEAVY_CHECK_MARK = "\u2714";
    const UNICODE_ICON_MULTIPLICATION_X = "\u2715";
    const UNICODE_ICON_HEAVY_MULTIPLICATION_X = "\u2716";
    const UNICODE_ICON_BALLOT_X = "\u2717";
    const UNICODE_ICON_HEAVY_BALLOT_X = "\u2718";
    const UNICODE_ICON_OUTLINED_GREEK_CROSS = "\u2719";
    const UNICODE_ICON_HEAVY_GREEK_CROSS = "\u271A";
    const UNICODE_ICON_OPEN_CENTRE_CROSS = "\u271B";
    const UNICODE_ICON_HEAVY_OPEN_CENTRE_CROSS = "\u271C";
    const UNICODE_ICON_LATIN_CROSS = "\u271D";
    const UNICODE_ICON_SHADOWED_WHITE_LATIN_CROSS = "\u271E";
    const UNICODE_ICON_OUTLINED_LATIN_CROSS = "\u271F";
    const UNICODE_ICON_MALTESE_CROSS = "\u2720";
    const UNICODE_ICON_STAR_OF_DAVID = "\u2721";
    const UNICODE_ICON_FOUR_TEARDROP_SPOKED_ASTERISK = "\u2722";
    const UNICODE_ICON_FOUR_BALLOON_SPOKED_ASTERISK = "\u2723";
    const UNICODE_ICON_HEAVY_FOUR_BALLOON_SPOKED_ASTERISK = "\u2724";
    const UNICODE_ICON_FOUR_CLUB_SPOKED_ASTERISK = "\u2725";
    const UNICODE_ICON_BLACK_FOUR_POINTED_STAR = "\u2726";
    const UNICODE_ICON_WHITE_FOUR_POINTED_STAR = "\u2727";
    const UNICODE_ICON_SPARKLES = "\u2728";
    const UNICODE_ICON_STRESS_OUTLINED_WHITE_STAR = "\u2729";
    const UNICODE_ICON_CIRCLED_WHITE_STAR = "\u272A";
    const UNICODE_ICON_OPEN_CENTRE_BLACK_STAR = "\u272B";
    const UNICODE_ICON_BLACK_CENTRE_WHITE_STAR = "\u272C";
    const UNICODE_ICON_OUTLINED_BLACK_STAR = "\u272D";
    const UNICODE_ICON_HEAVY_OUTLINED_BLACK_STAR = "\u272E";
    const UNICODE_ICON_PINWHEEL_STAR = "\u272F";
    const UNICODE_ICON_SHADOWED_WHITE_STAR = "\u2730";
    const UNICODE_ICON_HEAVY_ASTERISK = "\u2731";
    const UNICODE_ICON_OPEN_CENTRE_ASTERISK = "\u2732";
    const UNICODE_ICON_EIGHT_SPOKED_ASTERISK = "\u2733";
    const UNICODE_ICON_EIGHT_POINTED_BLACK_STAR = "\u2734";
    const UNICODE_ICON_EIGHT_POINTED_PINWHEEL_STAR = "\u2735";
    const UNICODE_ICON_SIX_POINTED_BLACK_STAR = "\u2736";
    const UNICODE_ICON_EIGHT_POINTED_RECTILINEAR_BLACK_STAR = "\u2737";
    const UNICODE_ICON_HEAVY_EIGHT_POINTED_RECTILINEAR_BLACK_STAR = "\u2738";
    const UNICODE_ICON_TWELVE_POINTED_BLACK_STAR = "\u2739";
    const UNICODE_ICON_SIXTEEN_POINTED_ASTERISK = "\u273A";
    const UNICODE_ICON_TEARDROP_SPOKED_ASTERISK = "\u273B";
    const UNICODE_ICON_OPEN_CENTRE_TEARDROP_SPOKED_ASTERISK = "\u273C";
    const UNICODE_ICON_HEAVY_TEARDROP_SPOKED_ASTERISK = "\u273D";
    const UNICODE_ICON_SIX_PETALLED_BLACK_AND_WHITE_FLORETTE = "\u273E";
    const UNICODE_ICON_BLACK_FLORETTE = "\u273F";
    const UNICODE_ICON_WHITE_FLORETTE = "\u2740";
    const UNICODE_ICON_EIGHT_PETALLED_OUTLINED_BLACK_FLORETTE = "\u2741";
    const UNICODE_ICON_CIRCLED_OPEN_CENTRE_EIGHT_POINTED_STAR = "\u2742";
    const UNICODE_ICON_HEAVY_TEARDROP_SPOKED_PINWHEEL_ASTERISK = "\u2743";
    const UNICODE_ICON_SNOWFLAKE = "\u2744";
    const UNICODE_ICON_TIGHT_TRIFOLIATE_SNOWFLAKE = "\u2745";
    const UNICODE_ICON_HEAVY_CHEVRON_SNOWFLAKE = "\u2746";
    const UNICODE_ICON_SPARKLE = "\u2747";
    const UNICODE_ICON_HEAVY_SPARKLE = "\u2748";
    const UNICODE_ICON_BALLOON_SPOKED_ASTERISK = "\u2749";
    const UNICODE_ICON_EIGHT_TEARDROP_SPOKED_PROPELLER_ASTERISK = "\u274A";
    const UNICODE_ICON_HEAVY_EIGHT_TEARDROP_SPOKED_PROPELLER_ASTERISK = "\u274B";
    const UNICODE_ICON_CROSS_MARK = "\u274C";
    const UNICODE_ICON_SHADOWED_WHITE_CIRCLE = "\u274D";
    const UNICODE_ICON_NEGATIVE_SQUARED_CROSS_MARK = "\u274E";
    const UNICODE_ICON_LOWER_RIGHT_DROP_SHADOWED_WHITE_SQUARE = "\u274F";
    const UNICODE_ICON_UPPER_RIGHT_DROP_SHADOWED_WHITE_SQUARE = "\u2750";
    const UNICODE_ICON_LOWER_RIGHT_SHADOWED_WHITE_SQUARE = "\u2751";
    const UNICODE_ICON_UPPER_RIGHT_SHADOWED_WHITE_SQUARE = "\u2752";
    const UNICODE_ICON_BLACK_QUESTION_MARK_ORNAMENT = "\u2753";
    const UNICODE_ICON_WHITE_QUESTION_MARK_ORNAMENT = "\u2754";
    const UNICODE_ICON_WHITE_EXCLAMATION_MARK_ORNAMENT = "\u2755";
    const UNICODE_ICON_BLACK_DIAMOND_MINUS_WHITE_X = "\u2756";
    const UNICODE_ICON_HEAVY_EXCLAMATION_MARK_SYMBOL = "\u2757";
    const UNICODE_ICON_LIGHT_VERTICAL_BAR = "\u2758";
    const UNICODE_ICON_MEDIUM_VERTICAL_BAR = "\u2759";
    const UNICODE_ICON_HEAVY_VERTICAL_BAR = "\u275A";
    const UNICODE_ICON_HEAVY_SINGLE_TURNED_COMMA_QUOTATION_MARK_ORNAMENT = "\u275B";
    const UNICODE_ICON_HEAVY_SINGLE_COMMA_QUOTATION_MARK_ORNAMENT = "\u275C";
    const UNICODE_ICON_HEAVY_DOUBLE_TURNED_COMMA_QUOTATION_MARK_ORNAMENT = "\u275D";
    const UNICODE_ICON_HEAVY_DOUBLE_COMMA_QUOTATION_MARK_ORNAMENT = "\u275E";
    const UNICODE_ICON_HEAVY_LOW_SINGLE_COMMA_QUOTATION_MARK_ORNAMENT = "\u275F";
    const UNICODE_ICON_HEAVY_LOW_DOUBLE_COMMA_QUOTATION_MARK_ORNAMENT = "\u2760";
    const UNICODE_ICON_CURVED_STEM_PARAGRAPH_SIGN_ORNAMENT = "\u2761";
    const UNICODE_ICON_HEAVY_EXCLAMATION_MARK_ORNAMENT = "\u2762";
    const UNICODE_ICON_HEAVY_HEART_EXCLAMATION_MARK_ORNAMENT = "\u2763";
    const UNICODE_ICON_HEAVY_BLACK_HEART = "\u2764";
    const UNICODE_ICON_ROTATED_HEAVY_BLACK_HEART_BULLET = "\u2765";
    const UNICODE_ICON_FLORAL_HEART = "\u2766";
    const UNICODE_ICON_ROTATED_FLORAL_HEART_BULLET = "\u2767";
    const UNICODE_ICON_MEDIUM_LEFT_PARENTHESIS_ORNAMENT = "\u2768";
    const UNICODE_ICON_MEDIUM_RIGHT_PARENTHESIS_ORNAMENT = "\u2769";
    const UNICODE_ICON_MEDIUM_FLATTENED_LEFT_PARENTHESIS_ORNAMENT = "\u276A";
    const UNICODE_ICON_MEDIUM_FLATTENED_RIGHT_PARENTHESIS_ORNAMENT = "\u276B";
    const UNICODE_ICON_MEDIUM_LEFT_POINTING_ANGLE_BRACKET_ORNAMENT = "\u276C";
    const UNICODE_ICON_MEDIUM_RIGHT_POINTING_ANGLE_BRACKET_ORNAMENT = "\u276D";
    const UNICODE_ICON_HEAVY_LEFT_POINTING_ANGLE_QUOTATION_MARK_ORNAMENT = "\u276E";
    const UNICODE_ICON_HEAVY_RIGHT_POINTING_ANGLE_QUOTATION_MARK_ORNAMENT = "\u276F";
    const UNICODE_ICON_HEAVY_LEFT_POINTING_ANGLE_BRACKET_ORNAMENT = "\u2770";
    const UNICODE_ICON_HEAVY_RIGHT_POINTING_ANGLE_BRACKET_ORNAMENT = "\u2771";
    const UNICODE_ICON_LIGHT_LEFT_TORTOISE_SHELL_BRACKET_ORNAMENT = "\u2772";
    const UNICODE_ICON_LIGHT_RIGHT_TORTOISE_SHELL_BRACKET_ORNAMENT = "\u2773";
    const UNICODE_ICON_MEDIUM_LEFT_CURLY_BRACKET_ORNAMENT = "\u2774";
    const UNICODE_ICON_MEDIUM_RIGHT_CURLY_BRACKET_ORNAMENT = "\u2775";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_DIGIT_ONE = "\u2776";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_DIGIT_TWO = "\u2777";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_DIGIT_THREE = "\u2778";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_DIGIT_FOUR = "\u2779";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_DIGIT_FIVE = "\u277A";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_DIGIT_SIX = "\u277B";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_DIGIT_SEVEN = "\u277C";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_DIGIT_EIGHT = "\u277D";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_DIGIT_NINE = "\u277E";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_NUMBER_TEN = "\u277F";
    const UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_ONE = "\u2780";
    const UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_TWO = "\u2781";
    const UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_THREE = "\u2782";
    const UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_FOUR = "\u2783";
    const UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_FIVE = "\u2784";
    const UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_SIX = "\u2785";
    const UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_SEVEN = "\u2786";
    const UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_EIGHT = "\u2787";
    const UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_NINE = "\u2788";
    const UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_NUMBER_TEN = "\u2789";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_SANS_SERIF_DIGIT_ONE = "\u278A";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_SANS_SERIF_DIGIT_TWO = "\u278B";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_SANS_SERIF_DIGIT_THREE = "\u278C";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_SANS_SERIF_DIGIT_FOUR = "\u278D";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_SANS_SERIF_DIGIT_FIVE = "\u278E";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_SANS_SERIF_DIGIT_SIX = "\u278F";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_SANS_SERIF_DIGIT_SEVEN = "\u2790";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_SANS_SERIF_DIGIT_EIGHT = "\u2791";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_SANS_SERIF_DIGIT_NINE = "\u2792";
    const UNICODE_ICON_DINGBAT_NEGATIVE_CIRCLED_SANS_SERIF_NUMBER_TEN = "\u2793";
    const UNICODE_ICON_HEAVY_WIDE_HEADED_RIGHTWARDS_ARROW = "\u2794";
    const UNICODE_ICON_HEAVY_PLUS_SIGN = "\u2795";
    const UNICODE_ICON_HEAVY_MINUS_SIGN = "\u2796";
    const UNICODE_ICON_HEAVY_DIVISION_SIGN = "\u2797";
    const UNICODE_ICON_HEAVY_SOUTH_EAST_ARROW = "\u2798";
    const UNICODE_ICON_HEAVY_RIGHTWARDS_ARROW = "\u2799";
    const UNICODE_ICON_HEAVY_NORTH_EAST_ARROW = "\u279A";
    const UNICODE_ICON_DRAFTING_POINT_RIGHTWARDS_ARROW = "\u279B";
    const UNICODE_ICON_HEAVY_ROUND_TIPPED_RIGHTWARDS_ARROW = "\u279C";
    const UNICODE_ICON_TRIANGLE_HEADED_RIGHTWARDS_ARROW = "\u279D";
    const UNICODE_ICON_HEAVY_TRIANGLE_HEADED_RIGHTWARDS_ARROW = "\u279E";
    const UNICODE_ICON_DASHED_TRIANGLE_HEADED_RIGHTWARDS_ARROW = "\u279F";
    const UNICODE_ICON_HEAVY_DASHED_TRIANGLE_HEADED_RIGHTWARDS_ARROW = "\u27A0";
    const UNICODE_ICON_BLACK_RIGHTWARDS_ARROW = "\u27A1";
    const UNICODE_ICON_THREE_D_TOP_LIGHTED_RIGHTWARDS_ARROWHEAD = "\u27A2";
    const UNICODE_ICON_THREE_D_BOTTOM_LIGHTED_RIGHTWARDS_ARROWHEAD = "\u27A3";
    const UNICODE_ICON_BLACK_RIGHTWARDS_ARROWHEAD = "\u27A4";
    const UNICODE_ICON_HEAVY_BLACK_CURVED_DOWNWARDS_AND_RIGHTWARDS_ARROW = "\u27A5";
    const UNICODE_ICON_HEAVY_BLACK_CURVED_UPWARDS_AND_RIGHTWARDS_ARROW = "\u27A6";
    const UNICODE_ICON_SQUAT_BLACK_RIGHTWARDS_ARROW = "\u27A7";
    const UNICODE_ICON_HEAVY_CONCAVE_POINTED_BLACK_RIGHTWARDS_ARROW = "\u27A8";
    const UNICODE_ICON_RIGHT_SHADED_WHITE_RIGHTWARDS_ARROW = "\u27A9";
    const UNICODE_ICON_LEFT_SHADED_WHITE_RIGHTWARDS_ARROW = "\u27AA";
    const UNICODE_ICON_BACK_TILTED_SHADOWED_WHITE_RIGHTWARDS_ARROW = "\u27AB";
    const UNICODE_ICON_FRONT_TILTED_SHADOWED_WHITE_RIGHTWARDS_ARROW = "\u27AC";
    const UNICODE_ICON_HEAVY_LOWER_RIGHT_SHADOWED_WHITE_RIGHTWARDS_ARROW = "\u27AD";
    const UNICODE_ICON_HEAVY_UPPER_RIGHT_SHADOWED_WHITE_RIGHTWARDS_ARROW = "\u27AE";
    const UNICODE_ICON_NOTCHED_LOWER_RIGHT_SHADOWED_WHITE_RIGHTWARDS_ARROW = "\u27AF";
    const UNICODE_ICON_CURLY_LOOP = "\u27B0";
    const UNICODE_ICON_NOTCHED_UPPER_RIGHT_SHADOWED_WHITE_RIGHTWARDS_ARROW = "\u27B1";
    const UNICODE_ICON_CIRCLED_HEAVY_WHITE_RIGHTWARDS_ARROW = "\u27B2";
    const UNICODE_ICON_WHITE_FEATHERED_RIGHTWARDS_ARROW = "\u27B3";
    const UNICODE_ICON_BLACK_FEATHERED_SOUTH_EAST_ARROW = "\u27B4";
    const UNICODE_ICON_BLACK_FEATHERED_RIGHTWARDS_ARROW = "\u27B5";
    const UNICODE_ICON_BLACK_FEATHERED_NORTH_EAST_ARROW = "\u27B6";
    const UNICODE_ICON_HEAVY_BLACK_FEATHERED_SOUTH_EAST_ARROW = "\u27B7";
    const UNICODE_ICON_HEAVY_BLACK_FEATHERED_RIGHTWARDS_ARROW = "\u27B8";
    const UNICODE_ICON_HEAVY_BLACK_FEATHERED_NORTH_EAST_ARROW = "\u27B9";
    const UNICODE_ICON_TEARDROP_BARBED_RIGHTWARDS_ARROW = "\u27BA";
    const UNICODE_ICON_HEAVY_TEARDROP_SHANKED_RIGHTWARDS_ARROW = "\u27BB";
    const UNICODE_ICON_WEDGE_TAILED_RIGHTWARDS_ARROW = "\u27BC";
    const UNICODE_ICON_HEAVY_WEDGE_TAILED_RIGHTWARDS_ARROW = "\u27BD";
    const UNICODE_ICON_OPEN_OUTLINED_RIGHTWARDS_ARROW = "\u27BE";
    const UNICODE_ICON_DOUBLE_CURLY_LOOP = "\u27BF";
    const UNICODE_ICON_SMILEY_POO = "\uD83D\uDCA9";
    const UNICODE_ICON_BEERS = "\uD83C\uDF7B";
    const UNICODE_ICON_CHICKEN = "\uD83D\uDC14";
    const UNICODE_ICON_BOMB = "\uD83D\uDCA3";
    const UNICODE_ICON_ZZZ = "\uD83D\uDCA4";
    const UNICODE_ICON_LOCK = "\uD83D\uDD12";

    const UNICODE_DECODE_JSON = 'json';
    const UNICODE_DECODE_HTML = 'html';

    private $multiCharacterIcons = array(
        self::UNICODE_ICON_SMILEY_POO,
        self::UNICODE_ICON_BEERS,
        self::UNICODE_ICON_CHICKEN,
        self::UNICODE_ICON_BOMB,
        self::UNICODE_ICON_ZZZ,
        self::UNICODE_ICON_LOCK,
    );

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
     * @return array
     */
    public function getMultiCharacterIcons()
    {
        return $this->multiCharacterIcons;
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

        if($this->getRuntimeConfig()->getUnicodeDecodingMethod() == self::UNICODE_DECODE_JSON) {
            $icon = json_decode(sprintf('"%s"', $v));

            if (empty($icon)) {
                return '';
            }
        }

        if($this->getRuntimeConfig()->getUnicodeDecodingMethod() == self::UNICODE_DECODE_HTML) {
            if(!$this->getRuntimeConfig()->hasUnicodeMultiCharacterSupport() && in_array($v, $this->multiCharacterIcons)){
                return '';
            }

            $string = str_replace('\\u', 'U+', UnicodeIcon::UNICODE_ICON_HEAVY_CHECK_MARK);
            $icon = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $string), ENT_NOQUOTES, 'UTF-8');

            if (empty($icon)) {
                return '';
            }
        }

        return $icon;
    }

    /**
     * HEAVY CHECK MARK
     * @return string
     */
    public function tick()
    {
        return $this->icon(self::UNICODE_ICON_HEAVY_CHECK_MARK);
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
        return $this->icon(self::UNICODE_ICON_HEAVY_BALLOT_X);
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
        return $this->icon(self::UNICODE_ICON_HEAVY_EXCLAMATION_MARK_SYMBOL);
    }

    /**
     * HEAVY LEFT-POINTING ANGLE QUOTATION MARK ORNAMENT
     * @return string
     */
    public function lt()
    {
        return $this->icon(self::UNICODE_ICON_HEAVY_LEFT_POINTING_ANGLE_BRACKET_ORNAMENT);
    }

    /**
     * HEAVY RIGHT-POINTING ANGLE QUOTATION MARK ORNAMENT
     * @return string
     */
    public function gt()
    {
        return $this->icon(self::UNICODE_ICON_HEAVY_RIGHT_POINTING_ANGLE_BRACKET_ORNAMENT);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT ONE
     * @return string
     */
    public function one()
    {
        return $this->icon(self::UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_ONE);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT TWO
     * @return string
     */
    public function two()
    {
        return $this->icon(self::UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_TWO);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT THREE
     * @return string
     */
    public function three()
    {
        return $this->icon(self::UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_THREE);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT FOUR
     * @return string
     */
    public function four()
    {
        return $this->icon(self::UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_FOUR);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT FIVE
     * @return string
     */
    public function five()
    {
        return $this->icon(self::UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_FIVE);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT SIX
     * @return string
     */
    public function six()
    {
        return $this->icon(self::UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_SIX);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT SEVEN
     * @return string
     */
    public function seven()
    {
        return $this->icon(self::UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_SEVEN);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT EIGHT
     * @return string
     */
    public function eight()
    {
        return $this->icon(self::UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_EIGHT);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT NINE
     * @return string
     */
    public function nine()
    {
        return $this->icon(self::UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_DIGIT_NINE);
    }

    /**
     * DINGBAT CIRCLED SANS-SERIF DIGIT TEN
     * @return string
     */
    public function ten()
    {
        return $this->icon(self::UNICODE_ICON_DINGBAT_CIRCLED_SANS_SERIF_NUMBER_TEN);
    }

    /**
     * ENVELOPE
     * @return string
     */
    public function envelope()
    {
        return $this->icon(self::UNICODE_ICON_ENVELOPE);
    }

    /**
     * SKULL AND CROSSBONES
     * @return string
     */
    public function skullAndCrossBones()
    {
        return $this->icon(self::UNICODE_ICON_SKULL_AND_CROSSBONES);
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
        return $this->icon(self::UNICODE_ICON_NO_ENTRY);
    }

    /**
     * ALARM CLOCK
     * @return string
     */
    public function alarmClock()
    {
        return $this->icon(self::UNICODE_ICON_ALARM_CLOCK);
    }

    /**
     * LEFT ARROW
     * @return string
     */
    public function leftArrow()
    {
        return $this->icon(self::UNICODE_ICON_LEFT_ARROW);
    }

    /**
     * UP ARROW
     * @return string
     */
    public function upArrow()
    {
        return $this->icon(self::UNICODE_ICON_UP_ARROW);
    }

    /**
     * RIGHT ARROW
     * @return string
     */
    public function rightArrow()
    {
        return $this->icon(self::UNICODE_ICON_RIGHT_ARROW);
    }

    /**
     * DOWN ARROW
     * @return string
     */
    public function downArrow()
    {
        return $this->icon(self::UNICODE_ICON_DOWN_ARROW);
    }

    /**
     * LEFT RIGHT ARROW
     * @return string
     */
    public function leftRightArrow()
    {
        return $this->icon(self::UNICODE_ICON_LEFT_RIGHT_ARROW);
    }

    /**
     * UP DOWN ARROW
     * @return string
     */
    public function upDownArrow()
    {
        return $this->icon(self::UNICODE_ICON_UP_DOWN_ARROW);
    }

    /**
     * SMILEY POO
     * @return string
     */
    public function smileyPoo()
    {
        return $this->icon(self::UNICODE_ICON_SMILEY_POO);
    }

    /**
     * BEERS
     * @return string
     */
    public function beers()
    {
        return $this->icon(self::UNICODE_ICON_BEERS);
    }

    /**
     * CHICKEN
     * @return string
     */
    public function chicken()
    {
        return $this->icon(self::UNICODE_ICON_CHICKEN);
    }

    /**
     * BOMB
     * @return string
     */
    public function bomb()
    {
        return $this->icon(self::UNICODE_ICON_BOMB);
    }

    /**
     * SNOOZE
     * @return string
     */
    public function snooze()
    {
        return $this->icon(self::UNICODE_ICON_ZZZ);
    }

    /**
     * LOCK
     * @return string
     */
    public function lock()
    {
        return $this->icon(self::UNICODE_ICON_LOCK);
    }
}