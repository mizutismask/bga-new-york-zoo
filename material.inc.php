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
$this->token_types = [
  'patch_7' => [
    'num' => 7,
    'type' => 'patch patch_7',
    'spaces' => 4,
    'income' => 1,
    'cost' => 4,
    'time' => 2,
    'mask' => ':118:001',
    'h' => 2,
    'w' => 3,
  ],
];