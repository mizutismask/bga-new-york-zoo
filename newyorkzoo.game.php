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
 * newyorkzoo.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */


require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');
require_once('modules/tokens.php');
require_once('modules/EuroGame.php');
require_once('modules/PwMatrix.php');

if (!defined('OFFSET')) {
    define("OFFSET", 5);
    define("ACTION_ZONES_COUNT", 25);
    define("ACTION_ZONE_PREFIX", "action_zone_");
    define('GS_ANIMAL_TO_PLACE', "animalToPlace");
    define('GS_OTHER_ANIMAL_TO_PLACE', "otherAnimalToPlace");
    define("GS_BREEDING", "breeding");
    define("GS_ANIMAL_TO_KEEP", "animalToKeep");
    define("GS_LAST_FENCE_PLACED", "lastFencePlaced");
}

class NewYorkZoo extends EuroGame {
    function __construct() {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels(array(
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
            GS_ANIMAL_TO_PLACE => 10, //animalType 1 from main action
            GS_OTHER_ANIMAL_TO_PLACE => 11,  //animalType 2 from main action
            GS_BREEDING => 12,
            GS_ANIMAL_TO_KEEP => 13, //from full fence
            GS_LAST_FENCE_PLACED => 14,
        ));

        $this->tokens = new Tokens();
        $this->matrix = new PwMatrix($this);
    }

    protected function getGameName() {
        // Used for translations and stuff. Please do not modify.
        return "newyorkzoo";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array()) {
        try {
            // Set the colors of the players with HTML color code
            // The default below is red/green/blue/orange/brown
            // The number of colors defined here must correspond to the maximum number of players allowed for the gams
            $gameinfos = self::getGameinfos();
            $default_colors = $gameinfos['player_colors'];

            // Create players
            // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
            $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
            $values = array();
            foreach ($players as $player_id => $player) {
                $color = array_shift($default_colors);
                $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
            }
            $sql .= implode($values, ',');
            self::DbQuery($sql);
            self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
            self::reloadPlayersBasicInfos();

            $this->initTables();

            /************ Start the game initialization *****/

            // Init global values with their initial values
            //self::setGameStateInitialValue( 'my_first_global_variable', 0 );
            self::setGameStateInitialValue(GS_ANIMAL_TO_PLACE, 0);
            self::setGameStateInitialValue(GS_OTHER_ANIMAL_TO_PLACE, 0);
            self::setGameStateInitialValue(GS_BREEDING, 0);

            // Init game statistics
            // (note: statistics used in this file must be defined in your stats.inc.php file)
            //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
            //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

            // TODO: setup the initial game situation here


            // Activate first player (which is in general a good idea :) )
            $this->activeNextPlayer();
        } catch (Exception $e) {
            // logging does not actually work in game init :(
            $this->error("Fatal error while creating game");
            $this->dump('err', $e);
        }

        /************ End of the game initialization *****/
    }

    function debugZoo() {
    }

    function initTables() {
        $this->createTokens();

        //creates houses and gets animals from the board
        $players = $this->loadPlayersBasicInfos();
        //self::dump("**********************",$players);
        $i = 1;
        foreach ($players as $player_id => $player) {
            //$this->tokens->createTokensPack($player_id . "_house_{INDEX}",  "house_" . $playerOrder, 3);
            $playerOrder = $player["player_no"];
            $boardConf = $this->boards[count($players)][$playerOrder];
            $h = 1;
            self::dump("***********boardConf***********", $boardConf);
            foreach ($boardConf["animals"] as $animal) {
                $this->tokens->moveToken($animal . '_' . $i, "house_" . $playerOrder . "_" . $h, 0);
                $i++;
                $h++;
            }
        }
        $this->setupPatchesOnBoard();
    }

    function createTokens() {
        //patches
        foreach ($this->token_types as $id => &$info) {
            if (startsWith($id, 'patch')) {
                $occ = $this->getRulesFor($id, "occurrences");
                $patchColor = $this->getRulesFor($id, "color");
                $dest = $patchColor === "bonus" ? "bonus_market" : "limbo";
                $state = $patchColor === "bonus" ? $this->getRulesFor($id, "spaces") : 0;
                if ($occ > 1) {
                    $this->tokens->createTokensPack($id . "_{INDEX}", $dest, $occ, 1, null, $state);
                } else {
                    $this->tokens->createToken($id, $dest, $state);
                }
            }
        }

        //animals
        $this->tokens->createTokensPack("meerkat_{INDEX}", "limbo", 28);
        $this->tokens->createTokensPack("flamingo_{INDEX}", "limbo", 26);
        $this->tokens->createTokensPack("kangaroo_{INDEX}", "limbo", 24);
        $this->tokens->createTokensPack("penguin_{INDEX}", "limbo", 24);
        $this->tokens->createTokensPack("fox_{INDEX}", "limbo", 24);
        $this->tokens->createToken("token_neutral", "action_zone_1", 0); //elephant todo remettre action_zone_10
    }

    function setupPatchesOnBoard() {
        $colors = [LIGHTEST_GREEN, LIGHT_GREEN, DARK_GREEN, DARKEST_GREEN];
        $layers = array();
        foreach ($colors as $color) {
            $locations = $this->getPolyominoesLocationOnBoard($color);
            $patches = $this->mtCollectWithFieldValue("color", $color);
            shuffle($patches);
            foreach ($patches as $i => $patchId) {
                $loc = $locations[$i];
                if (!isset($layers[$loc])) {
                    $layers[$loc] = 1;
                } else {
                    $layers[$loc]++;
                }
                //$state = $this->token_types[$patchId]["w"] > $this->token_types[$patchId]["h"] ? 1 : 0;
                $this->tokens->moveToken($patchId, ACTION_ZONE_PREFIX . $loc, $layers[$loc], null);
            }
        }
        $players = $this->loadPlayersBasicInfos();
        $players_nbr = count($players);
        if ($players_nbr == 2) {
            $locations = $this->getPolyominoesLocationsOnBoard();
            foreach ($locations as $loc) {
                $patch = $this->tokens->getTokenOnTop(ACTION_ZONE_PREFIX . $loc, true, "patch");
                $this->tokens->moveToken($patch["key"], "limbo");
                //todo distribuer équitablement par couleur
            }
        }
        //place fake filler token at the top left corner
        for ($i = 0; $i < $players_nbr; $i++) {
            $order = $i + 1;
            $this->tokens->moveToken("patch_1" . $players_nbr . $order, "square_" . $order . "_0_0");
        }
    }
    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas() {
        $result = array();

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb($sql);


        foreach ($this->token_types as $id => &$info) {
            if (startsWith($id, 'patch')) {
                $mask = $info['mask'];
                $mask = preg_replace('/11/', '21', $mask, 1);
                if (strpos($mask, '2') === false) {
                    $mask = preg_replace('/1:/', '3:', $mask, 1);
                }
                if (strpos($mask, '3') === false) {
                    $mask = preg_replace('/1$/', '3', $mask, 1);
                }
                if (strpos($mask, '3') === false) {
                    $mask = preg_replace('/1/', '3', $mask, 1);
                }
                $matrix = $this->matrix->pieceMatrix($mask);

                // first value of coords of 2 or 3 if 2 not found
                $info['lcoords'] = ($this->matrix->valueCoords($matrix, 2) + $this->matrix->valueCoords($matrix, 3))[0];
            }
        }
        $result = parent::getAllDatas();
        $players_basic = $this->loadPlayersBasicInfos();
        foreach ($players_basic as $player_info) {
            $order = $player_info['player_no'];
            $occupancy = $this->getOccupancyMatrix($order);
            $unoccup_count = $this->getOccupancyEmpty($occupancy);
            $this->setCounter($result['counters'], "empties_${order}_counter", $unoccup_count);
        }
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
        $result['gridSize'] = self::getGridSize();
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression() {
        // TODO: compute and return the game progression

        return 0;
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    //////////// 
    function filterAnimals($tokens) {
        return array_filter($tokens, function ($tok) {
            return str_starts_with($tok, FOX) || str_starts_with($tok, PENGUIN) || str_starts_with($tok, MEERKAT) || str_starts_with($tok, FLAMINGO) || str_starts_with($tok, KANGAROO);
        }, ARRAY_FILTER_USE_KEY);
    }

    function filterAnimalType($tokens, $animalType) {
        return array_filter($tokens, function ($tok) use ($animalType) {
            return str_starts_with($tok, $animalType);
        });
    }

    function getUniqueMasks($patches) {
        $uniques = [];
        foreach ($patches as $patchId) {
            $uniques[$this->getRulesFor($patchId, "mask")] = $patchId;
        }
        return array_values($uniques);
    }

    function getFenceType($fenceKey) {
        return self::getUniqueValueFromDB("SELECT animal_type FROM fence WHERE token_key = '$fenceKey'");
    }

    function getPatchFromSquare($square) {
        return self::getUniqueValueFromDB("SELECT token_key FROM fence_squares WHERE square = '$square'");
    }

    function getFenceSquares($fenceKey) {
        return self::getObjectListFromDB("SELECT square FROM fence_squares WHERE token_key = '$fenceKey'", true);
    }

    function isFenceFull($fenceId) {
        $squares = $this->getFenceSquares($fenceId);
        $empty = $this->filterFreeSquares($squares);
        self::dump('***********isFenceFull********', !$empty);
        return !$empty;
    }

    /** Empty fence and return animal type */
    function emptyFence($fenceId) {
        $type = $this->getFenceType($fenceId);
        $squares = $this->getFenceSquares($fenceId);
        //self::dump('******************getFenceSquares*', $squares);
        $occupied = $this->filterOccupiedSquares($squares);
        $tokens = $this->tokens->getTokensInLocations($occupied);
        $tokens = $this->filterAnimals($tokens);
        $this->dbSetTokensLocation($tokens, "limbo", null, '${player_name} has a full fence', []);
        return $type;
    }

    function isHouse($targetName) {
        return str_starts_with($targetName, "house_");
    }

    function transformMask($mask) {
        $mask = preg_replace('/11/', '21', $mask, 1);
        if (strpos($mask, '2') === false) {
            $mask = preg_replace('/1:/', '3:', $mask, 1);
        }
        if (strpos($mask, '3') === false) {
            $mask = preg_replace('/1$/', '3', $mask, 1);
        }
        if (strpos($mask, '3') === false) {
            $mask = preg_replace('/1/', '3', $mask, 1);
        }
        return $mask;
    }
    /*
        In this space, you can put any utility methods useful for your game logic
    */
    function getGridSize() {
        $players = $this->loadPlayersBasicInfos();
        $players_nbr = count($players);
        if ($players_nbr == 2) {
            return array(14, 9); //x,y
        } else if ($players_nbr == 3) {
            return array(11, 9); //x,y
        } else if ($players_nbr == 4) {
            return array(9, 9); //x,y
        } else if ($players_nbr == 5) {
            return array(8, 9); //x,y
        }
    }

    function getGridWidth() {
        $boardSize = $this->getGridSize();
        return $boardSize[0];
    }

    function getGridHeight() {
        $boardSize = $this->getGridSize();
        return $boardSize[1];
    }

    function getMatrixStart() {
        return 0 - OFFSET;
    }

    function getMatrixWidthEnd() {
        return $this->getGridWidth() +  OFFSET;
    }
    function getMatrixHeightEnd() {
        return $this->getGridHeight() + OFFSET;
    }

    function getPolyominoesCount($color) {
        $players = $this->loadPlayersBasicInfos();
        $players_nbr = count($players);
        if ($players_nbr >= 2) {
            switch ($color) {
                case LIGHTEST_GREEN:
                    return 8;
                case LIGHT_GREEN:
                    return 15;
                case DARK_GREEN:
                    return 15;
                case DARKEST_GREEN:
                    return 7;
            }
        }
    }
    //todo ajouter les autres nb de joueurs
    function getPolyominoesLocationOnBoard($color) {
        $players = $this->loadPlayersBasicInfos();
        $players_nbr = count($players);
        //if ($players_nbr >= 2) {
        switch ($color) {
            case LIGHTEST_GREEN:
                return [4, 6, 8, 9, 18, 19, 21, 23];
            case LIGHT_GREEN:
            case DARK_GREEN:
                return [1, 3, 4, 6, 8, 9, 11, 13, 14, 16, 18, 19, 21, 23, 24];
            case DARKEST_GREEN:
                return [1, 3, 11, 13, 14, 16, 24];
        }
        // }
    }

    function getPolyominoesLocationsOnBoard() {
        return [1, 3, 4, 6, 8, 9, 11, 13, 14, 16, 18, 19, 21, 23, 24];
    }

    function getNextActionZoneNumber($current) {
        return $current == ACTION_ZONES_COUNT ? 1 : $current + 1;
    }

    /**
     * Get actions zones (animal or patches) that can be reach within an elephant move.
     */
    function getNextActionZones() {
        $zones = [];
        $tokenNeutral = $this->tokens->getTokenInfo('token_neutral');
        $neutralLocation = getPart($tokenNeutral['location'], 2);

        $moveMax = $this->arg_elephantMove();
        $moveCount = 1;
        $nextZone = $this->getNextActionZoneNumber($neutralLocation);
        while (count($zones) < $moveMax) {
            $patches = $this->tokens->getTokensOfTypeInLocation('patch', ACTION_ZONE_PREFIX . $nextZone);
            if ($this->actionStripZones[ACTION_ZONE_PREFIX . $nextZone]['type'] == PATCH && !$patches) {
                //empty patch zones do not count
                self::dump("************no patch at******************", ACTION_ZONE_PREFIX . $nextZone);
            } else {
                $zones[] = ACTION_ZONE_PREFIX . $nextZone;
                $moveCount++;
            }
            $nextZone = $this->getNextActionZoneNumber($nextZone);
        }
        //self::dump("************possible zones******************", $zones);
        return $zones;
    }

    function mtCollectWithField($field, $callback = null) {
        $res = [];
        foreach ($this->token_types as $id => $info) {
            if (array_key_exists($field, $info)) {
                $trig = $info[$field];
                if ((!$callback) || $callback($trig, $id)) {
                    $res[] = $id;
                }
            }
        }
        return $res;
    }

    function mtCollectWithFieldValue($field, $expectedValue, $callback = null) {
        $res = [];
        foreach ($this->token_types as $id => $info) {
            if (array_key_exists($field, $info)) {
                $trig = $info[$field];
                if ($trig === $expectedValue) {
                    if ((!$callback) || $callback($trig, $id)) {
                        $res[] = $id;
                    }
                }
            }
        }
        return $res;
    }

    function stateToRotor($state) {
        $ydir = (int) ($state / 4);
        $zdir = $state % 4;
        $rotateY = $ydir * 180;
        $rotateZ = $zdir * 90;
        $rotor = "${rotateZ}_$rotateY";
        return $rotor;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    //////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in newyorkzoo.action.php)
    */

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */
    function action_place($token_id, $dropTarget, $rotateZ, $rotateY) {
        $this->checkAction('place');

        $player_id = $this->getActivePlayerId();
        //$this->notifyWithName('message', clienttranslate('${player_name} plays pick and place action'));

        $buts = $this->getRulesFor($token_id, 'cost');
        $time = $this->getRulesFor($token_id, 'time');

        $order = $this->getPlayerPosition($player_id);
        $canBuy  = $this->arg_canBuyPatches($order);
        $this->userAssertTrue(self::_("Cannot buy this patch Yet"), array_search($token_id, $canBuy) !== false);
        $pos = $this->tokens->getTokenLocation($token_id);
        //self::dump("**************pos*********************", $pos);
        $this->saction_MoveNeutralToken($pos);
        $this->saction_PlacePatch($order, $token_id, $dropTarget, $rotateZ, $rotateY);

        $this->gamestate->nextState(TRANSITION_PLACE_ANIMAL);
    }

    function saction_PlacePatch($order, $token_id, $dropTarget, $rotateZ, $rotateY) {
        $occupancy = $this->getOccupancyMatrix($order);
        self::dump('*********getOccupancyMatrixBefore**********', $this->matrix->dumpMatrix($occupancy));

        $rotateZ = $rotateZ % 360;
        $rotateY = $rotateY % 360;
        $rotor = "${rotateZ}_$rotateY";
        $occupancy = $this->getOccupancyMatrix($order);
        $moves = $this->arg_possibleMoves($token_id, $order, $rotor, $occupancy)[$rotor];
        $valid = array_search($dropTarget, $moves) !== false;
        $this->userAssertTrue(self::_("Not possible to place patch: illegal move"), $valid);
        $state = $rotateZ / 90 + $rotateY / 180 * 4;
        $message = clienttranslate('${player_name} places patch ${token_div}');
        $this->dbSetTokenLocation(
            $token_id,
            $dropTarget,
            $state,
            $message,
            []
        );

        $occupancy = $this->getOccupancyMatrix($order);
        self::dump('*********getOccupancyMatrix**********', $this->matrix->dumpMatrix($occupancy));
        $unoccup_count = $this->getOccupancyEmpty($occupancy);
        $this->notifyCounterDirect("empties_${order}_counter", $unoccup_count, '');


        $occupancy = $this->getOccupancyMatrixForPiece($order, $token_id);
        self::dump('*********getOccupancyMatrixForPiece**********', $this->matrix->dumpMatrix($occupancy));
        $prefix = "square_${order}_";
        $occupiedByPiece = $this->matrix->remap($occupancy, $prefix, 1);
        self::dump('*********occupiedByPiece**********', $occupiedByPiece);
        $fenceId = $this->dbInsertFence($order, $token_id, $occupiedByPiece);
        self::setGameStateValue(GS_LAST_FENCE_PLACED, $fenceId);
    }

    function dbInsertFence($order, $token_id, $squares) {
        $sql = "INSERT INTO fence (token_key, player_order) VALUES ('$token_id', '$order') ";
        self::DbQuery($sql);

        $sql = "INSERT INTO fence_squares (token_key, square) VALUES ";
        $values = array();
        foreach ($squares as $loc) {
            $values[] = "( '$token_id', '$loc' )";
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);

        return self::getUniqueValueFromDB("SELECT max(id) FROM fence");
    }

    function saction_MoveNeutralToken($pos) {
        $old = $this->tokens->getTokenLocation('token_neutral');
        $old = getPart($old, 2) + 0;
        $new = getPart($pos, 2) + 0;

        $spaces = array_search($pos, $this->getNextActionZones()) + 1;

        $this->dbSetTokenLocation('token_neutral', $pos, null, '${player_name} moves ${token_name} ${spaces_count} spaces away', ['spaces_count' => $spaces]);
        //breeding ?
        foreach ($this->birthZones as $info) {
            $limit = $info['triggerZone'];
            if ($old < $limit && $new >= $limit) {
                $animal = $info['animal'];
                self::setGameStateInitialValue(GS_BREEDING, $this->getAnimalType($animal));
                break;
            } else {
                self::setGameStateInitialValue(GS_BREEDING, 0);
            }
        }

        $this->notifyAllPlayers('eofnet', '', []); // end of moving neutral token
    }

    function action_getAnimals($animalZone) {
        $this->checkAction('getAnimals');

        $player_id = $this->getActivePlayerId();
        //$this->notifyWithName('message', clienttranslate('${player_name} plays pick and place action'));
        $order = $this->getPlayerPosition($player_id);
        $canGo  = $this->arg_canGetAnimals($order);
        $this->userAssertTrue(self::_("Cannot place those animals anywhere"), array_search($animalZone, $canGo) !== false);
        $this->saction_MoveNeutralToken($animalZone);

        self::setGameStateValue(GS_ANIMAL_TO_PLACE, $this->getAnimalType($this->actionStripZones[$animalZone]['animals'][0]));
        self::setGameStateValue(GS_OTHER_ANIMAL_TO_PLACE, $this->getAnimalType($this->actionStripZones[$animalZone]['animals'][1]));
        $this->gamestate->nextState('placeAnimal');
    }

    function action_placeAnimal($from, $to, $animalType, $animalId) {
        $this->checkAction('placeAnimal');
        $this->userAssertTrue(self::_("Have to select a location for this animal"), $animalType !== false && $to !== false);
        $args = $this->arg_placeAnimal();
        $this->userAssertTrue(self::_("Wrong animal to place"), isset($args["animals"][$animalType]));
        $canGo  = $args["animals"][$animalType]["possibleTargets"];
        $this->userAssertTrue(self::_("Animal not allowed here"), array_search($to, $canGo) !== false);

        $state = $this->gamestate->state();

        $animal = $this->tokens->getTokenOfTypeInLocation($animalType, "limbo");
        $this->dbSetTokenLocation($animal["key"], $to, null, '${player_name} places a ${token_name}', []);

        if (!$this->isHouse($to)) {
            $patch = $this->getPatchFromSquare($to);
            $this->dbUpdateFenceType($patch, $animalType);
            $this->dbIncFenceAnimalsAddedNumber($patch);
            if ($this->isFenceFull($patch)) {
                $animalType = $this->emptyFence($patch);
                if ($this->getFreeHouses($this->getMostlyActivePlayerOrder())) {
                    //offers to keep one animal
                    self::setGameStateValue(GS_ANIMAL_TO_KEEP, $this->getAnimalType($animalType));
                    $this->gamestate->nextState(TRANSITION_KEEP_ANIMAL);
                    return;
                } else {
                    $this->gamestate->nextState(TRANSITION_PLACE_ATTRACTION);
                    return;
                }
            }
        }

        if ($state['name'] == 'keepAnimalFromFullFence') {
            self::setGameStateValue(GS_ANIMAL_TO_KEEP, 0);
            $this->gamestate->nextState(TRANSITION_PLACE_ATTRACTION);
        } else {
            //animal zone
            if (self::getGameStateValue(GS_ANIMAL_TO_PLACE) == $this->getAnimalType($animalType)) {
                self::setGameStateValue(GS_ANIMAL_TO_PLACE, 0);
            }
            if (self::getGameStateValue(GS_OTHER_ANIMAL_TO_PLACE) == $this->getAnimalType($animalType)) {
                self::setGameStateValue(GS_OTHER_ANIMAL_TO_PLACE, 0);
            }
            if (self::getGameStateValue(GS_ANIMAL_TO_PLACE) || self::getGameStateValue(GS_OTHER_ANIMAL_TO_PLACE)) {
                $this->gamestate->nextState('placeAnimal');
            } else {
                $this->gamestate->nextState(TRANSITION_NEXT_PLAYER);
            }
        }
    }

    function dbUpdateTable(String $table, String $field, String $newValue, String $pkfield, String $key) {
        $this->DbQuery("UPDATE $table SET $field = '$newValue' WHERE $pkfield = '$key'");
    }

    function dbIncField(String $table, String $field, String $pkfield, String $key) {
        $this->DbQuery("UPDATE $table SET $field = $field+1 WHERE $pkfield = '$key'");
    }

    function dbUpdateFenceType(String $key, String $newValue) {
        $this->dbUpdateTable("fence", "animal_type", $newValue, "token_key", $key);
    }
    function dbIncFenceAnimalsAddedNumber(String $key) {
        $this->dbIncField("fence", "animals_added", "token_key", $key);
    }
    function dbUpdateFenceAnimalsAddedNumber(String $key, Int $newValue) {
        $this->dbUpdateTable("fence", "animals_added", $newValue, "token_key", $key);
    }

    function dbGetFence(String $token_key) {
        return self::getObjectFromDB("SELECT * FROM fence WHERE token_key='$token_key'");
    }

    function action_dismissAnimal() {
        self::setGameStateValue(GS_ANIMAL_TO_PLACE, 0);
        self::setGameStateValue(GS_OTHER_ANIMAL_TO_PLACE, 0);
        $this->gamestate->nextState(TRANSITION_NEXT_PLAYER);
    }

    function action_chooseFences($tokenIds) {
        $this->checkAction('chooseFences');
        $this->userAssertTrue(self::_("Have to select at most 2 fences"), count($tokenIds) <= 0);
        $args = $this->arg_chooseFences();
        foreach ($tokenIds as $token) {
            $this->userAssertTrue(self::_("This fence can't be placed anywhere"), array_search($token, $args) !== false);
        }
        $animalType = self::getGameStateValue(GS_BREEDING);
        foreach ($tokenIds as $token) {
            $token = $this->tokens->getTokenOfTypeInLocation($animalType, "limbo");
            $this->tokens->moveToken($token["key"], $tokenIds); //todo specify where exactly
        }
        self::setGameStateValue(GS_BREEDING, 0);
    }

    function getAnimalType($animalName) {
        $index = array_search($animalName, $this->animals);
        return $index === false ? 0 : $this->animalTypes[$index];
    }
    function getAnimalName($animalType, $constant = false) {

        $index = array_search($animalType, $this->animalTypes);
        $animalName = $index === false ? null : $this->animals[$index];
        /* if ($constant) {
            return strtoupper($animalName);
        }*/
        return $animalName;
    }

    function saction_FinalScoring() {
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $info) {
            $this->dbSetScore($player_id, 0);
            $order = $info['player_no'];

            // empty spaces
            $occupancy = $this->getOccupancyMatrix($order);
            $unoccup_count = $this->getOccupancyEmpty($occupancy);
            $this->dbIncScoreValueAndNotify($player_id, -$unoccup_count, clienttranslate('${player_name} loses ${mod} point(s) for empty spaces'), 'game_empty_slot');

            //tie breaker
            $animalCount = 0;
            foreach ($this->animals as $type) {
                $animalCount += count($this->tokens->getTokensOfTypeInLocation($type, "square_" . $order));
            }
            $animalCount += $this->tokens->countTokensInLocation($type, "house_" . $player_id);
            $this->dbSetAuxScore($player_id, $animalCount);
        }
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////
    function arg_playerTurn() {
        $player_id = $this->getActivePlayerId();
        $order = $this->getPlayerPosition($player_id);
        $res = [];
        $patches = $this->arg_canBuyPatches($order);
        self::dump('*************arg_playerTurn***patches***', $patches);
        $curbuttons = $this->tokens->getTokensInLocation("buttons_$order");
        $buttons = count($curbuttons);
        $canUseAny = false;
        $occupancy = $this->getOccupancyMatrix($order);
        foreach ($patches as $patch) {
            $moves = $this->arg_possibleMoves($patch, $order, null, $occupancy);
            $canPlace = false;
            foreach ($moves as $arr) {
                if (count($arr) > 0) {
                    $canPlace = true;
                    break;
                }
            }
            $res['patches'][$patch]['moves'] = $moves;
            $res['patches'][$patch]['canPlace'] = $canPlace;

            $canUse = $canPlace;
            $res['patches'][$patch]['canUse'] = $canUse;
            $canUseAny = $canUseAny || $canUse;
        }
        // $advance = $this->dbGetAdvance($player_id);
        $advance = 0; //todo
        $res += ['advance' => $advance];
        $res += ['buttons' => $buttons];

        $res['canPatch'] = true; //$canUseAny;
        $res['maxMoves'] = $this->arg_elephantMove();
        $res['canGetAnimals'] = $this->arg_canGetAnimals($order); //$this->hasEmptyHouses(1)||$this->hasFenceAcceptinq();


        return $res;
    }

    function arg_placeAttraction() {
        $res = [];
        $player_id = $this->getActivePlayerId();
        $playerOrder = $this->getPlayerPosition($player_id);
        $patches = array_keys($this->tokens->getTokensInLocation("bonus_market"));
        $patches = $this->getUniqueMasks($patches);
        self::dump('*************arg_placeAttraction***patches***', $patches);
        $canUseAny = false;
        $occupancy = $this->getOccupancyMatrix($playerOrder);
        foreach ($patches as $patch) {
            $moves = $this->arg_possibleMoves($patch, $playerOrder, null, $occupancy);
            $canPlace = false;
            foreach ($moves as $arr) {
                if (count($arr) > 0) {
                    $canPlace = true;
                    break;
                }
            }
            $res['patches'][$patch]['moves'] = $moves;
            $res['patches'][$patch]['canPlace'] = $canPlace;

            $canUse = $canPlace;
            $res['patches'][$patch]['canUse'] = $canUse;
            $canUseAny = $canUseAny || $canUse;
        }

        $res['canPatch'] = true; //$canUseAny;
        return $res;
    }

    function arg_occupancyData($order) {
        $tokens = $this->tokens->getTokensOfTypeInLocation("patch", "square_${order}%");
        //$this->warn(toJson($tokens));
        $occupdata = [];
        foreach ($tokens as $key => $info) {
            $loc = $info['location'];
            $state = $info['state'];
            $y = getPart($loc, 2);
            $x = getPart($loc, 3);
            $rotor2 = $this->stateToRotor($state);
            $mask2 = $this->getRulesFor($key, 'mask');
            $occupdata[] = [$x, $y, $mask2, $rotor2];
        }
        return $occupdata;
    }

    function getOccupancyMatrix($order) {
        $occupdata = null;
        if ($order !== null) {
            $occupdata = $this->arg_occupancyData($order);
        }
        $occupancy = $this->matrix->occupancyMatrix($occupdata);

        return $occupancy;
    }

    function getOccupancyMatrixForPiece($order, $tokenId) {
        $occupdata = null;
        if ($order !== null && $tokenId !== null) {
            $token = $this->tokens->getTokenInfo($tokenId);
            $loc = $token['location'];
            $state = $token['state'];
            $y = getPart($loc, 2);
            $x = getPart($loc, 3);
            $rotor2 = $this->stateToRotor($state);
            $mask2 = $this->getRulesFor($tokenId, 'mask');
            $occupdata[] = [$x, $y, $mask2, $rotor2];
            self::dump('*******************occupdata', $occupdata);
        }
        $occupancy = $this->matrix->occupancyMatrix($occupdata);

        return $occupancy;
    }

    function getOccupancyEmpty($occupancy) {
        $unoccup = $this->matrix->remap($occupancy, '', 0);
        $unoccup_count = count($unoccup);
        $empty = $unoccup_count;
        return $empty;
    }

    function arg_possibleMoves($patch, $order = null, $rotor = null, $occupancy = null) {
        if ($order !== null) {
            $prefix = "square_${order}_";
        } else
            $prefix = '';

        if ($occupancy == null) {
            $occupancy = $this->getOccupancyMatrix($order);
        }
        $mask = $this->getRulesFor($patch, 'mask');
        return $this->matrix->possibleMoves($mask, $prefix, $rotor, $occupancy);
    }

    /** Return patches that are accessible with an elephant move. */
    function arg_canBuyPatches($order) {
        //$patches = ['patch_16', 'patch_1', 'patch_18'];
        $patches = [];
        $nextZones = $this->getNextActionZones();
        foreach ($nextZones as $nz) {
            self::dump("*****************getNextActionZones rs*", $nz);
            $topPatch = $this->tokens->getTokenOnTop($nz, true, 'patch');
            if ($topPatch)
                $patches[] = $topPatch["key"];
        }
        self::dump("*****************arg_canBuyPatches*", $patches);
        return $patches;
    }

    function arg_canGetAnimals($order) {
        $from = [];
        $nextZones = $this->getNextActionZones();
        foreach ($nextZones as $nz) {
            //actionStripZones
            $zoneType = $this->actionStripZones[$nz]["type"];
            if (
                $zoneType == ANIMAL &&
                ($this->arg_possibleTargetsForAnimal($order, $this->actionStripZones[$nz]["animals"][0])
                    || $this->arg_possibleTargetsForAnimal($order, $this->actionStripZones[$nz]["animals"][1]))
            ) {
                $from[] = $nz;
            }
        }
        self::dump("*****************arg_canGetAnimals*", $from);
        return $from;
    }

    function getPlayerHouses($playerOrder) {
        $players = $this->loadPlayersBasicInfos();
        $playerCount = count($players);
        $houseCount = $this->boards[$playerCount][$playerOrder]["houses"];
        $houses = [];
        for ($i = 1; $i <= $houseCount; $i++) {
            $houses[] = "house_" . $playerOrder . "_" . $i;
        }
        return $houses;
    }

    function getFreeHouses($playerOrder) {
        $houseTokens = $this->tokens->getTokensInLocation("house_" . $playerOrder . "%");
        $housesWithToken = $this->getFieldValuesFromArray($houseTokens, "location", true);
        $allHouses = $this->getPlayerHouses($playerOrder);
        $emptyHouses = array_values(array_diff($allHouses, $housesWithToken));
        self::dump("*****************getFreeHouses ", implode(",", $emptyHouses));
        return $emptyHouses;
    }

    function getTokensOfTypeInPatch($anmlType, $patch) {
        return [];
    }


    function getFieldValuesFromArray($arr, $field, $unique = false) {
        $concatenated = array();
        foreach ($arr as $element) {
            if (isset($element[$field])) {
                $concatenated[] = $element[$field];
            }
        }
        return $unique ? array_unique($concatenated) : $concatenated;
    }

    function arg_elephantMove() {
        $players = $this->loadPlayersBasicInfos();
        $players_nbr = count($players);
        switch ($players_nbr) {
            case 2:
            case 4:
                return 4;
            case 3:
            case 5:
                return 3;
        }
    }

    function arg_populateNewFence() {
        $args = [];
        $playerOrder = $this->getMostlyActivePlayerOrder();

        $fenceId = $this->getGameStateValue(GS_LAST_FENCE_PLACED);
        $fenceTokenKey = $this->getFenceTokenKey($fenceId);
        $fence = $this->dbGetFence($fenceTokenKey);

        $animalsInHouses = $this->tokens->getTokensInLocation("house_" . $playerOrder . "%");
        $args["possibleAnimals"] =  array_keys($animalsInHouses);

        //plus animals inside fences that have at least 2 animals
        $onFences = $this->getAnimalsByFenceHavingMinimalAnimalCount($playerOrder, 2);
        //minus 1 animal we cant leave alone
        foreach ($onFences as $f => $animals) {
            array_shift($animals);
            $args["possibleAnimals"] = array_merge($args["possibleAnimals"], array_values($animals));
        }

        if ($fence["animal_type"] !== "none") { //mean we're placing a second animal, so we keep only those of the same type
            //remove animals of the wrong type
            $args["possibleAnimals"] = $this->filterAnimalType($args["possibleAnimals"], $fence["animal_type"]);
        }

        $args["possibleTargets"] =  $this->getFenceSquares($fenceTokenKey);

        self::dump('*******************arg_populateNewFence', $args);
        return $args;
    }

    function getFenceTokenKey($fenceTechId) {
        return self::getUniqueValueFromDB("SELECT token_key FROM fence where id = $fenceTechId");
    }

    function arg_placeAnimal() {
        $player_id = $this->getActivePlayerId();
        $order = $this->getPlayerPosition($player_id);
        $args = [];
        $first = $this->getGameStateValue(GS_ANIMAL_TO_PLACE);
        $second = $this->getGameStateValue(GS_OTHER_ANIMAL_TO_PLACE);
        $args["animalType1"] = "";
        $args["animalType2"] = "";
        if ($first) {
            $anmlType = $this->getAnimalName($first, true);
            $args["animalType1"] = $anmlType;
            $targets = $this->arg_possibleTargetsForAnimal($order, $anmlType);
            $args["animals"][$anmlType]["possibleTargets"] =  $targets;
            $args["animals"][$anmlType]["canPlace"] = count($targets) != 0;
        }
        if ($second) {
            $anmlType = $this->getAnimalName($second, true);
            $args["animalType2"] = $anmlType;
            $targets = $this->arg_possibleTargetsForAnimal($order, $anmlType);
            $args["animals"][$anmlType]["possibleTargets"] =  $targets;
            $args["animals"][$anmlType]["canPlace"] = count($targets) != 0;
        }
        if ($first && $second) {
            $args["canDismiss"] = false;
        } else {
            $args["canDismiss"] = true;
        }

        return $args;
    }

    function arg_possibleTargetsForAnimal($playerOrder, $animal) {
        $freeHouses = $this->getFreeHouses($playerOrder);
        $fencesAcceptingAnml = $this->getSquaresInFencesAccepting($playerOrder, $animal); //todo
        return array_merge($freeHouses, $fencesAcceptingAnml);
    }

    function getSquaresInFencesAccepting($playerOrder, $animal) {
        $sql = "SELECT square FROM fence_squares JOIN fence on fence.token_key = fence_squares.token_key WHERE player_order=$playerOrder AND animal_type in('none', '$animal')";
        $allSquares = self::getObjectListFromDB($sql, true);
        return $this->filterFreeSquares($allSquares);
    }

    function filterFreeSquares($squares) {
        $allSquaresParam = $this->dbArrayParam($squares);
        $sql = "SELECT token_location FROM token WHERE token_location in($allSquaresParam) AND token_key not like 'patch%'";
        $occupied = self::getObjectListFromDB($sql, true);
        return array_diff($squares, $occupied);
    }

    function filterOccupiedSquares($squares) {
        $allSquaresParam = $this->dbArrayParam($squares);
        $sql = "SELECT token_location FROM token WHERE token_location in($allSquaresParam) AND token_key not like 'patch%'";
        $occupied = self::getObjectListFromDB($sql, true);
        //self::dump('***************filterOccupiedSquares****', $occupied);
        return $occupied;
    }
    function getAnimalsByFence($playerOrder) {
        $sql = "SELECT fence.token_key fence_key, token.token_key animal_key FROM token JOIN fence_squares on token.token_location = fence_squares.square JOIN fence on fence.token_key = fence_squares.token_key WHERE token.token_key not like 'patch%' AND token.token_location like 'square%' AND player_order= $playerOrder GROUP BY fence.token_key, token.token_key";
        $animalsByFence =  self::getDoubleKeyCollectionFromDB($sql, true);
        return $animalsByFence;
    }

    function getAnimalsByFenceHavingMinimalAnimalCount($playerOrder, $animalMin) {
        $animalsByFence = $this->getAnimalsByFence($playerOrder);
        $respectingMin = [];

        foreach ($animalsByFence as $fence => $animals) {
            if (count($animals) >= $animalMin) {
                $respectingMin[$fence] = array_keys($animals);
            }
        }
        return $respectingMin;
    }


    function dbArrayParam($arrayp) {
        return '"' . implode($arrayp, '","') . '"';
    }

    function arg_chooseFences() {
        $player_id = $this->getActivePlayerId();
        $playerOrder = $this->getPlayerPosition($player_id);
        $allFences = $this->tokens->getTokensOfTypeInLocation("patch", "square_" . $playerOrder);
        $anmlType = self::getGameStateValue(GS_BREEDING);
        //only keep those with 2 animals of the required type
        $validFences = array_filter($allFences, function ($f) use ($anmlType) {
            return $this->getTokensOfTypeInPatch($anmlType, $f);
        });
        return $validFences; //todo get ids only
    }

    function arg_keep_animal() {
        $args = [];
        $playerOrder = $this->getMostlyActivePlayerOrder();
        $anml = self::getGameStateValue(GS_ANIMAL_TO_KEEP);
        $animalName = $this->getAnimalName($anml);
        $args["animalType1"] = $animalName;
        $args["animals"][$animalName]["possibleTargets"] =  $this->getFreeHouses($playerOrder);
        $args["animals"][$animalName]["canPlace"] =  true;
        $args["canDismiss"] = true;
        return $args;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    function st_gameTurnNextPlayer() {
        $endOfGame = false;
        $players = $this->loadPlayersBasicInfos();
        foreach ($players as $player_id => $info) {
            $order = $info['player_no'];
            $occupdata = $this->arg_occupancyData($order);
            $occupancy = $this->matrix->occupancyMatrix($occupdata);
            $filled = $this->matrix->isFullyFilled($occupancy);
            if (!$endOfGame && $filled) {
                $endOfGame = true;
            }
        }

        if ($endOfGame) {
            $this->saction_FinalScoring();
            $this->gamestate->nextState('last');
            return;
        }
        $this->activateNextPlayerCustom();
        $this->gamestate->nextState('next');
    }

    function st_playerTurn() {
        $args = $this->arg_playerTurn();
        $canPatch = $args['canPatch'];
        //$this->warn("st_playerTurn canPatch='".$canPatch."' pl=$player_id ".toJson($args)."|");
        if (false &&  !$canPatch) {
            $this->notifyWithName('message', clienttranslate('${player_name} cannot buy any patches'));
            $this->sendNotifications(); // have to do it so it does not bundle too much
        }
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn($state, $active_player) {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */

    function upgradeTableDb($from_version) {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
        //        if( $from_version <= 1404301345 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
        //            self::applyDbUpgradeToAllDB( $sql );
        //        }
        //        if( $from_version <= 1405061421 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
        //            self::applyDbUpgradeToAllDB( $sql );
        //        }
        //        // Please add your future database scheme changes here
        //
        //


    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// Debug utils
    //////////
    function fullFence($fenceKey, $forcedAnimalType = null, $leaveOneSpace = false) {
        $squares = $this->getFenceSquares($fenceKey);
        $type = $this->getFenceType($fenceKey);
        if ($type === "none") {
            $type = $forcedAnimalType;
        }
        $minus = 0;
        foreach ($squares as $square) {
            $occupied = $this->tokens->getTokensInLocation($square);
            $occupied = $this->filterAnimals($occupied);
            if (!$occupied) {
                if ($leaveOneSpace && $minus == 0) {
                    $minus = 1;
                } else {
                    $filler = $this->tokens->getTokenOfTypeInLocation($type, "limbo");
                    $this->dbSetTokenLocation($filler["key"], $square);
                }
            }
        }
        if ($forcedAnimalType)
            $this->dbUpdateFenceType($fenceKey, $forcedAnimalType);
    }
}
