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
  define("LIGHTEST_GREEN", "lightest");
  define("LIGHT_GREEN", "light");
  define("DARK_GREEN", "dark");
  define("DARKEST_GREEN", "darkest");

  define("MEERKAT", "meerkat");
  define("FLAMINGO", "flamingo");
  define("KANGAROO", "kangaroo");
  define("PENGUIN", "penguin");
  define("FOX", "fox");

  define("ANIMAL", "animal");
  define("PATCH", "patch");
}
//by player count, then player order
$this->boards = [
  '2' => [
    '1' => [
      'animals' => [MEERKAT, FLAMINGO],
      'houses' => 3,
    ],
    '2' => [
      'animals' => [MEERKAT, KANGAROO],
      'houses' => 3,
    ],
  ],
  '3' => [
    '1' => [
      'animals' => [MEERKAT, FLAMINGO],
      'houses' => 2,
    ],
    '2' => [
      'animals' => [MEERKAT, FLAMINGO],
      'houses' => 3,
    ],
    '3' => [
      'animals' => [MEERKAT, KANGAROO],
      'houses' => 3,
    ],
  ],
  '4' => [
    '1' => [
      'animals' => [MEERKAT, FLAMINGO],
      'houses' => 2,
    ],
    '2' => [
      'animals' => [MEERKAT, FLAMINGO],
      'houses' => 3,
    ],
    '3' => [
      'animals' => [MEERKAT, KANGAROO],
      'houses' => 3,
    ],
    '4' => [
      'animals' => [MEERKAT, KANGAROO],
      'houses' => 4,
    ],
  ],
  '5' => [
    '1' => [
      'animals' => [MEERKAT, FLAMINGO],
      'houses' => 2,
    ],
    '2' => [
      'animals' => [MEERKAT, FLAMINGO],
      'houses' => 3,
    ],
    '3' => [
      'animals' => [MEERKAT, KANGAROO],
      'houses' => 3,
    ],
    '4' => [
      'animals' => [MEERKAT, KANGAROO],
      'houses' => 4,
    ],
    '5' => [
      'animals' => [FLAMINGO, KANGAROO],
      'houses' => 4,
    ],
  ],
];

/* Action board */
$topY = 2.42;
$topYLine2 = 70.56;
$smallWidth = 6.42;
$mediumWidth = 7.30;
$bigWidth = 10.49;
$height = 27;
$offsetZones = 0.01;
$this->actionStripZones = [];
$this->actionStripZones['action_zone_1'] = [
  'topX' =>  0.52,
  'topY' =>  $topY,
  'width' =>  $bigWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_2'] = [
  'topX' =>  $this->actionStripZones['action_zone_1']['topX'] + $this->actionStripZones['action_zone_1']['width'] + $offsetZones,
  'topY' =>  $topY,
  'width' =>  $smallWidth,
  'height' =>  $height,
  'animals' =>  [FOX, KANGAROO],
  'type' =>  ANIMAL,
];
$this->actionStripZones['action_zone_3'] = [
  'topX' =>  $this->actionStripZones['action_zone_2']['topX'] + $this->actionStripZones['action_zone_2']['width'] + $offsetZones,
  'topY' =>  $topY,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_4'] = [
  'topX' => 27.28,
  'topY' =>  $topY,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_5'] = [
  'topX' =>  $this->actionStripZones['action_zone_4']['topX'] + $this->actionStripZones['action_zone_4']['width'] + $offsetZones,
  'topY' =>  $topY,
  'width' =>  $smallWidth,
  'height' =>  $height,
  'animals' =>  [PENGUIN, MEERKAT],
  'type' =>  ANIMAL,
];
$this->actionStripZones['action_zone_6'] = [
  'topX' =>  $this->actionStripZones['action_zone_5']['topX'] + $this->actionStripZones['action_zone_5']['width'] + $offsetZones,
  'topY' =>  $topY,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_7'] = [
  'topX' =>  $this->actionStripZones['action_zone_6']['topX'] + $this->actionStripZones['action_zone_6']['width'] + $offsetZones,
  'topY' =>  $topY,
  'width' =>  $smallWidth,
  'height' =>  $height,
  'animals' =>  [PENGUIN, FOX],
  'type' =>  ANIMAL,
];
$this->actionStripZones['action_zone_8'] = [
  'topX' =>  $this->actionStripZones['action_zone_7']['topX'] + $this->actionStripZones['action_zone_7']['width'] + $offsetZones,
  'topY' =>  $topY,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_9'] = [
  'topX' =>  64.68,
  'topY' =>  $topY,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_10'] = [
  'topX' =>  $this->actionStripZones['action_zone_9']['topX'] + $this->actionStripZones['action_zone_9']['width'] + $offsetZones,
  'topY' =>  $topY,
  'width' =>  $smallWidth,
  'height' =>  $height,
  'animals' =>  [FOX, FLAMINGO],
  'type' =>  ANIMAL,
];
$this->actionStripZones['action_zone_11'] = [
  'topX' =>  $this->actionStripZones['action_zone_10']['topX'] + $this->actionStripZones['action_zone_10']['width'] + $offsetZones,
  'topY' =>  $topY,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_12'] = [
  'topX' =>  $this->actionStripZones['action_zone_11']['topX'] + $this->actionStripZones['action_zone_11']['width'] + $offsetZones,
  'topY' =>  $topY,
  'width' =>  $smallWidth,
  'height' =>  $height,
  'animals' =>  [PENGUIN, KANGAROO],
  'type' =>  ANIMAL,
];
$this->actionStripZones['action_zone_13'] = [
  'topX' =>  $this->actionStripZones['action_zone_12']['topX'] + $this->actionStripZones['action_zone_12']['width'] + $offsetZones,
  'topY' =>  $topY,
  'width' =>  $smallWidth,
  'height' =>  100,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_14'] = [
  'topX' =>  78.46,
  'topY' =>  $topYLine2,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_15'] = [
  'topX' =>  $this->actionStripZones['action_zone_14']['topX'] - $smallWidth - $offsetZones,
  'topY' =>  $topYLine2,
  'width' =>  $smallWidth,
  'height' =>  $height,
  'animals' =>  [KANGAROO, MEERKAT],
  'type' =>  ANIMAL,
];
$this->actionStripZones['action_zone_16'] = [
  'topX' =>  $this->actionStripZones['action_zone_15']['topX'] - $mediumWidth - $offsetZones,
  'topY' =>  $topYLine2,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_17'] = [
  'topX' =>  $this->actionStripZones['action_zone_16']['topX'] - $smallWidth - $offsetZones,
  'topY' =>  $topYLine2,
  'width' =>  $smallWidth,
  'height' =>  $height,
  'animals' =>  [FLAMINGO, MEERKAT],
  'type' =>  ANIMAL,
];
$this->actionStripZones['action_zone_18'] = [
  'topX' =>  $this->actionStripZones['action_zone_17']['topX'] - $mediumWidth - $offsetZones,
  'topY' =>  $topYLine2,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_19'] = [
  'topX' =>  41.05,
  'topY' =>  $topYLine2,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_20'] = [
  'topX' =>  $this->actionStripZones['action_zone_19']['topX'] - $smallWidth - $offsetZones,
  'topY' =>  $topYLine2,
  'width' =>  $smallWidth,
  'height' =>  $height,
  'animals' =>  [FLAMINGO, KANGAROO],
  'type' =>  ANIMAL,
];
$this->actionStripZones['action_zone_21'] = [
  'topX' =>  $this->actionStripZones['action_zone_20']['topX'] - $mediumWidth - $offsetZones,
  'topY' =>  $topYLine2,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_22'] = [
  'topX' =>  $this->actionStripZones['action_zone_21']['topX'] - $smallWidth - $offsetZones,
  'topY' =>  $topYLine2,
  'width' =>  $smallWidth,
  'height' =>  $height,
  'animals' =>  [FOX, MEERKAT],
  'type' =>  ANIMAL,
];
$this->actionStripZones['action_zone_23'] = [
  'topX' =>  $this->actionStripZones['action_zone_22']['topX'] - $mediumWidth - $offsetZones,
  'topY' =>  $topYLine2,
  'width' =>  $mediumWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_24'] = [
  'topX' =>  0.52,
  'topY' =>  $topYLine2,
  'width' =>  $bigWidth,
  'height' =>  $height,
  'type' =>  PATCH,
];
$this->actionStripZones['action_zone_25'] = [
  'topX' =>  0.52,
  'topY' =>  36.96,
  'width' =>  $smallWidth,
  'height' =>  26.57,
  'animals' =>  [PENGUIN, FLAMINGO],
  'type' =>  ANIMAL,
];

$this->birthZones = [
  'birth_zone_1' => [
    'triggerZone' =>  4,
    'animal' =>  KANGAROO,
  ],
  'birth_zone_2' => [
    'triggerZone' =>  9,
    'animal' =>  PENGUIN,
  ],
  'birth_zone_3' => [
    'triggerZone' =>  14,
    'animal' =>  FOX,
  ],
  'birth_zone_4' => [
    'triggerZone' =>  19,
    'animal' =>  MEERKAT,
  ],
  'birth_zone_5' => [
    'triggerZone' =>  24,
    'animal' =>  FLAMINGO,
  ],
];


$this->token_types = [

  'token_neutral' => [
    'name' => clienttranslate("Elephant"),
    'w' => 1, 'h' => 1,
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
    'tooltip' => clienttranslate("Counter for remaining empty spaces. The first player who fill his board wins the game."),
  ],
  'flamingo' => [
    'type' => FLAMINGO,
    'name' => clienttranslate("Flamingo"),
  ],
  'meerkat' => [
    'type' => MEERKAT,
    'name' => clienttranslate("Meerkat"),
  ],
  'fox' => [
    'type' => FOX,
    'name' => clienttranslate("Fox"),
  ],
  'penguin' => [
    'type' => PENGUIN,
    'name' => clienttranslate("Penguin"),
  ],
  'kangaroo' => [
    'type' => KANGAROO,
    'name' => clienttranslate("Kangaroo"),
  ],

  //1 to indicate filler, then playerCount_playerOrder
   'patch_121' => [
    'num' => 121,
    'type' => 'patch filler filler_2_1',
    'spaces' => 19,
    'mask' => ':1111:1111:1111:1111:1110',
    'w' => 4,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
 'patch_122' => [
    'num' => 122,
    'type' => 'patch filler filler_2_2',
    'spaces' => 21,
    'mask' => ':11111:11110:11110:11110:11110',
    'w' =>5,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
  'patch_131' => [
    'num' => 131,
    'type' => 'patch filler filler_3_1',
    'spaces' => 18,
    'mask' => ':1111:1111:1111:1110:1110',
    'w' => 4,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
 'patch_132' => [
    'num' => 132,
    'type' => 'patch filler filler_3_2',
    'spaces' => 19,
    'mask' => ':1111:1111:1111:1111:1110',
    'w' => 4,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
  'patch_133' => [
    'num' => 133,
    'type' => 'patch filler filler_3_3',
    'spaces' => 21,
    'mask' => ':11111:11110:11110:11110:11110',
    'w' => 5,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],

  'patch_141' => [
    'num' => 141,
    'type' => 'patch filler filler_4_1',
    'spaces' => 18,
    'mask' => ':1111:1111:1111:1110:1110',
    'w' => 4,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
 'patch_142' => [
    'num' => 142,
    'type' => 'patch filler filler_4_2',
    'spaces' => 19,
    'mask' => ':1111:1111:1111:1111:1110',
    'w' => 4,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
  'patch_143' => [
    'num' => 143,
    'type' => 'patch filler filler_4_3',
    'spaces' => 21,
    'mask' => ':11111:11110:11110:11110:11110',
    'w' => 5,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
  'patch_144' => [
    'num' => 144,
    'type' => 'patch filler filler_4_4',
    'spaces' => 22,
    'mask' => ':11111:11111:11110:11110:11110',
    'w' => 5,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],

  'patch_151' => [
    'num' => 151,
    'type' => 'patch filler filler_5_1',
    'spaces' => 18,
    'mask' => ':1111:1111:1111:1110:1110',
    'w' => 4,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
 'patch_152' => [
    'num' => 152,
    'type' => 'patch filler filler_5_2',
    'spaces' => 19,
    'mask' => ':1111:1111:1111:1111:1110',
    'w' => 4,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
  'patch_153' => [
    'num' => 153,
    'type' => 'patch filler filler_5_3',
    'spaces' => 21,
    'mask' => ':11111:11110:11110:11110:11110',
    'w' => 5,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
  'patch_154' => [
    'num' => 154,
    'type' => 'patch filler filler_5_4',
    'spaces' => 22,
    'mask' => ':11111:11111:11110:11110:11110',
    'w' => 5,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],
  'patch_155' => [
    'num' => 155,
    'type' => 'patch filler filler_5_5',
    'spaces' => 23,
    'mask' => ':11111:11111:11111:11110:11110',
    'w' => 5,
    'h' => 5,
    'color' => 'filler',
    'occurrences' => 1,
  ],

  'patch_1' => [
    'num' => 1,
    'type' => 'patch patch_1',
    'spaces' => 4,
    'mask' => ':010:111',
    'w' => 3,
    'h' => 2,
    'color' => 'lightest',
    'occurrences' => 1,
  ],
  'patch_2' => [
    'num' => 2,
    'type' => 'patch patch_2',
    'spaces' => 6,
    'mask' => ':111:111',
    'w' => 3,
    'h' => 2,
    'color' => 'bonus',
    'occurrences' => 1,
  ],
  'patch_3' => [
    'num' => 3,
    'type' => 'patch patch_3',
    'spaces' => 4,
    'mask' => ':01:11:10',
    'w' => 2,
    'h' => 3,
    'color' => 'lightest',
    'occurrences' => 1,
  ],
  'patch_4' => [
    'num' => 4,
    'type' => 'patch patch_4',
    'spaces' => 8,
    'mask' => ':1111:1111',
    'w' => 4,
    'h' => 2,
    'color' => 'bonus',
    'occurrences' => 1,
  ],
  'patch_5' => [
    'num' => 5,
    'type' => 'patch patch_5',
    'spaces' => 4,
    'mask' => ':01:11:10',
    'w' => 2,
    'h' => 3,
    'color' => 'lightest',
    'occurrences' => 1,
  ],
  'patch_6' => [
    'num' => 6,
    'type' => 'patch patch_6',
    'spaces' => 4,
    'mask' => ':11:11',
    'w' => 2,
    'h' => 2,
    'color' => 'bonus',
    'occurrences' => 1,
  ],
  'patch_7' => [
    'num' => 7,
    'type' => 'patch patch_7',
    'spaces' => 4,
    'mask' => ':1111',
    'w' => 4,
    'h' => 1,
    'color' => 'bonus',
    'occurrences' => 1,
  ],
  'patch_8' => [
    'num' => 8,
    'type' => 'patch patch_8',
    'spaces' => 3,
    'mask' => ':111',
    'w' => 3,
    'h' => 1,
    'color' => 'bonus',
    'occurrences' => 1,
  ],
  'patch_9' => [
    'num' => 9,
    'type' => 'patch patch_9',
    'spaces' => 3,
    'mask' => ':111',
    'w' => 3,
    'h' => 1,
    'color' => 'bonus',
    'occurrences' => 1,
  ],
  'patch_10' => [
    'num' => 10,
    'type' => 'patch patch_10',
    'spaces' => 3,
    'mask' => ':111',
    'w' => 3,
    'h' => 1,
    'color' => 'bonus',
    'occurrences' => 1,
  ],
  'patch_11' => [
    'num' => 11,
    'type' => 'patch patch_11',
    'spaces' => 3,
    'mask' => ':11:10',
    'w' => 2,
    'h' => 2,
    'color' => 'bonus',
    'occurrences' => 1,
  ],
  'patch_12' => [
    'num' => 12,
    'type' => 'patch patch_12',
    'spaces' => 3,
    'mask' => ':11:10',
    'w' => 2,
    'h' => 2,
    'color' => 'bonus',
    'occurrences' => 1,
  ],
  'patch_13' => [
    'num' => 13,
    'type' => 'patch patch_13',
    'spaces' => 4,
    'mask' => ':010:111',
    'w' => 3,
    'h' => 2,
    'color' => 'lightest',
    'occurrences' => 1,
  ],
  'patch_14' => [
    'num' => 14,
    'type' => 'patch patch_14',
    'spaces' => 4,
    'mask' => ':11:10:10',
    'w' => 2,
    'h' => 3,
    'color' => 'lightest',
    'occurrences' => 1,
  ],
  'patch_15' => [
    'num' => 15,
    'type' => 'patch patch_15',
    'spaces' => 5,
    'mask' => ':1000:1111',
    'w' => 4,
    'h' => 2,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_16' => [
    'num' => 16,
    'type' => 'patch patch_16',
    'spaces' => 4,
    'mask' => ':11:10:10',
    'w' => 2,
    'h' => 3,
    'color' => 'lightest',
    'occurrences' => 1,
  ],
  'patch_17' => [
    'num' => 17,
    'type' => 'patch patch_17',
    'spaces' => 4,
    'mask' => ':11:11',
    'w' => 2,
    'h' => 2,
    'color' => 'lightest',
    'occurrences' => 1,
  ],
  'patch_18' => [
    'num' => 18,
    'type' => 'patch patch_18',
    'spaces' => 4,
    'mask' => ':1111',
    'w' => 4,
    'h' => 1,
    'color' => 'lightest',
    'occurrences' => 1,
  ],
  'patch_19' => [
    'num' => 19,
    'type' => 'patch patch_19',
    'spaces' => 5,
    'mask' => ':010:111:010',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_20' => [
    'num' => 20,
    'type' => 'patch patch_20',
    'spaces' => 5,
    'mask' => ':01:11:01:01',
    'w' => 2,
    'h' => 4,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_21' => [
    'num' => 21,
    'type' => 'patch patch_21',
    'spaces' => 5,
    'mask' => ':011:110:010',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_22' => [
    'num' => 22,
    'type' => 'patch patch_22',
    'spaces' => 5,
    'mask' => ':111:010:010',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_23' => [
    'num' => 23,
    'type' => 'patch patch_23',
    'spaces' => 5,
    'mask' => ':011:110:100',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_24' => [
    'num' => 24,
    'type' => 'patch patch_24',
    'spaces' => 5,
    'mask' => ':011:110:100',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_25' => [
    'num' => 25,
    'type' => 'patch patch_25',
    'spaces' => 1,
    'mask' => ':1',
    'w' => 1,
    'h' => 1,
    'color' => 'bonus',
    'occurrences' => 6,
  ],
  'patch_26' => [
    'num' => 26,
    'type' => 'patch patch_26',
    'spaces' => 5,
    'mask' => ':01:11:01:01',
    'w' => 2,
    'h' => 4,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_27' => [
    'num' => 27,
    'type' => 'patch patch_27',
    'spaces' => 2,
    'mask' => ':11',
    'w' => 2,
    'h' => 1,
    'color' => 'bonus',
    'occurrences' => 2,
  ],
  'patch_28' => [
    'num' => 28,
    'type' => 'patch patch_28',
    'spaces' => 2,
    'mask' => ':11',
    'w' => 2,
    'h' => 1,
    'color' => 'bonus',
    'occurrences' => 2,
  ],
  'patch_29' => [
    'num' => 29,
    'type' => 'patch patch_29',
    'spaces' => 2,
    'mask' => ':11',
    'w' => 2,
    'h' => 1,
    'color' => 'bonus',
    'occurrences' => 2,
  ],
  'patch_30' => [
    'num' => 30,
    'type' => 'patch patch_30',
    'spaces' => 5,
    'mask' => ':011:110:010',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_31' => [
    'num' => 31,
    'type' => 'patch patch_31',
    'spaces' => 5,
    'mask' => ':001:001:111',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_32' => [
    'num' => 32,
    'type' => 'patch patch_32',
    'spaces' => 5,
    'mask' => ':011:111',
    'w' => 3,
    'h' => 2,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_33' => [
    'num' => 33,
    'type' => 'patch patch_33',
    'spaces' => 5,
    'mask' => ':011:010:110',
    'w' => 3,
    'h' => 3,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_34' => [
    'num' => 34,
    'type' => 'patch patch_34',
    'spaces' => 5,
    'mask' => ':11111',
    'w' => 5,
    'h' => 1,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_35' => [
    'num' => 35,
    'type' => 'patch patch_35',
    'spaces' => 5,
    'mask' => ':01:11:10:10',
    'w' => 2,
    'h' => 4,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_36' => [
    'num' => 36,
    'type' => 'patch patch_36',
    'spaces' => 5,
    'mask' => ':11:10:11',
    'w' => 2,
    'h' => 3,
    'color' => 'light',
    'occurrences' => 1,
  ],
  'patch_37' => [
    'num' => 37,
    'type' => 'patch patch_37',
    'spaces' => 6,
    'mask' => ':110:111:010',
    'w' => 3,
    'h' => 3,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_38' => [
    'num' => 38,
    'type' => 'patch patch_38',
    'spaces' => 6,
    'mask' => ':001:011:111',
    'w' => 3,
    'h' => 3,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_39' => [
    'num' => 39,
    'type' => 'patch patch_39',
    'spaces' => 6,
    'mask' => ':1000:1100:0111',
    'w' => 4,
    'h' => 3,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_40' => [
    'num' => 40,
    'type' => 'patch patch_40',
    'spaces' => 6,
    'mask' => ':10:11:11:10',
    'w' => 2,
    'h' => 4,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_41' => [
    'num' => 41,
    'type' => 'patch patch_41',
    'spaces' => 6,
    'mask' => ':100:111:011',
    'w' => 3,
    'h' => 3,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_42' => [
    'num' => 42,
    'type' => 'patch patch_42',
    'spaces' => 6,
    'mask' => ':111:010:011',
    'w' => 3,
    'h' => 3,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_43' => [
    'num' => 43,
    'type' => 'patch patch_43',
    'spaces' => 6,
    'mask' => ':1111:1001',
    'w' => 4,
    'h' => 2,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_44' => [
    'num' => 44,
    'type' => 'patch patch_44',
    'spaces' => 6,
    'mask' => ':0111:1110',
    'w' => 4,
    'h' => 2,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_45' => [
    'num' => 45,
    'type' => 'patch patch_45',
    'spaces' => 1,
    'mask' => ':1',
    'w' => 1,
    'h' => 1,
    'color' => 'bonus',
    'occurrences' => 5,
  ],
  'patch_46' => [
    'num' => 46,
    'type' => 'patch patch_46',
    'spaces' => 6,
    'mask' => ':010:111:010:010',
    'w' => 3,
    'h' => 4,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_47' => [
    'num' => 47,
    'type' => 'patch patch_47',
    'spaces' => 6,
    'mask' => ':1110:1011',
    'w' => 4,
    'h' => 2,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_48' => [
    'num' => 48,
    'type' => 'patch patch_48',
    'spaces' => 6,
    'mask' => ':100:111:010:010',
    'w' => 3,
    'h' => 4,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_49' => [
    'num' => 49,
    'type' => 'patch patch_49',
    'spaces' => 6,
    'mask' => ':10:10:10:11:10',
    'w' => 2,
    'h' => 5,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_50' => [
    'num' => 50,
    'type' => 'patch patch_50',
    'spaces' => 6,
    'mask' => ':110:111:100',
    'w' => 3,
    'h' => 3,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_51' => [
    'num' => 51,
    'type' => 'patch patch_51',
    'spaces' => 6,
    'mask' => ':100:110:011:001',
    'w' => 3,
    'h' => 4,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_52' => [
    'num' => 52,
    'type' => 'patch patch_52',
    'spaces' => 6,
    'mask' => ':1111:1010',
    'w' => 4,
    'h' => 2,
    'color' => 'dark',
    'occurrences' => 1,
  ],
  'patch_53' => [
    'num' => 53,
    'type' => 'patch patch_53',
    'spaces' => 7,
    'mask' => ':0001:1111:0110',
    'w' => 4,
    'h' => 3,
    'color' => 'darkest',
    'occurrences' => 1,
  ],
  'patch_54' => [
    'num' => 54,
    'type' => 'patch patch_54',
    'spaces' => 1,
    'mask' => ':1',
    'w' => 1,
    'h' => 1,
    'color' => 'bonus',
    'occurrences' => 5,
  ],
  'patch_55' => [
    'num' => 55,
    'type' => 'patch patch_55',
    'spaces' => 7,
    'mask' => ':011:111:110',
    'w' => 3,
    'h' => 3,
    'color' => 'darkest',
    'occurrences' => 1,
  ],
  'patch_56' => [
    'num' => 56,
    'type' => 'patch patch_56',
    'spaces' => 7,
    'mask' => ':1100:1111:0010',
    'w' => 4,
    'h' => 3,
    'color' => 'darkest',
    'occurrences' => 1,
  ],
  'patch_57' => [
    'num' => 57,
    'type' => 'patch patch_57',
    'spaces' => 3,
    'mask' => ':11:10',
    'w' => 2,
    'h' => 2,
    'color' => 'bonus',
    'occurrences' => 1,
  ],
  'patch_58' => [
    'num' => 58,
    'type' => 'patch patch_58',
    'spaces' => 7,
    'mask' => ':1111:0110:0100',
    'w' => 4,
    'h' => 3,
    'color' => 'darkest',
    'occurrences' => 1,
  ],
  'patch_59' => [
    'num' => 59,
    'type' => 'patch patch_59',
    'spaces' => 7,
    'mask' => ':01001:11111',
    'w' => 5,
    'h' => 2,
    'color' => 'darkest',
    'occurrences' => 1,
  ],
  'patch_60' => [
    'num' => 60,
    'type' => 'patch patch_60',
    'spaces' => 7,
    'mask' => ':101:111:110',
    'w' => 3,
    'h' => 3,
    'color' => 'darkest',
    'occurrences' => 1,
  ],
  'patch_61' => [
    'num' => 61,
    'type' => 'patch patch_61',
    'spaces' => 7,
    'mask' => ':0010:0111:1110',
    'w' => 4,
    'h' => 3,
    'color' => 'darkest',
    'occurrences' => 1,
  ],
];
