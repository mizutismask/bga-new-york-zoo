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
 * states.inc.php
 *
 * NewYorkZoo game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!
if (!defined('STATE_END_GAME')) { // guard since this included multiple times
    define("STATE_PLAYER_TURN", 2);
    define("STATE_GAME_TURN_NEXT_PLAYER", 3);
    define("STATE_PLAYER_GAME_END", 4);
    define("STATE_PLAYER_PLACE_ANIMAL", 5);
    define("STATE_PLAYER_PLACE_ATTRACTION", 6);
    define("STATE_PLAYER_CHOOSE_FENCE", 7);
    define("STATE_PLAYER_CHOOSE_BREEDING_FENCE", 8);
    define("STATE_PLAYER_KEEP_ANIMAL_FROM_FULL_FENCE", 9);
    define("STATE_END_GAME", 99);


    define("TRANSITION_NEXT_PLAYER", "next");
    define("TRANSITION_END_GAME", "endGame");
    define("TRANSITION_PASS", "pass");
    define("TRANSITION_DISMISS", "dismiss");
    define("TRANSITION_PLACE_ANIMAL", "placeAnimal");
    define("TRANSITION_KEEP_ANIMAL", "keepAnimal");
    define("TRANSITION_PLACE_ATTRACTION", "placeAttraction");
    define("TRANSITION_CHOOSE_FENCE", "chooseFence");
}

$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 2)
    ),

    STATE_PLAYER_TURN => [ // main active player state 
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must move the elephant to choose an action'),
        "descriptionmyturn" => clienttranslate('${you} must move the elephant up to ${maxMoves} spaces to choose an action'),
        "type" => "activeplayer",
        "args" => "arg_playerTurn",
        "action" => "st_playerTurn",
        "possibleactions" => ["place", "getAnimals"],
        "transitions" => [
            "next" => STATE_GAME_TURN_NEXT_PLAYER,
            "last" => STATE_PLAYER_GAME_END,
            TRANSITION_PLACE_ANIMAL => STATE_PLAYER_PLACE_ANIMAL,
        ] // 
    ],

    STATE_PLAYER_PLACE_ANIMAL => [
        "name" => "placeAnimal",
        "description" => clienttranslate('${actplayer} can place an animal'),
        "descriptionmyturn" => clienttranslate('${you} can place an animal'),
        "type" => "activeplayer",
        "args" => "arg_placeAnimal",
        "possibleactions" => ["dismiss", "placeAnimal"],
        "transitions" => [
            "next" => STATE_GAME_TURN_NEXT_PLAYER,
            "last" => STATE_PLAYER_GAME_END,
            TRANSITION_PLACE_ANIMAL => STATE_PLAYER_PLACE_ANIMAL,
            TRANSITION_KEEP_ANIMAL => STATE_PLAYER_KEEP_ANIMAL_FROM_FULL_FENCE,
            TRANSITION_PLACE_ATTRACTION => STATE_PLAYER_PLACE_ATTRACTION,
            TRANSITION_DISMISS => STATE_GAME_TURN_NEXT_PLAYER,
            TRANSITION_CHOOSE_FENCE => STATE_PLAYER_CHOOSE_FENCE
        ] // 
    ],

    STATE_PLAYER_KEEP_ANIMAL_FROM_FULL_FENCE => [
        "name" => "keepAnimalFromFullFence",
        "description" => clienttranslate('${actplayer} can place an animal from the full fence to a house'),
        "descriptionmyturn" => clienttranslate('${you} can place an animal from your full fence to a house'),
        "type" => "activeplayer",
        "args" => "arg_keep_animal",
        "possibleactions" => ["dismiss", "keepAnimal"],
        "transitions" => [
            "next" => STATE_GAME_TURN_NEXT_PLAYER,
            "last" => STATE_PLAYER_GAME_END,
            TRANSITION_PLACE_ANIMAL => STATE_PLAYER_PLACE_ANIMAL,
            TRANSITION_KEEP_ANIMAL => STATE_PLAYER_KEEP_ANIMAL_FROM_FULL_FENCE,
            TRANSITION_PLACE_ATTRACTION => STATE_PLAYER_PLACE_ATTRACTION,
            TRANSITION_DISMISS => STATE_GAME_TURN_NEXT_PLAYER,
            TRANSITION_CHOOSE_FENCE => STATE_PLAYER_CHOOSE_FENCE
        ] // 
    ],

    STATE_PLAYER_PLACE_ATTRACTION => [
        "name" => "placeAttraction",
        "description" => clienttranslate('${actplayer} can place a bonus attraction'),
        "descriptionmyturn" => clienttranslate('${you} can place a bonus attraction'),
        "type" => "activeplayer",
        "args" => "arg_placeAttraction",
        "possibleactions" => ["dismiss", "placeAttraction"],
        "transitions" => [
            "next" => STATE_GAME_TURN_NEXT_PLAYER,
            "last" => STATE_PLAYER_GAME_END,
            TRANSITION_PLACE_ANIMAL => STATE_PLAYER_PLACE_ANIMAL,
            TRANSITION_PLACE_ATTRACTION => STATE_PLAYER_PLACE_ATTRACTION,
            TRANSITION_DISMISS => STATE_GAME_TURN_NEXT_PLAYER,
            TRANSITION_CHOOSE_FENCE => STATE_PLAYER_CHOOSE_FENCE
        ] // 
    ],

    STATE_PLAYER_CHOOSE_BREEDING_FENCE => [
        "name" => "chooseFence",
        "description" => clienttranslate('${actplayer} can choose fences for breeding'),
        "descriptionmyturn" => clienttranslate('${you} can choose at most two fences for breeding'),
        "type" => "multipleactiveplayer",
        "args" => "arg_chooseFences",
        "possibleactions" => ["chooseFences"],
        "transitions" => [
            "next" => STATE_GAME_TURN_NEXT_PLAYER, //maybe check fences state
        ]
    ],

    STATE_GAME_TURN_NEXT_PLAYER => [ // next player state
        "name" => "gameTurnNextPlayer", "description" => clienttranslate('Upkeep...'),
        "type" => "game", //
        "action" => "st_gameTurnNextPlayer", //
        "updateGameProgression" => true,
        "transitions" => [
            "next" => STATE_PLAYER_TURN, "loopback" => STATE_GAME_TURN_NEXT_PLAYER,
            "last" => STATE_END_GAME
        ], // STATE_PLAYER_GAME_END TODO remove after, its there to use undo during dev
    ],

    STATE_PLAYER_GAME_END => [ // active player state for debugging end of game
        "name" => "playerGameEnd",
        "description" => clienttranslate('${actplayer} Game is Over'),
        "descriptionmyturn" => clienttranslate('${you} Game is Over'),
        "type" => "activeplayer",
        "args" => "arg_playerTurn",
        "possibleactions" => ["endGame"],
        "transitions" => ["next" => STATE_END_GAME, "loopback" => STATE_PLAYER_GAME_END] // 
    ],
    // End of Game states 
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    STATE_END_GAME => [
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"), "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ]

);
