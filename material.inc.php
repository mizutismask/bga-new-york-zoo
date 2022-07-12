<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * NewYorkZoo implementation : © Séverine Kamycki severinek@gmail.com
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * NewYorkZoo game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


/*

Example:

$this->card_types = array(
    1 => array( "card_name" => ...,
                ...
              )
);

*/
if (!defined("LIGHTEST_GREEN")) {
  define("LIGHTEST_GREEN","lightest");
  define("LIGHT_GREEN","light");
  define("DARK_GREEN","dark");
  define("DARKEST_GREEN","darkest");
}

$topY = 2.42;
$topYLine2 = 70.56;
$smallWidth = 7.10;
$mediumWidth = 7.75;
$bigWidth = 11.33;
$height = 27;

$this->actionStripZones = [
  'action_zone_1' => [
    'topX' =>  0.52,
    'topY' =>  $topY,
    'width' =>  $bigWidth,
    'height' =>  $height,
  ],
  'action_zone_2' => [
    'topX' =>  11.82,
    'topY' =>  $topY,
    'width' =>  $smallWidth,
    'height' =>  $height,
  ],
  'action_zone_3' => [
    'topX' =>  19,
    'topY' =>  $topY,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_4' => [
    'topX' =>  29.5,
    'topY' =>  $topY,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_5' => [
    'topX' =>  37.33,
    'topY' =>  $topY,
    'width' =>  $smallWidth,
    'height' =>  $height,
  ],
  'action_zone_6' => [
    'topX' =>  44.5,
    'topY' =>  $topY,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_7' => [
    'topX' =>  52.08,
    'topY' =>  $topY,
    'width' =>  $smallWidth,
    'height' =>  $height,
  ],
  'action_zone_8' => [
    'topX' =>   59.25,
    'topY' =>  $topY,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_9' => [
    'topX' =>  69.92,
    'topY' =>  $topY,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_10' => [
    'topX' =>  77.75,
    'topY' =>  $topY,
    'width' =>  $smallWidth,
    'height' =>  $height,
  ],
  'action_zone_11' => [
    'topX' =>  84.75,
    'topY' =>  $topY,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_12' => [
    'topX' =>  92.33,
    'topY' =>  $topY,
    'width' =>  $smallWidth,
    'height' =>  $height,
  ],
  'action_zone_13' => [
    'topX' =>  99.67,
    'topY' =>  $topY,
    'width' =>  $smallWidth,
    'height' =>  100,
  ],
  'action_zone_14' => [
    'topX' =>  84.67,
    'topY' =>  $topYLine2,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_15' => [
    'topX' =>  77.58,
    'topY' =>  $topYLine2,
    'width' =>  $smallWidth,
    'height' =>  $height,
  ],
  'action_zone_16' => [
    'topX' =>  69.92,
    'topY' =>  $topYLine2,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_17' => [
    'topX' =>  62.75,
    'topY' =>  $topYLine2,
    'width' =>  $smallWidth,
    'height' =>  $height,
  ],
  'action_zone_18' => [
    'topX' =>  54.92,
    'topY' =>  $topYLine2,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_19' => [
    'topX' =>  44.33,
    'topY' =>  $topYLine2,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_20' => [
    'topX' =>  37.33,
    'topY' =>  $topYLine2,
    'width' =>  $smallWidth,
    'height' =>  $height,
  ],
  'action_zone_21' => [
    'topX' =>  29.5,
    'topY' =>  $topYLine2,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_22' => [
    'topX' =>  22.33,
    'topY' =>  $topYLine2,
    'width' =>  $smallWidth,
    'height' =>  $height,
  ],
  'action_zone_23' => [
    'topX' =>  14.6,
    'topY' =>  $topYLine2,
    'width' =>  $mediumWidth,
    'height' =>  $height,
  ],
  'action_zone_24' => [
    'topX' =>  0.52,
    'topY' =>  $topYLine2,
    'width' =>  $bigWidth,
    'height' =>  $height,
  ],
];

$this->token_types = [

  'token_neutral' => [
    'name' => clienttranslate("Neutral Token"),
    'w'=>1,'h'=>1,
  ],
  'rotate_control' => [
    'type' => 'rotate-image control-image control-node drop-zone',
    'name' => clienttranslate("Rotate Left"),
  ],
  'flip_control' => [
    'type' => 'mirror-image control-image control-node drop-zone',
    'name' => clienttranslate("Flip"),
  ],
  'done_control' => [
    'type' => 'done-image control-image control-node',
    'name' => clienttranslate("Confirm Placement"),
    'tooltip' => clienttranslate("Click to confirm"),
  ],
  'cancel_control' => [
    'type' => 'cancel-image control-image control-node',
    'name' => clienttranslate("Cancel Placement"),
    'tooltip' => clienttranslate("Click to cancel"),
  ],
  'empties' => [
    'name' => clienttranslate("Empties Counter"),
    'tooltip' => clienttranslate("Counter for remaining empty spaces. You will get 2 point penalty at the end of game per empty space."),
  ],

  'patch_1' => [
    'num' => 1,
    'type' => 'patch patch_1',
    'spaces' => 4,
    'mask' => ':010:111',
    'w' => 3,
    'h' => 2,
    'color' => 'lightest',
  ],
  'patch_2' => [
    'num' => 2,
    'type' => 'patch patch_2',
    'spaces' => 6,
    'mask' => ':111:111',
    'w' => 3,
    'h' => 2,
    'color' => '',
  ],
  'patch_3' => [
    'num' => 3,
    'type' => 'patch patch_3',
    'spaces' => 4,
    'mask' => ':01:11:10',
    'w' => 2,
    'h' => 3,
    'color' => 'lightest',
  ],
  'patch_4' => [
    'num' => 4,
    'type' => 'patch patch_4',
    'spaces' => 8,
    'mask' => ':1111:1111',
    'w' => 4,
    'h' => 2,
    'color' => 'darkest',
  ],
  'patch_5' => [
    'num' => 5,
    'type' => 'patch patch_5',
    'spaces' => 4,
    'mask' => ':01:11:10',
    'w' => 2,
    'h' => 3,
    'color' => 'lightest',
  ],
  'patch_6' => [
    'num' => 6,
    'type' => 'patch patch_6',
    'spaces' => 4,
    'mask' => ':11:11',
    'w' => 2,
    'h' => 2,
    'color' => '',
  ],
  'patch_7' => [
    'num' => 7,
    'type' => 'patch patch_7',
    'spaces' => 4,
    'mask' => ':1111',
    'w' => 4,
    'h' => 1,
    'color' => '',
  ],
  'patch_8' => [
    'num' => 8,
    'type' => 'patch patch_8',
    'spaces' => 3,
    'mask' => ':111',
    'w' => 3,
    'h' => 1,
    'color' => '',
  ],
  'patch_9' => [
    'num' => 9,
    'type' => 'patch patch_9',
    'spaces' => 3,
    'mask' => ':111',
    'w' => 3,
    'h' => 1,
    'color' => '',
  ],
  'patch_10' => [
    'num' => 10,
    'type' => 'patch patch_10',
    'spaces' => 3,
    'mask' => ':111',
    'w' => 3,
    'h' => 1,
    'color' => '',
  ],
  'patch_11' => [
    'num' => 11,
    'type' => 'patch patch_11',
    'spaces' => 3,
    'mask' => ':11:10',
    'w' => 2,
    'h' => 2,
    'color' => '',
  ],
  'patch_12' => [
    'num' => 12,
    'type' => 'patch patch_12',
    'spaces' => 3,
    'mask' => ':11:10',
    'w' => 2,
    'h' => 2,
    'color' => '',
  ],
  'patch_13' => [
    'num' => 13,
    'type' => 'patch patch_13',
    'spaces' => 4,
    'mask' => ':010:111',
    'w' => 3,
    'h' => 2,
    'color' => 'lightest',
  ],
  'patch_14' => [
    'num' => 14,
    'type' => 'patch patch_14',
    'spaces' => 4,
    'mask' => ':11:10:10',
    'w' => 2,
    'h' => 3,
    'color' => 'lightest',
  ],
  'patch_15' => [
    'num' => 15,
    'type' => 'patch patch_15',
    'spaces' => 5,
    'mask' => ':1000:1111',
    'w' => 4,
    'h' => 2,
    'color' => 'light',
  ],
  'patch_16' => [
    'num' => 16,
    'type' => 'patch patch_16',
    'spaces' => 4,
    'mask' => ':11:10:10',
    'w' => 2,
    'h' => 3,
    'color' => 'lightest',
  ],
  'patch_17' => [
    'num' => 17,
    'type' => 'patch patch_17',
    'spaces' => 4,
    'mask' => ':11:11',
    'w' => 2,
    'h' => 2,
    'color' => 'lightest',
  ],
  'patch_18' => [
    'num' => 18,
    'type' => 'patch patch_18',
    'spaces' => 4,
    'mask' => ':1111',
    'w' => 4,
    'h' => 1,
    'color' => 'lightest',
  ],
  'patch_19' => [
    'num' => 19,
    'type' => 'patch patch_19',
    'spaces' => 5,
    'mask' => ':010:111:010',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
  ],
  'patch_20' => [
    'num' => 20,
    'type' => 'patch patch_20',
    'spaces' => 5,
    'mask' => ':01:11:01:01',
    'w' => 2,
    'h' => 4,
    'color' => 'light',
  ],
  'patch_21' => [
    'num' => 21,
    'type' => 'patch patch_21',
    'spaces' => 5,
    'mask' => ':011:110:010',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
  ],
  'patch_22' => [
    'num' => 22,
    'type' => 'patch patch_22',
    'spaces' => 5,
    'mask' => ':111:010:010',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
  ],
  'patch_23' => [
    'num' => 23,
    'type' => 'patch patch_23',
    'spaces' => 5,
    'mask' => ':011:110:100',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
  ],
  'patch_24' => [
    'num' => 24,
    'type' => 'patch patch_24',
    'spaces' => 5,
    'mask' => ':011:110:100',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
  ],
  'patch_25' => [
    'num' => 25,
    'type' => 'patch patch_25',
    'spaces' => 1,
    'mask' => ':1',
    'w' => 1,
    'h' => 1,
    'color' => '',
  ],
  'patch_26' => [
    'num' => 26,
    'type' => 'patch patch_26',
    'spaces' => 5,
    'mask' => ':01:11:01:01',
    'w' => 2,
    'h' => 4,
    'color' => 'light',
  ],
  'patch_27' => [
    'num' => 27,
    'type' => 'patch patch_27',
    'spaces' => 2,
    'mask' => ':11',
    'w' => 2,
    'h' => 1,
    'color' => '',
  ],
  'patch_28' => [
    'num' => 28,
    'type' => 'patch patch_28',
    'spaces' => 2,
    'mask' => ':11',
    'w' => 2,
    'h' => 1,
    'color' => '',
  ],
  'patch_29' => [
    'num' => 29,
    'type' => 'patch patch_29',
    'spaces' => 2,
    'mask' => ':11',
    'w' => 2,
    'h' => 1,
    'color' => '',
  ],
  'patch_30' => [
    'num' => 30,
    'type' => 'patch patch_30',
    'spaces' => 5,
    'mask' => ':011:110:010',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
  ],
  'patch_31' => [
    'num' => 31,
    'type' => 'patch patch_31',
    'spaces' => 5,
    'mask' => ':001:001:111',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
  ],
  'patch_32' => [
    'num' => 32,
    'type' => 'patch patch_32',
    'spaces' => 5,
    'mask' => ':011:111',
    'w' => 3,
    'h' => 2,
    'color' => 'light',
  ],
  'patch_33' => [
    'num' => 33,
    'type' => 'patch patch_33',
    'spaces' => 5,
    'mask' => ':011:010:110',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
  ],
  'patch_34' => [
    'num' => 34,
    'type' => 'patch patch_34',
    'spaces' => 5,
    'mask' => ':11111',
    'w' => 5,
    'h' => 1,
    'color' => 'light',
  ],
  'patch_35' => [
    'num' => 35,
    'type' => 'patch patch_35',
    'spaces' => 5,
    'mask' => ':01:11:10:10',
    'w' => 2,
    'h' => 4,
    'color' => 'light',
  ],
  'patch_36' => [
    'num' => 36,
    'type' => 'patch patch_36',
    'spaces' => 5,
    'mask' => ':11:10:11',
    'w' => 2,
    'h' => 3,
    'color' => 'light',
  ],
  'patch_37' => [
    'num' => 37,
    'type' => 'patch patch_37',
    'spaces' => 6,
    'mask' => ':110:111:010',
    'w' => 3,
    'h' => 3,
    'color' => 'dark',
  ],
  'patch_38' => [
    'num' => 38,
    'type' => 'patch patch_38',
    'spaces' => 6,
    'mask' => ':001:011:111',
    'w' => 3,
    'h' => 3,
    'color' => 'dark',
  ],
  'patch_39' => [
    'num' => 39,
    'type' => 'patch patch_39',
    'spaces' => 6,
    'mask' => ':1000:1100:0111',
    'w' => 4,
    'h' => 3,
    'color' => 'dark',
  ],
  'patch_40' => [
    'num' => 40,
    'type' => 'patch patch_40',
    'spaces' => 6,
    'mask' => ':10:11:11:10',
    'w' => 2,
    'h' => 4,
    'color' => 'dark',
  ],
  'patch_41' => [
    'num' => 41,
    'type' => 'patch patch_41',
    'spaces' => 6,
    'mask' => ':100:111:011',
    'w' => 3,
    'h' => 3,
    'color' => 'dark',
  ],
  'patch_42' => [
    'num' => 42,
    'type' => 'patch patch_42',
    'spaces' => 6,
    'mask' => ':111:010:011',
    'w' => 3,
    'h' => 3,
    'color' => 'dark',
  ],
  'patch_43' => [
    'num' => 43,
    'type' => 'patch patch_43',
    'spaces' => 6,
    'mask' => ':1111:1001',
    'w' => 4,
    'h' => 2,
    'color' => 'dark',
  ],
  'patch_44' => [
    'num' => 44,
    'type' => 'patch patch_44',
    'spaces' => 6,
    'mask' => ':0111:1110',
    'w' => 4,
    'h' => 2,
    'color' => 'dark',
  ],
  'patch_45' => [
    'num' => 45,
    'type' => 'patch patch_45',
    'spaces' => 1,
    'mask' => ':1',
    'w' => 1,
    'h' => 1,
    'color' => '',
  ],
  'patch_46' => [
    'num' => 46,
    'type' => 'patch patch_46',
    'spaces' => 6,
    'mask' => ':010:111:010:010',
    'w' => 3,
    'h' => 4,
    'color' => 'dark',
  ],
  'patch_47' => [
    'num' => 47,
    'type' => 'patch patch_47',
    'spaces' => 6,
    'mask' => ':1110:1011',
    'w' => 4,
    'h' => 2,
    'color' => 'dark',
  ],
  'patch_48' => [
    'num' => 48,
    'type' => 'patch patch_48',
    'spaces' => 6,
    'mask' => ':100:111:010:010',
    'w' => 3,
    'h' => 4,
    'color' => 'dark',
  ],
  'patch_49' => [
    'num' => 49,
    'type' => 'patch patch_49',
    'spaces' => 6,
    'mask' => ':10:10:10:11:10',
    'w' => 2,
    'h' => 5,
    'color' => 'dark',
  ],
  'patch_50' => [
    'num' => 50,
    'type' => 'patch patch_50',
    'spaces' => 6,
    'mask' => ':110:111:100',
    'w' => 3,
    'h' => 3,
    'color' => 'dark',
  ],
  'patch_51' => [
    'num' => 51,
    'type' => 'patch patch_51',
    'spaces' => 6,
    'mask' => ':100:110:011:001',
    'w' => 3,
    'h' => 4,
    'color' => 'dark',
  ],
  'patch_52' => [
    'num' => 52,
    'type' => 'patch patch_52',
    'spaces' => 6,
    'mask' => ':1111:1010',
    'w' => 4,
    'h' => 2,
    'color' => 'dark',
  ],
  'patch_53' => [
    'num' => 53,
    'type' => 'patch patch_53',
    'spaces' => 7,
    'mask' => ':0001:1111:0110',
    'w' => 4,
    'h' => 3,
    'color' => 'darkest',
  ],
  'patch_54' => [
    'num' => 54,
    'type' => 'patch patch_54',
    'spaces' => 1,
    'mask' => ':1',
    'w' => 1,
    'h' => 1,
    'color' => '',
  ],
  'patch_55' => [
    'num' => 55,
    'type' => 'patch patch_55',
    'spaces' => 7,
    'mask' => ':011:111:110',
    'w' => 3,
    'h' => 3,
    'color' => 'darkest',
  ],
  'patch_56' => [
    'num' => 56,
    'type' => 'patch patch_56',
    'spaces' => 7,
    'mask' => ':1100:1111:0010',
    'w' => 4,
    'h' => 3,
    'color' => 'darkest',
  ],
  'patch_57' => [
    'num' => 57,
    'type' => 'patch patch_57',
    'spaces' => 3,
    'mask' => ':11:10',
    'w' => 2,
    'h' => 2,
    'color' => '',
  ],
  'patch_58' => [
    'num' => 58,
    'type' => 'patch patch_58',
    'spaces' => 7,
    'mask' => ':1111:0110:0100',
    'w' => 4,
    'h' => 3,
    'color' => 'darkest',
  ],
  'patch_59' => [
    'num' => 59,
    'type' => 'patch patch_59',
    'spaces' => 7,
    'mask' => ':01001:11111',
    'w' => 5,
    'h' => 2,
    'color' => 'darkest',
  ],
  'patch_60' => [
    'num' => 60,
    'type' => 'patch patch_60',
    'spaces' => 7,
    'mask' => ':101:111:110',
    'w' => 3,
    'h' => 3,
    'color' => 'darkest',
  ],
  'patch_61' => [
    'num' => 61,
    'type' => 'patch patch_61',
    'spaces' => 7,
    'mask' => ':0010:0111:1110',
    'w' => 4,
    'h' => 3,
    'color' => 'darkest',
  ],
];