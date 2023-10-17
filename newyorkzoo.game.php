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
require_once("modules/constants.inc.php");

if (!defined('OFFSET')) {
    define("OFFSET", 5);
    define("ACTION_ZONES_COUNT", 25);
    define("ACTION_ZONE_PREFIX", "action_zone_");
    define("ACTION_ZONE_ANML_PREFIX", "action_zone_anml_");
    define("ANIMALS_INITIAL_NUMBER", 60);
    define('GS_ANIMAL_TO_PLACE', "animalToPlace");
    define('GS_OTHER_ANIMAL_TO_PLACE', "otherAnimalToPlace");
    define("GS_BREEDING", "breeding");
    define("GS_BREEDING_2_LONG_MOVE", "breeding2LongMove");
    define("GS_BONUS_BREEDING", "bonusBreeding");
    define("GS_ANIMAL_TO_KEEP", "animalToKeep");
    define("GS_LAST_FENCE_PLACED", "lastFencePlaced");
    define("GS_FROM", "from");
    define("GS_TO", "to");
    define("GS_BREED2_TO", "breed2To");
    define("GS_BREED_TRIGGER", "breedTrigger");
    define("GS_RESOLVING_BREEDING", "resolvingBreeding");
    define("GS_PREVIOUS_NEUTRAL_LOCATION", "previousNeutralLocation");
    define("GS_CAN_UNDO_ACQUISITION_MOVE", "canUndoAcquisitionMove");

    //context_log actions
    define("ACTION_GET_ANIMALS", 'getAnimals');
    define("ACTION_GET_ANIMAL", 'getAnimal');
    define("ACTION_PLACE_FENCE", 'placeFence');
    define("ACTION_POPULATE_FENCE", 'populateFence');
    define("ACTION_PLACE_ATTRACTION", 'placeAttraction');
    define("ACTION_KEEP_ANIMAL_FROM_FULL_FENCE", 'actionKeepAnimalFromFullFence');
    define("CHECK_FENCE_FULL", 'fenceFull');
    define("ADD_FROM_HOUSE", 'addFromHouse');
    define("BREEDING", 'breeding');
    define("BONUS_BREEDING", 'bonusBreeding');
    define("ATTRACTION_1x1_PACK_QUANTITY", 5);
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
            FAST_GAME => 100, //2 players only
            SOLO => 101, //1 player only
            SOLO_BOARD => 102, //2 possible boards with 3 or 4 houses
            GS_ANIMAL_TO_PLACE => 10, //animalType 1 from main action
            GS_OTHER_ANIMAL_TO_PLACE => 11,  //animalType 2 from main action
            GS_BREEDING => 12,
            GS_ANIMAL_TO_KEEP => 13, //from full fence
            GS_LAST_FENCE_PLACED => 14,
            GS_FROM => 15,
            GS_TO => 16,
            GS_BREED_TRIGGER => 17, //player who triggered breeding
            GS_BREED2_TO => 18, //destination fence for 2nd breeding
            GS_RESOLVING_BREEDING => 19, //0=no breeding, 1 or 2=order of the breeding being resolved
            GS_BONUS_BREEDING => 20, //boolean
            GS_BREEDING_2_LONG_MOVE => 21, //when fences spaces are empty, a 4 move can cross 2 birth lines
            GS_PREVIOUS_NEUTRAL_LOCATION => 22, //need to store elephant position prior to a move to acquisition zone since it can be canceled
            GS_CAN_UNDO_ACQUISITION_MOVE => 23, //can undo only if no animal has been placed (just meant to prevent misclicks)
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
            //todo call inherited
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
            self::setGameStateInitialValue(GS_BREEDING_2_LONG_MOVE, 0);
            self::setGameStateInitialValue(GS_BREED_TRIGGER, 0);
            self::setGameStateInitialValue(GS_BREED2_TO, 0);
            self::setGameStateInitialValue(GS_RESOLVING_BREEDING, 0);
            self::setGameStateInitialValue(GS_BONUS_BREEDING, 0);
            self::setGameStateInitialValue(GS_CAN_UNDO_ACQUISITION_MOVE, 0);
            self::setGameStateInitialValue(GS_PREVIOUS_NEUTRAL_LOCATION, 0);

            $this->initStats();


            // Activate first player (which is in general a good idea :) )
            $this->activeNextPlayer();
        } catch (Exception $e) {
            // logging does not actually work in game init :(
            $this->error("Fatal error while creating game");
            $this->dump('err', $e);
        }

        $this->debugSetup();

        /************ End of the game initialization *****/
    }

    function getSoloBoard($player_count) {
        if ($player_count == 1) {
            switch (self::getGameStateValue(SOLO_BOARD)) {
                case HOUSE_COUNT_3:
                    return "board_houses_3";
                case HOUSE_COUNT_4:
                    return "board_houses_4";
            }
        }
        return "";
    }

    function getSoloHousesCount() {
        switch (self::getGameStateValue(SOLO_BOARD)) {
            case HOUSE_COUNT_3:
                return 3;
            case HOUSE_COUNT_4:
                return 4;
        }
    }

    function debugSetup() {
        if ($this->getBgaEnvironment() != 'studio') {
            return;
        }
        //$this->dblBreeding(FLAMINGO, 1);
        //$this->dblBreeding(MEERKAT, 1);

        $this->tokens->moveToken("token_neutral", $this->getActionZoneName(16));
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
            $boardConf = $this->isSoloMode() ? $this->boards[count($players)][$this->getSoloHousesCount()] : $this->boards[count($players)][$playerOrder];
            $h = 1;
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
                    $mask = $this->getRulesFor($id, "mask");
                    if ($mask === ":1") {
                        $this->tokens->createTokensPack($id . "_{INDEX}", $dest, min($occ, ATTRACTION_1x1_PACK_QUANTITY), 1, null, $state);
                        if ($occ > ATTRACTION_1x1_PACK_QUANTITY) {
                            $this->tokens->createTokensPack($id . "_{INDEX}", "limbo", $occ - ATTRACTION_1x1_PACK_QUANTITY, 1 + ATTRACTION_1x1_PACK_QUANTITY, null, $state);
                        }
                    } else {
                        $this->tokens->createTokensPack($id . "_{INDEX}", $dest, $occ, 1, null, $state);
                    }
                } else {
                    $this->tokens->createToken($id, $dest, $state);
                }
            }
        }

        //animals
        $this->tokens->createTokensPack("meerkat_{INDEX}", "limbo", ANIMALS_INITIAL_NUMBER);
        $this->tokens->createTokensPack("flamingo_{INDEX}", "limbo", ANIMALS_INITIAL_NUMBER);
        $this->tokens->createTokensPack("kangaroo_{INDEX}", "limbo", ANIMALS_INITIAL_NUMBER);
        $this->tokens->createTokensPack("penguin_{INDEX}", "limbo", ANIMALS_INITIAL_NUMBER);
        $this->tokens->createTokensPack("fox_{INDEX}", "limbo", ANIMALS_INITIAL_NUMBER);
        $this->tokens->createToken("token_neutral", "action_zone_anml_10", 0);

        if($this->isSoloMode()){
            for ($i=0; $i < 5; $i++) { 
                $this->tokens->createToken("solo_token_${i}", "limbo", 0);
            }
        }
    }

    /**
     * 1x1 Attractions are in infinite number. We can’t just create a large bunch at the beginning of the game,
     * because safari won’t handle them correctly if there are too many. So we add them when needed, 5 by 5.
     */
    function addMore1x1AttractionsIfNeeded() {
        $remaining = 0;
        $remaining += count($this->tokens->getTokensOfTypeInLocation("patch_25%", "bonus_market"));
        $remaining += count($this->tokens->getTokensOfTypeInLocation("patch_45%", "bonus_market"));
        $remaining += count($this->tokens->getTokensOfTypeInLocation("patch_54%", "bonus_market"));
        if ($remaining < 2) {
            /*$newTokens = [];
            $idAttraction = 'patch_25';
            $state = $this->getRulesFor($idAttraction, "spaces");
            for ($i = 0; $i < 5; $i++) {
                $newTokens[] = $this->tokens->createTokenAutoInc($idAttraction, "bonus_market", $state);
            }
            $this->dbSyncInfo($newTokens);*/

            $stock = $this->tokens->getTokensOfTypeInLocation("patch_25%", "limbo");
            $stock = array_merge($stock, $this->tokens->getTokensOfTypeInLocation("patch_45%", "limbo"));
            $stock = array_merge($this->tokens->getTokensOfTypeInLocation("patch_54%", "limbo"));
            $toMove = array_keys(array_slice($stock, 0, ATTRACTION_1x1_PACK_QUANTITY));
            //self::dump('*******************toMove', $toMove);
            if ($toMove) {
                $this->tokens->moveTokens($toMove, "bonus_market");
                $this->dbSyncInfo($toMove);
            } else {
                $this->error("No more 1x1 attraction in stock");
            }
        }
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
                $this->tokens->moveToken($patchId, ACTION_ZONE_PREFIX . $loc, $layers[$loc]);
            }
        }
        $players = $this->loadPlayersBasicInfos();
        $players_nbr = count($players);
        if ($this->isSoloMode() || ($players_nbr == 2 && $this->isFastGame())) {
            $locations = $this->getPolyominoesLocationsOnBoard();
            $removed = [];
            foreach ($locations as $loc) {
                $patch = $this->tokens->getTokenOnTop(ACTION_ZONE_PREFIX . $loc, true, "patch");
                $this->tokens->moveToken($patch["key"], "limbo");
                $removed[] = $patch;
            }

            if ($players_nbr == 2 && $this->isFastGame()) {
                //deal evenly the removed patches by color
                $stateP1 = 1;
                $stateP2 = 1;
                foreach ([DARKEST_GREEN, DARK_GREEN] as $color) {
                    $coloredPatches = $this->mtCollectWithFieldValue("color", $color);
                    $removedColoredPatch = array_values(array_filter($removed, fn ($patch) => array_search($patch["key"], $coloredPatches) != false));
                    for ($i = 0; $i < 3; $i++) {
                        $this->tokens->moveToken($removedColoredPatch[$i]["key"], "hand_" . array_keys($players)[0], $stateP1);
                        $stateP1++;
                    }
                    for ($i = 3; $i < 6; $i++) {
                        $this->tokens->moveToken($removedColoredPatch[$i]["key"], "hand_" . array_keys($players)[1], $stateP2);
                        $stateP2++;
                    }
                }
            }
        }
        //place fake filler token at the top left corner
        for ($i = 0; $i < $players_nbr; $i++) {
            $order = $i + 1;
            $this->tokens->moveToken($this->getFillerId($players_nbr, $order), "square_" . $order . "_0_0");
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
        $players = $this->loadPlayersBasicInfos();
        $done = [];
        foreach ($players as $player_id => $info) {
            // empty spaces
            $order = $info['player_no'];
            $occupancy = $this->getOccupancyMatrix($order);
            $unoccup_count = $this->getOccupancyEmpty($occupancy);

            //total
            $fillerId = $this->isSoloMode() ? "patch_1" . count($players) . $this->getSoloHousesCount() : "patch_1" . count($players) . $order;
            $fillerSize = substr_count($this->token_types[$fillerId]["mask"], "1");
            $totalToDo = $this->getGridHeight() * $this->getGridWidth() - $fillerSize;

            $done[$player_id] = ($totalToDo - $unoccup_count) * 100 / $totalToDo;
        }
        return max($done);
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    //////////// 
    function getFillerId($players_nbr, $order) {
        if ($this->isSoloMode()) {
            return "patch_1" . $players_nbr . $this->getSoloHousesCount();
        }
        return "patch_1" . $players_nbr . $order;
    }

    function action_endGame() {
        $this->checkAction('endGame');
        $this->gamestate->nextState('next');
    }

    function hasReachLimit($patch) {
        return intval($this->dbGetFence($patch)["animals_added"]) >= 2;
    }

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
        $gridSquare = $this->replaceAnimalSquareByGridSquare($square);
        return self::getUniqueValueFromDB("SELECT token_key FROM fence_squares WHERE square = '$gridSquare'");
    }

    function getFenceSquares($fenceKey) {
        return self::getObjectListFromDB("SELECT replace(square, 'square_', 'anml_square_') FROM fence_squares WHERE token_key = '$fenceKey'", true);
    }

    function isFenceFull($fenceKey) {
        $squares = $this->getFenceSquares($fenceKey);
        $empty = $this->filterFreeSquares($squares);
        return !$empty;
    }

    function getFullFences($order) {
        $fences = $this->dbGetFencesKeysByPlayer($order);
        $full = [];
        foreach ($fences as $fenceId) {
            if ($this->isFenceFull($fenceId)) {
                $full[] = $fenceId;
            }
        }
        return $full;
    }

    /** Empty fence and return animal type */
    function emptyFence($fenceId) {
        $type = $this->getFenceType($fenceId);
        $this->systemAssertTrue("This fence does NOT exist.", $type);
        $squares = $this->getFenceSquares($fenceId);
        //self::dump('******************getFenceSquares*', $squares);
        $occupied = $this->filterOccupiedSquares($squares);
        $tokens = $this->tokens->getTokensInLocations($occupied);
        $tokens = $this->filterAnimals($tokens);
        $this->dbSetTokensLocation($tokens, "limbo", null, '✅ ${player_name} has a full fence', []);
        $this->dbUpdateFenceType($fenceId, "none");
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
        if ($players_nbr == 1 && $this->getSoloHousesCount() == 3) {
            return array(11, 9); //x,y
        } else if ($players_nbr == 1 && $this->getSoloHousesCount() == 4) {
            return array(14, 9); //x,y
        } else if ($players_nbr == 2) {
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

    function getNextActionZoneNumber(int $current) {
        return $current == ACTION_ZONES_COUNT ? 1 : $current + 1;
    }
    function getPreviousActionZoneNumber(int $current) {
        return $current == 1 ? ACTION_ZONES_COUNT : $current - 1;
    }

    /**
     * Get actions zones (animal or patches) that can be reach within an elephant move.
     */
    function getNextActionZones() {
        $zones = [];
        $moveMax = $this->arg_elephantMove();
        $moveCount = 1;
        $nextZone = $this->getNextActionZone();
        while (count($zones) < $moveMax) {
            if ($this->isFenceActionZoneWithoutFences($nextZone)) {
                //empty patch zones do not count
            } else {
                $zones[] = $nextZone;
                $moveCount++;
            }
            $nextZone = $this->getNextActionZone($nextZone);
        }
        //self::dump("************possible zones******************", $zones);
        return $zones;
    }

    function isFenceActionZoneWithoutFences($nextZone) {
        $patches = $this->tokens->getTokensOfTypeInLocation('patch', $nextZone);
        return $this->actionStripZones[$nextZone]['type'] == PATCH && !$patches;
    }

    function getNextActionZone($from = null) {
        if ($from) {
            $origin = getPart($from, -1);
        } else {
            $origin = $this->getNeutralPositionNumber();
        }
        $nextZone = $this->getNextActionZoneNumber(intval($origin));
        return $this->getActionZoneName($nextZone);
    }

    function getActionZoneName($number) {
        $zoneId = ACTION_ZONE_PREFIX . $number;
        if (array_key_exists($zoneId, $this->actionStripZones)) {
            return $zoneId;
        } else {
            return ACTION_ZONE_ANML_PREFIX . $number;
        }
    }

    function getNeutralPositionNumber() {
        $tokenNeutral = $this->tokens->getTokenInfo('token_neutral');
        $neutralLocation = getPart($tokenNeutral['location'], -1);
        return $neutralLocation;
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

    /**
     * Return the entire object and not only its id.
     */
    function mtCollectAllWithFieldValue($field, $expectedValue, $callback = null) {
        $res = [];
        foreach ($this->token_types as $id => $info) {
            if (array_key_exists($field, $info)) {
                $trig = $info[$field];
                if ($trig === $expectedValue) {
                    if ((!$callback) || $callback($trig, $id)) {
                        $res[] = $info;
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

    /********** OPTIONS */

    public function isFastGame() {
        return $this->getGameStateValue(FAST_GAME) == ACTIVATED;
    }

    public function isSoloMode() {
        return $this->getGameStateValue(SOLO) == ACTIVATED;
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

    function action_placeStartFence($token_id, $dropTarget, $rotateZ, $rotateY) {
        $this->checkAction('placeStartFence');

        $player_id = $this->getMostlyActivePlayerId();
        $order = $this->getMostlyActivePlayerOrder();

        $canBuy  = $this->arg_placeStartFences()[$player_id];
        $this->userAssertTrue(self::_("Cannot choose this fence yet"), array_search($token_id, $canBuy) !== false);
        $this->saction_PlacePatch($order, $token_id, $dropTarget, $rotateZ, $rotateY);

        //stays in the same state to place other fences or desactivate players until they are all finished
        if ($this->tokens->countTokensInLocation("hand_" . $player_id) == 0) {
            $this->gamestate->setPlayerNonMultiactive($player_id, "");
        } else {
            //notify next possible moves
            $fakeStartFenceStateArgs = [];
            $fakeStartFenceStateArgs["args"] = $this->arg_placeStartFences();
            $this->notifyPlayer($player_id, "placeStartFenceArgs", "", $fakeStartFenceStateArgs);
        }
    }

    function action_resetStartFences() {
        $this->checkAction('resetStartFences');
        $playerOrder = $this->getMostlyActivePlayerOrder();
        self::DbQuery("DELETE FROM fence_squares where square like 'square_$playerOrder%'");
        self::DbQuery("DELETE FROM fence where player_order = '$playerOrder'");
        $fences = array_keys($this->tokens->getTokensOfTypeInLocation("patch%", "square_" . $playerOrder . "%"));
        $fillers = $this->mtCollectWithFieldValue("color", "filler");
        $this->dbSetTokensLocation(array_diff($fences, $fillers), "hand_" . $this->getMostlyActivePlayerId(), null, '${player_name} resets the start fences', []);
    }

    function resolveLastFullFenceContext() {
        $context = $this->dbGetLastContextToResolve();
        if ($context["action"] == CHECK_FENCE_FULL) {
            $this->dbResolveContextLog($context["id"]);
            $this->notifyWithName("fenceFull", "", ["fence" => $context["param1"], "resolved" => true, "animal" => $context["param2"]]);
        }
    }

    function action_place($token_id, $dropTarget, $rotateZ, $rotateY) {
        $this->checkAction('place');

        $player_id = $this->getActivePlayerId();
        $order = $this->getMostlyActivePlayerOrder();
        //$this->notifyWithName('message', clienttranslate('${player_name} plays pick and place action'));
        $pos = $this->tokens->getTokenLocation($token_id);

        if ($this->gamestate->state()["name"] === "placeAttraction") {
            $this->userAssertTrue(self::_("Should be a bonus attraction"), $pos === "bonus_market");

            $this->saction_PlacePatch($order, $token_id, $dropTarget, $rotateZ, $rotateY, true);
            self::incStat(1, "game_attractions", $player_id);
            $mask = $this->getRulesFor($token_id, "mask");
            self::incStat(substr_count($mask, "1"), "game_attractions_squares", $player_id);
            $this->addMore1x1AttractionsIfNeeded();

            $this->resolveLastFullFenceContext();
        } else {
            //$order = $this->getPlayerPosition($player_id);
            $this->userAssertTrue(self::_("No animal available to populate a fence"), $this->canPopulateNewFence());

            $canBuy  = $this->arg_canBuyPatches($order);
            $this->userAssertTrue(self::_("Cannot choose this fence yet"), array_search($token_id, $canBuy) !== false);

            $this->saction_MoveNeutralToken($pos);
            $this->dbInsertContextLog(ACTION_POPULATE_FENCE, $token_id);
            $this->dbInsertContextLog(ACTION_PLACE_FENCE, $token_id);
            $this->saction_PlacePatch($order, $token_id, $dropTarget, $rotateZ, $rotateY);
        }
        $this->changeNextStateFromContext();
    }

    function saction_PlacePatch($order, $token_id, $dropTarget, $rotateZ, $rotateY, $isBonusAttraction = false) {
        $occupancy = $this->getOccupancyMatrix($order);
        //self::dump('*********getOccupancyMatrixBefore**********', $this->matrix->dumpMatrix($occupancy));

        $rotateZ = $rotateZ % 360;
        $rotateY = $rotateY % 360;
        $rotor = "${rotateZ}_$rotateY";
        $occupancy = $this->getOccupancyMatrix($order);
        $moves = $this->arg_possibleMoves($token_id, $order, $rotor, $occupancy)[$rotor];
        $valid = array_search($dropTarget, $moves) !== false;
        $this->userAssertTrue(self::_("Not possible to place fence: illegal move"), $valid);
        $state = $rotateZ / 90 + $rotateY / 180 * 4;
        $message = clienttranslate('${player_name} places fence ${token_div}');
        $this->dbSetTokenLocation(
            $token_id,
            $dropTarget,
            $state,
            $message,
            []
        );

        $occupancy = $this->getOccupancyMatrix($order);
        //self::dump('*********getOccupancyMatrix**********', $this->matrix->dumpMatrix($occupancy));
        $unoccup_count = $this->getOccupancyEmpty($occupancy);
        $this->notifyCounterDirect("empties_${order}_counter", $unoccup_count, '');


        $occupancy = $this->getOccupancyMatrixForPiece($order, $token_id);
        //self::dump('*********getOccupancyMatrixForPiece**********', $this->matrix->dumpMatrix($occupancy));
        $prefix = "square_${order}_";
        $occupiedByPiece = $this->matrix->remap($occupancy, $prefix, 1);
        //self::dump('*********occupiedByPiece**********', $occupiedByPiece);
        $fenceId = $this->dbInsertFence($order, $token_id, $occupiedByPiece, $isBonusAttraction);
        self::setGameStateValue(GS_LAST_FENCE_PLACED, $fenceId);
    }

    function dbInsertFence($order, $token_id, $squares, $isBonusAttraction) {
        $sql = "INSERT INTO fence (token_key, player_order) VALUES ('$token_id', '$order') ";
        self::DbQuery($sql);

        $sql = "INSERT INTO fence_squares (token_key, square, bonus) VALUES ";
        $values = array();
        $bonus = intval($isBonusAttraction);
        foreach ($squares as $loc) {
            $values[] = "( '$token_id', '$loc' , '$bonus' )";
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);

        return self::getUniqueValueFromDB("SELECT max(id) FROM fence");
    }

    function action_undoElephantMoveToAnimals() {
        $this->dbSetTokenLocation('token_neutral', $this->getActionZoneName(self::getGameStateValue(GS_PREVIOUS_NEUTRAL_LOCATION)), null, '${player_name} cancels the elephant move', []);
        self::setGameStateValue(GS_ANIMAL_TO_PLACE, 0);
        self::setGameStateValue(GS_OTHER_ANIMAL_TO_PLACE, 0);
        $this->resolveLastContextIfAction(ACTION_GET_ANIMAL);
        $this->resolveLastContextIfAction(ACTION_GET_ANIMAL);
        self::setGameStateValue(GS_BREEDING, 0);
        self::setGameStateValue(GS_BREEDING_2_LONG_MOVE, 0);
        $this->gamestate->nextState(TRANSITION_UNDO_ELEPHANT_MOVE_TO_ANIMALS);
        $this->notifyAllPlayers('eofnet', '', []); // end of moving neutral token
    }

    function saction_MoveNeutralToken($pos) {
        $old = $this->tokens->getTokenLocation('token_neutral');
        $old = getPart($old, -1) + 0;
        $new = getPart($pos, -1) + 0;

        $spaces = array_search($pos, $this->getNextActionZones()) + 1;

        $this->dbSetTokenLocation('token_neutral', $pos, null, '${player_name} moves ${token_name} ${spaces_count} spaces away', ['spaces_count' => $spaces]);
        $this->checkIfBreedingLineCrossed($old, $new, $spaces);

        $this->notifyAllPlayers('eofnet', '', []); // end of moving neutral token
    }

    /**
     * Returns crossed action_zones during a move, excluding start position, including end position.
     */
    function getCrossedActionZones($oldPosition, $spaces) {
        $moves = [$this->getNextActionZoneNumber($oldPosition)];
        for ($i = 0; $i < $spaces - 1; $i++) {
            $moves[] = $this->getNextActionZoneNumber($moves[$i]);
        }
        //self::dump('*******************moves', $moves);
        return $moves;
    }

    function countCrossedZones($oldPosition, $newPosition) {
        $moves = 0;
        $pos = $oldPosition;
        while ($pos != $newPosition) {
            $pos = $this->getNextActionZoneNumber($pos);
            $moves++;
        }
        return $moves;
    }

    function checkIfBreedingLineCrossed($oldPosition, $newPosition, $spaces) {
        self::setGameStateValue(GS_BREEDING, 0);
        self::setGameStateValue(GS_BREEDING_2_LONG_MOVE, 0);

        $moves = $this->getCrossedActionZones($oldPosition, $this->countCrossedZones($oldPosition, $newPosition));
        $crossed = false;
        $i = 0;
        foreach ($this->birthZones as $info) {
            $limit = $info['triggerZone'];
            $animal = $info['animal'];
            //self::dump('*******************limit', $limit);
            //self::dump('*******************animal', $animal);
            //self::dump('*******************array_search($limit, $moves)', array_search($limit, $moves));
            if (array_search($limit, $moves) !== false) {
                $crossed = true;
                if ($i == 0) {
                    self::setGameStateValue(GS_BREEDING, $this->getAnimalType($animal));
                    self::dump('*******************breeding 1', $animal);
                } else {
                    self::dump('*******************breeding 2', $animal);
                    self::setGameStateValue(GS_BREEDING_2_LONG_MOVE, $this->getAnimalType($animal));
                }
                $i++;
            }
        }
        return $crossed;
    }

    function action_getAnimals($animalZone) {
        $this->checkAction(ACTION_GET_ANIMALS);

        $player_id = $this->getActivePlayerId();
        //$this->notifyWithName('message', clienttranslate('${player_name} plays pick and place action'));
        $order = $this->getPlayerPosition($player_id);
        $canGo  = $this->arg_canGetAnimals($order);
        $this->userAssertTrue(self::_("Cannot place those animals anywhere"), array_search($animalZone, $canGo) !== false);

        $animal1 = $this->actionStripZones[$animalZone]['animals'][0];
        $animal2 = $this->actionStripZones[$animalZone]['animals'][1];
        self::setGameStateValue(GS_ANIMAL_TO_PLACE, $this->getAnimalType($animal1));
        self::setGameStateValue(GS_OTHER_ANIMAL_TO_PLACE, $this->getAnimalType($animal2));
        $this->insertGetAnimalsContextLog($animal1, $animal2);
        self::setGameStateValue(GS_CAN_UNDO_ACQUISITION_MOVE, 1);
        self::setGameStateValue(GS_PREVIOUS_NEUTRAL_LOCATION, getPart($this->tokens->getTokenLocation('token_neutral'), -1, true));

        $this->saction_MoveNeutralToken($animalZone);

        $this->gamestate->nextState('placeAnimal');
    }

    function insertGetAnimalsContextLog($animal1, $animal2) {
        $this->dbInsertContextLog(ACTION_GET_ANIMAL, $animal1);
        $this->dbInsertContextLog(ACTION_GET_ANIMAL, $animal2);
    }

    /**
     * Check parameters before placing an animal.
     */
    function action_placeAnimal($from, $to, $animalType, $animalId) {
        $this->checkAction('placeAnimal');
        $state = $this->gamestate->state();
        switch ($state['name']) {
            case 'populateNewFence':
                $this->userAssertTrue(self::_("Have to select an animal and a location for this animal"), $from !== false && $to !== false);
                $args = $this->arg_populateNewFence();
                $canGo  = $args["possibleTargets"];
                $this->userAssertTrue(self::_("Animal must go in the new fence"), array_search($to, $canGo) !== false);
                //todo check good from
                $animalId = $from;
                $animalType = getPart($animalId, 0);
                break;
            default:
                //animal strip zone
                $this->userAssertTrue(self::_("Have to select a location for this animal"), $animalType !== false && $to !== false);
                $args = $this->arg_placeAnimal();
                $isExpectedAnimal = isset($args["animals"][$animalType]);
                $isChoosableAnimal = isset($args["choosableAnimals"][$animalType]);
                $this->userAssertTrue(self::_("Wrong animal to place"), $isExpectedAnimal || $isChoosableAnimal);
                if ($isExpectedAnimal) {
                    $canGo  = $args["animals"][$animalType]["possibleTargets"];
                    $this->userAssertTrue(self::_("Animal not allowed here"), array_search($to, $canGo) !== false);
                }
                if ($isChoosableAnimal) {
                    $canGo  = $args["choosableAnimals"][$animalType]["possibleTargets"];
                    $this->userAssertTrue(self::_("Animal not allowed here"), array_search($to, $canGo) !== false);
                }
                $animalId = $this->tokens->getTokenOfTypeInLocation($animalType, "limbo")["key"];
                self::setGameStateValue(GS_CAN_UNDO_ACQUISITION_MOVE, 0);
                break;
        }
        //self::dump('*******************animalId', $animalId, $state['name']);
        $this->saction_placeAnimal($from, $to, $animalType, $animalId);
    }

    function action_keepAnimalFromFullFence() {
        $this->checkAction('keepAnimalFromFullFence');
        $animalType = $this->getAnimalName(self::getGameStateValue(GS_ANIMAL_TO_KEEP));
        $freeHouses = $this->getFreeHouses($this->getMostlyActivePlayerOrder());
        $animalId = $this->tokens->getTokenOfTypeInLocation($animalType, "limbo")["key"];
        $this->saction_placeAnimal(null, array_pop($freeHouses), $animalType, $animalId);
    }

    function replaceGridSquareByAnimalSquare($squareId) {
        return str_replace("square_", "anml_square_", $squareId);
    }

    function replaceAnimalSquareByGridSquare($squareId) {
        return str_replace("anml_square_", "square_", $squareId);
    }

    function formatPlaceName($placeName) {
        $parts = explode('_', $placeName);
        $len = count($parts);
        if ($len == 5 && str_starts_with($placeName, "anml_square"))
            return strval(intval(getpart($placeName, 3, true)) + 1) . ":" . strval(intval(getpart($placeName, 4)) + 1);
        return $placeName;
    }

    /**
     * Does place the animal, when all parameters have been checked.
     */
    function saction_placeAnimal($from, $to, $animalType, $animalId) {

        $state = $this->gamestate->state();
        $newLocation = "";
        if (str_starts_with($to, "house")) {
            $newLocation = clienttranslate('into a house');
        } else if (str_starts_with($to, "anml_square")) {
            $newLocation = clienttranslate('into a fence');
        }
        $this->dbSetTokenLocation($animalId, $to, null, '${player_name} places a ${token_name} ${newLocation}', ["newLocation" => $newLocation]); //todo i18
        $patch = $this->getPatchFromSquare($to);
        //self::dump('*******************state name', $state['name']);

        switch ($state['name']) {

            case 'keepAnimalFromFullFence':
                self::setGameStateValue(GS_ANIMAL_TO_KEEP, 0);
                self::incStat(1, "game_animals_kept_from_full_fence", $this->getMostlyActivePlayerId());
                $this->resolveLastContextIfAction(ACTION_KEEP_ANIMAL_FROM_FULL_FENCE);
                break;
            case 'chooseFence':
                //only bonus breeding passes here
                self::incStat(1, "game_animals_bonus_breed", $this->getMostlyActivePlayerId());
                break;
            case 'placeAnimalFromHouse':
                $this->resolveLastContextIfAction(ADD_FROM_HOUSE);
                self::incStat(1, "game_animals_added_from_house", $this->getMostlyActivePlayerId());
                break;
            default:
                //animal zone
                $anmlTypeConst = $this->getAnimalType($animalType);
                if (self::getGameStateValue(GS_OTHER_ANIMAL_TO_PLACE) && self::getGameStateValue(GS_ANIMAL_TO_PLACE) != $anmlTypeConst && self::getGameStateValue(GS_OTHER_ANIMAL_TO_PLACE) != $anmlTypeConst) {
                    //choosen type not in the expected ones, so we skip the second animal, the player give up on him by choosing another one
                    $this->resolveLastContextIfAction(ACTION_GET_ANIMAL);
                } else {
                    if (self::getGameStateValue(GS_ANIMAL_TO_PLACE) == $this->getAnimalType($animalType)) {
                        self::setGameStateValue(GS_ANIMAL_TO_PLACE, 0);
                    }
                    if (self::getGameStateValue(GS_OTHER_ANIMAL_TO_PLACE) == $this->getAnimalType($animalType)) {
                        self::setGameStateValue(GS_OTHER_ANIMAL_TO_PLACE, 0);
                    }
                }
                $this->resolveLastContextIfAction(ACTION_GET_ANIMAL);
        }
        if ($state['name'] === 'placeAnimalFromHouse' && $this->getGameStateValue(GS_BREED2_TO)) {
            $this->dbIncFenceAnimalsAddedNumber($patch);
            if (self::getGameStateValue(GS_RESOLVING_BREEDING) == 2) {
                self::setGameStateValue(GS_RESOLVING_BREEDING, 0);
                self::setGameStateValue(GS_BREED2_TO, 0);
            } else {
                self::setGameStateValue(GS_RESOLVING_BREEDING, 2);
            }
        }

        if (!$this->isHouse($to)) {
            //into a fence
            $this->dbUpdateFenceType($patch, $animalType);
            $this->dbIncFenceAnimalsAddedNumber($patch);
            if ($this->isFenceFull($patch)) {
                $this->notifyWithName("fenceFull", "", ["fence" => $patch, "resolved" => false, "animal" => $animalType]);
                $this->dbInsertContextLog(CHECK_FENCE_FULL, $patch, $animalType);
                $this->giveExtraTime($this->getMostlyActivePlayerId());
            } else {
                if ($state['name'] !== 'populateNewFence' && $this->getHousesWithAnimalType($this->getMostlyActivePlayerOrder(), $animalType) && !$this->hasReachLimit($patch)) {
                    self::setGameStateValue(GS_FROM, $this->getAnimalType($animalType));
                    self::setGameStateValue(GS_TO, getPart($this->dbGetFence($patch)["token_key"], 1));
                    $this->dbInsertContextLog(ADD_FROM_HOUSE, $patch);
                }
            }
        }
        $this->changeNextStateFromContext();
    }
    function resolveLastContextIfAction($action) {
        $context = $this->dbGetLastContextToResolve();
        if ($context && $context["action"] == $action) {
            $this->dbResolveContextLog($context["id"]);
            //self::dump('*******************Context just resolved', $context);
        } else {
            //self::dump('*******************Last context not as expected', $action);
        }
    }

    function changeNextStateFromContext() {
        $nextState = "";
        $situation = $this->dbGetLastContextToResolve();
        //self::dump('******************situation*', $situation);
        if (!$situation) {
            $nextState = TRANSITION_NEXT_PLAYER;
        } else {
            switch ($situation["action"]) {
                case ACTION_GET_ANIMALS: //main action
                case ACTION_GET_ANIMAL:
                    $nextState = TRANSITION_PLACE_ANIMAL;
                    break;
                case ACTION_PLACE_FENCE: //main action
                    $nextState = TRANSITION_POPULATE_FENCE;
                    $this->dbResolveContextLog($situation["id"]);
                    break;
                case ACTION_POPULATE_FENCE:
                    $patch = $situation["param1"];
                    $squares = $this->getFenceSquares($patch);
                    $occupied = $this->filterOccupiedSquares($squares);
                    if (count($occupied) == 2) {
                        $this->dbResolveContextLog($situation["id"]);
                        $nextState = TRANSITION_NEXT_PLAYER;
                    } else {
                        $args = $this->arg_populateNewFence();
                        $hasAnimals  = $args["possibleAnimals"];
                        if ($hasAnimals) {
                            //stay in this state to place an other animal if possible
                            $nextState = TRANSITION_PLACE_ANIMAL;
                        } else {
                            $this->dbResolveContextLog($situation["id"]);
                            $nextState = TRANSITION_NEXT_PLAYER;
                        }
                    }
                    break;
                case CHECK_FENCE_FULL:
                    $patch = $situation["param1"];
                    $nextState = $this->fenceFullActions($patch);
                    break;
                case ADD_FROM_HOUSE:
                    $nextState = TRANSITION_PLACE_FROM_HOUSE;
                    break;
                case ACTION_KEEP_ANIMAL_FROM_FULL_FENCE:
                    $nextState = TRANSITION_PLACE_ATTRACTION;
                    break;
                case BREEDING:
                case BONUS_BREEDING:
                    if ($this->isBonusBreeding()) {
                        $nextState = TRANSITION_NEXT_BONUS_BREEDER;
                    } else {
                        $nextState = TRANSITION_NEXT_BREEDER;
                    }
                    break;
                default:
                    # code...
                    break;
            }
        }
        if ($nextState == TRANSITION_NEXT_PLAYER) {
            if (!$this->isGameOver() && $this->isBreedingNeeded()) {
                //before moving to the next player, we have to make breeding
                $nextState = TRANSITION_NEXT_BREEDER;
            }
        }
        //self::dump('******************nextState*', $nextState);
        $this->gamestate->nextState($nextState);
    }

    function fenceFullActions($fence): string {
        $nextState = null;
        $animalType = $this->emptyFence($fence);
        $resolvedContext = $this->dbGetLastResolvedContext();
        $alreadyKept = $resolvedContext && $resolvedContext["action"] == ACTION_KEEP_ANIMAL_FROM_FULL_FENCE
            && $resolvedContext["resolved"] && $resolvedContext["player"] == $this->getMostlyActivePlayerId()
            && $resolvedContext["param1"] == $fence;
        if (!$alreadyKept && $this->getFreeHouses($this->getMostlyActivePlayerOrder())) {
            //offers to keep one animal
            self::setGameStateValue(GS_ANIMAL_TO_KEEP, $this->getAnimalType($animalType));
            $this->dbInsertContextLog(ACTION_KEEP_ANIMAL_FROM_FULL_FENCE, $fence);
            $nextState = TRANSITION_KEEP_ANIMAL;
        } else {
            $nextState = TRANSITION_PLACE_ATTRACTION;
        }
        return $nextState;
    }

    function isBreedingPlanned() {
        return self::getGameStateValue(GS_BREEDING) || self::getGameStateValue(GS_BREEDING_2_LONG_MOVE);
    }

    function isBreedingNeeded() {
        if (!$this->isBreedingPlanned()) return false;

        //it’s planned and someone can place one animal
        $normalBreeding  = $this->isAnyBreedingNeeded(self::getGameStateValue(GS_BREEDING));
        $longMoveBreeding  = $this->isAnyBreedingNeeded(self::getGameStateValue(GS_BREEDING_2_LONG_MOVE));
        //self::dump('*******************normalBreeding', $normalBreeding);
        //self::dump('*******************longMoveBreeding', $longMoveBreeding);
        $needed = $normalBreeding || $longMoveBreeding;

        //if necessary, slides the long move breeding value into normal breeding to follow the same path of resolving actions
        if (!self::getGameStateValue(GS_BREEDING) && self::getGameStateValue(GS_BREEDING_2_LONG_MOVE)) {
            //self::dump('*******************slide', self::getGameStateValue(GS_BREEDING_2_LONG_MOVE));
            self::setGameStateValue(GS_BREEDING, self::getGameStateValue(GS_BREEDING_2_LONG_MOVE));
            self::setGameStateValue(GS_BREEDING_2_LONG_MOVE, 0);
        }
        //self::dump('*******************self::getGameStateValue(GS_BREEDING)', self::getGameStateValue(GS_BREEDING));
        //self::dump('*******************self::getGameStateValue(GS_BREEDING_2_LONG_MOVE)', self::getGameStateValue(GS_BREEDING_2_LONG_MOVE));

        //notifies breeding, whether it’s going to be resolved or not
        $animalType = $this->getAnimalName(self::getGameStateValue(GS_BREEDING));
        $players = $this->getPlayingPlayersInOrder($this->getMostlyActivePlayerId());
        $notBreeding = [];
        foreach ($players as $playerId =>  $player) {
            $squaresByFence = $this->getFreeSquaresAvailableForBreeding($this->getPlayerPosition($playerId), $animalType);
            $nb = count(array_keys($squaresByFence));
            if ($nb == 0) {
                $notBreeding[] = $player;
            }
        }
        self::notifyAllPlayers("breedingTime", clienttranslate('Breeding time for ${animal}'), array(
            'animal' => $animalType, //replaced by format_recursive
            'animalType' => $animalType, //stays as is
            'cantBreed' => array_map(fn ($p) => $p["player_id"], $notBreeding),
            'bonus' => false,
        ));


        if ($needed) {
            foreach ($notBreeding as $player) {
                $this->notifyAllPlayers("msg", clienttranslate('${player_name} has no fence where to breed ${animals}'), array(
                    'animals' => $animalType,
                    "player_name" => $player["player_name"],
                ));
            }
            $this->prepareBreeding(self::getGameStateValue(GS_BREEDING),  $this->getMostlyActivePlayerId());
        } else {
            $this->notifyAllPlayers("msg", clienttranslate('No one has any fence where to breed ${animals}'), array('animals' => $animalType));
        }
        return $needed;
    }

    function isAnyBreedingNeeded($animalToBreed) {

        //self::dump('******************animalToBreed value*', $animalToBreed);
        $needed = $animalToBreed != 0;
        $currentPlayerId = $this->getMostlyActivePlayerId();
        if ($needed) {
            $animalType = $this->getAnimalName($animalToBreed);
            $needed = false;
            $players = $this->getPlayingPlayersInOrder($currentPlayerId);
            foreach ($players as $playerId =>  $player) {
                $squaresByFence = $this->getFreeSquaresAvailableForBreeding($this->getPlayerPosition($playerId), $animalType);
                $nb = count(array_keys($squaresByFence));
                $needed = $needed || $nb > 0;
            }
            self::dump("isAnyBreedingNeeded", compact('animalToBreed', 'animalType', 'needed'));
        } else {
            self::dump("isAnyBreedingNeedednot", compact('animalToBreed', 'needed'));
        }
        return $needed;
    }

    function prepareBreeding($animalToBreed, $triggeringPlayerId) {
        $animalType = $this->getAnimalName($animalToBreed);
        $players = $this->getPlayingPlayersInOrder($triggeringPlayerId);

        foreach ($players as $playerId =>  $player) {
            $squaresByFence = $this->getFreeSquaresAvailableForBreeding($this->getPlayerPosition($playerId), $animalType);
            $nb = count(array_keys($squaresByFence));
            $this->dbUpdatePlayer($playerId, "player_breeding_remaining", $nb);
            if ($nb == 0) {
                $notBreeding[] = $player;
            }
        }

        self::setGameStateValue(GS_BREED_TRIGGER, $triggeringPlayerId);
        self::setGameStateValue(GS_RESOLVING_BREEDING, 1); //todo check 1 or 2
        $this->dbInsertContextLog(BREEDING, $animalType);
        $this->dbResetAllFenceAnimalsAddedNumber(0);
    }

    function action_placeAnimalFromHouse() {
        $this->checkAction('placeAnimalFromHouse');
        $playerOrder = $this->getMostlyActivePlayerOrder();
        $from = self::getGameStateValue(GS_FROM); //animal type
        $animalType = $this->getAnimalName($from);
        $animal = $this->tokens->getTokenOfTypeInLocation($animalType, "house_" . $playerOrder . "%");
        $to = "";
        //self::dump('*******************GS_TO ', self::getGameStateValue(GS_TO));
        //self::dump('*******************GS_RESOLVING_BREEDING ', self::getGameStateValue(GS_RESOLVING_BREEDING));
        // self::dump('*******************GS_BREED2_TO ', self::getGameStateValue(GS_BREED2_TO));
        if (self::getGameStateValue(GS_TO) && (self::getGameStateValue(GS_RESOLVING_BREEDING) == 1 || self::getGameStateValue(GS_BONUS_BREEDING) == 1)) {
            $to = "patch_" . self::getGameStateValue(GS_TO); //fence key number
            //self::dump('*******************action_placeAnimalFromHouse 1 ', $to);
        } else if (self::getGameStateValue(GS_RESOLVING_BREEDING) == 2) {
            $to = "patch_" . self::getGameStateValue(GS_BREED2_TO); //fence key number
            //self::dump('*******************action_placeAnimalFromHouse 2 ', $to);
        } else {
            $to = "patch_" . self::getGameStateValue(GS_TO); //fence key number
        }
        $squares = $this->getFenceSquares($to);
        $squares = $this->filterFreeSquares($squares);
        $toSquare = array_pop($squares);
        //self::dump('*******************action_placeAnimalFromHouse frorm ', $from);
        //self::dump('*******************action_placeAnimalFromHouse animal ', $animal);
        //self::dump('*******************action_placeAnimalFromHouse to ', $to);
        $this->saction_placeAnimal(null, $toSquare, $animalType, $animal["key"]);
    }

    function dbUpdateTable(String $table, String $field, String $newValue, String $pkfield, String $key) {
        $sql = "UPDATE $table SET $field = '$newValue'";
        if ($pkfield && $key) {
            $sql .= " WHERE $pkfield = '$key'";
        }
        $this->DbQuery($sql);
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
    function dbResetAllFenceAnimalsAddedNumber(Int $newValue) {
        $this->dbUpdateTable("fence", "animals_added", $newValue, "", "");
    }

    function dbIncFenceAnimalsAddedByBreedingNumber(String $key) {
        $this->dbIncField("fence", "animals_added_by_breeding", "token_key", $key);
    }
    function dbUpdateFenceAnimalsAddedByBreedingNumber(String $key, Int $newValue) {
        $this->dbUpdateTable("fence", "animals_added_by_breeding", $newValue, "token_key", $key);
    }
    function dbResetAllFenceAnimalsAddedByBreedingNumber(Int $newValue) {
        $this->dbUpdateTable("fence", "animals_added_by_breeding", $newValue, "", "");
    }

    function dbGetFence(String $token_key) {
        return self::getObjectFromDB("SELECT * FROM fence WHERE token_key='$token_key'");
    }

    function dbGetFencesKeysByPlayer(Int $order) {
        return self::getObjectListFromDB("SELECT token_key FROM fence WHERE player_order='$order'", true);
    }
    function action_dismissAttraction() {
        $this->checkAction('dismissAttraction');
        $this->notifyWithName('message', clienttranslate('${player_name} does not place a bonus attraction'));
        $this->resolveLastFullFenceContext();
        $this->changeNextStateFromContext();
    }

    function action_dismissAnimal() {
        $this->checkAction('dismiss');
        $state = $this->gamestate->state();

        switch ($state['name']) {
            case 'placeAnimalFromHouse':
                switch (self::getGameStateValue(GS_RESOLVING_BREEDING)) {
                    case 0:
                        self::setGameStateValue(GS_FROM, 0);
                        self::setGameStateValue(GS_TO, 0);
                        break;
                    case 1:
                        self::setGameStateValue(GS_TO, 0);
                        break;
                    case 2:
                        self::setGameStateValue(GS_FROM, 0);
                        self::setGameStateValue(GS_BREED2_TO, 0);
                        break;
                }
                $this->resolveLastContextIfAction(ADD_FROM_HOUSE);
                $this->changeNextStateFromContext();
                break;
            case "populateNewFence":
                $this->resolveLastContextIfAction(ACTION_POPULATE_FENCE);
                $this->changeNextStateFromContext();
                break;
            case "keepAnimalFromFullFence":
                $this->resolveLastContextIfAction(ACTION_KEEP_ANIMAL_FROM_FULL_FENCE);
                $this->changeNextStateFromContext();
                break;
            default:
                //animal action zone
                self::setGameStateValue(GS_ANIMAL_TO_PLACE, 0);
                self::setGameStateValue(GS_OTHER_ANIMAL_TO_PLACE, 0);
                $this->resolveLastContextIfAction(ACTION_GET_ANIMAL);
                $this->changeNextStateFromContext();
                break;
        }
    }

    function checkChooseFencesPossible($squaresIds, $argChooseFences) {
        $this->userAssertTrue(self::_("You have to select at most 2 fences"), count($squaresIds) <= 2);

        $foundFences = [];
        foreach ($squaresIds as $i => $square) {
            $keyFound = false;
            foreach ($argChooseFences["squares"] as $fenceKey => $squares) {
                if ($keyFound === false) {
                    $keyFound = array_search($square, $squares);
                    if ($keyFound !== false) {
                        $foundFences[] = $fenceKey;
                    }
                }
            }
            if ($keyFound === false) {
                $this->userAssertTrue(self::_("An animal can only be bred in a fence where there is two parents of the required specie."), $keyFound);
            }
        }
        if (count($foundFences) == 2) {
            $this->userAssertTrue(self::_("The two possible breedings must happen in different fences."), $foundFences[0] !== $foundFences[1]);
        }
    }

    function action_chooseFences($squaresIds) {
        $this->checkAction('chooseFences');
        $args = $this->arg_chooseFences();
        $squaresCount = count($squaresIds);
        if ($args["bonusBreeding"]) {
            $this->action_chooseFencesForBonusBreeding($squaresIds, $args);
        } else {
            $animalType = $this->getAnimalName(self::getGameStateValue(GS_BREEDING));
            if ($squaresCount) {
                $this->checkChooseFencesPossible($squaresIds, $args);

                foreach ($squaresIds as $i => $squareKey) {
                    $token = $this->tokens->getTokenOfTypeInLocation($animalType, "limbo");
                    $this->dbSetTokenLocation($token["key"], $squareKey, null, '', []);
                    self::incStat(1, "game_animals_breed", $this->getMostlyActivePlayerId());

                    $patch = $this->getPatchFromSquare($squareKey);
                    $this->dbIncFenceAnimalsAddedNumber($patch);

                    //we need to offer to place from house for each fence before checking if anyone is full
                    //so we can't just call saction_placeAnimal
                    if (!$this->isFenceFull($patch) && !$this->hasReachLimit($patch)) {
                        $housesWithAskedAnml = $this->getHousesWithAnimalType($this->getMostlyActivePlayerOrder(), $animalType);
                        if ($housesWithAskedAnml && $i == 0) {
                            self::setGameStateValue(GS_FROM, $this->getAnimalType($animalType));
                            self::setGameStateValue(GS_TO, getPart($this->dbGetFence($patch)["token_key"], 1));
                            $this->dbInsertContextLog(ADD_FROM_HOUSE, $patch);
                        }
                        if (count($housesWithAskedAnml) > 1 && $i == 1) {
                            self::setGameStateValue(GS_BREED2_TO, getPart($this->dbGetFence($patch)["token_key"], 1));
                            $this->dbInsertContextLog(ADD_FROM_HOUSE, $patch);
                        }
                    }
                    if ($this->isFenceFull($patch)) {
                        $this->notifyWithName("fenceFull", "", ["fence" => $patch, "resolved" => false, "animal" => $animalType]);
                        $this->dbInsertContextLog(CHECK_FENCE_FULL, $patch);
                    }
                }
            }

            self::notifyAllPlayers("msg", clienttranslate('🍼 ${player_name} breeds ${number} ${animals}(s)'), array(
                'player_name' => self::getActivePlayerName(),
                'number' => $squaresCount,
                'animals' => $animalType
            ));

            $playerId = $this->getMostlyActivePlayerId();
            $this->dbUpdatePlayer($playerId, "player_breeding_remaining", 0);
            $this->dbUpdatePlayer($playerId, "player_has_bred", $squaresCount > 0);

            $this->changeNextStateFromContext();
        }
    }
    function action_chooseFencesForBonusBreeding($squaresIds, $args) {
        $squaresCount = count($squaresIds);
        if ($squaresCount) {

            $this->userAssertTrue(self::_("Have to select exactly 1 fence"), $squaresCount == 1);
            $this->checkSquaresInSquaresByFence($squaresIds, $args["squares"], _("Bonus breeding must happen in fences where there was no normal breeding"));

            $squareKey = array_pop($squaresIds);
            $patch = $this->getPatchFromSquare($squareKey);
            $animalType = $this->getFenceType($patch);
            //$token = $this->tokens->getTokenOfTypeInLocation($animalType, "limbo");
            //$this->dbSetTokenLocation($token["key"], $squareKey, null, '', []);
            //$this->dbIncFenceAnimalsAddedNumber($patch);

            self::notifyAllPlayers("msg", clienttranslate('🍼 ${player_name} breeds ${number} ${animals}(s) with the bonus breeding'), array(
                'player_name' => self::getActivePlayerName(),
                'number' => $squaresCount,
                'animals' => $animalType
            ));

            $playerId = $this->getMostlyActivePlayerId();
            $this->dbUpdatePlayer($playerId, "player_has_bonus_bred", 1);
            $animalId = $this->tokens->getTokenOfTypeInLocation($animalType, "limbo")["key"];
            $this->saction_placeAnimal(null, $squareKey, $animalType, $animalId);
        } else {
            self::notifyWithName("msg", clienttranslate('${player_name} does not use the bonus breeding'), []);
            $this->dbUpdatePlayer($this->getMostlyActivePlayerId(), "player_has_bonus_bred", 1);
            $this->gamestate->nextState(TRANSITION_NEXT_BONUS_BREEDER);
        }
    }
    function checkSquaresInSquaresByFence($squaresIds, $squaresByFence, $errorMsg) {
        foreach ($squaresIds as $i => $square) {
            $keyFound = false;
            foreach ($squaresByFence as $fenceKey => $squares) {
                if ($keyFound === false) {
                    $keyFound = array_search($square, $squares);
                }
            }
            if ($keyFound === false) {
                $this->userAssertTrue($errorMsg, $keyFound);
            }
        }
    }

    function getAnimalType($animalName) {
        $index = array_search($animalName, $this->animals);
        return $index === false ? 0 : $this->animalTypes[$index];
    }
    function getAnimalName($animalType) {

        $index = array_search($animalType, $this->animalTypes);
        $animalName = $index === false ? null : $this->animals[$index];
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
            self::setStat($unoccup_count, "game_empty_squares", $player_id);

            //tie breaker
            $animalCount = 0;
            foreach ($this->animals as $type) {
                $animalCount += count($this->tokens->getTokensOfTypeInLocation($type, "anml_square_" . $order . "%"));
            }
            $animalCount += $this->tokens->countTokensInLocation("house_" . $order . "%");
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
        //self::dump('*************arg_playerTurn***patches***', $patches);
        $canUseAny = false;
        $canPopulate = $this->canPopulateNewFence();
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

            $canUse = $canPlace && $canPopulate;
            $res['patches'][$patch]['canUse'] = $canUse;
            $canUseAny = $canUseAny || $canUse;
        }

        $res['canPatch'] = $canUseAny && $canPopulate;
        $res['maxMoves'] = $this->arg_elephantMove();
        $res['canGetAnimals'] = $this->arg_canGetAnimals($order);


        return $res;
    }

    function canPopulateNewFence() {
        $order = $this->getMostlyActivePlayerOrder();
        return !empty($this->getAnimalsByFenceHavingMinimalAnimalCount($order, 2)) || count($this->getFreeHouses($order)) != count($this->getPlayerHouses($order));
    }

    function arg_possibleMovesByPatch($patches, $player_id, $bonuses = false) {
        $res = [];
        $playerOrder = $this->getPlayerPosition($player_id);
        if ($bonuses)
            $patches = $this->getUniqueMasks($patches);
        //self::dump('*************arg_placeAttraction***patches***', $patches);
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
            //$key = $bonuses ? $this->getRulesFor($patch, "mask") : $patch;
            $key = $patch;
            $res['patches'][$key]['moves'] = $moves;
            $res['patches'][$key]['canPlace'] = $canPlace;
            $canUse = $canPlace;
            $res['patches'][$key]['canUse'] = $canUse;
            $canUseAny = $canUseAny || $canUse;
        }

        $res['canPatch'] = true; //$canUseAny;

        if ($bonuses)
            foreach ($res['patches'] as $key => &$info) {
                $info['equivalentPatches'] = $this->getEquivalentAttractions($key);
            }
        return $res;
    }

    function getEquivalentAttractions($patchId): array {
        $mask = $this->getRulesFor($patchId, "mask");
        $available = array_keys($this->tokens->getTokensOfTypeInLocation("patch_%", "bonus_market"));
        $equivalent = array_filter($available, fn ($p) => $this->getRulesFor($p, "mask") == $mask);
        $equivalent = array_diff($equivalent, [$patchId]);
        return $equivalent;
    }

    function arg_placeAttraction() {
        $res = $this->arg_possibleMovesByPatch(array_keys($this->tokens->getTokensInLocation("bonus_market")), $this->getActivePlayerId(), true);
        return $res;
    }

    function arg_placeStartFences() {
        $res = [];
        $players = $this->loadPlayersBasicInfos();
        foreach ($players as $player_id => $player_info) {
            $res[$player_id] = $this->arg_possibleMovesByPatch(
                array_keys($this->tokens->getTokensInLocation("hand_" . $player_id)),
                $player_id
            );
        }
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
            //self::dump('*******************occupdata', $occupdata);
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
        $patches = [];
        $nextZones = $this->getNextActionZones();
        foreach ($nextZones as $nz) {
            // self::dump("*****************getNextActionZones rs*", $nz);
            $topPatch = $this->tokens->getTokenOnTop($nz, true, 'patch');
            if ($topPatch)
                $patches[] = $topPatch["key"];
        }
        //self::dump("*****************arg_canBuyPatches*", $patches);
        return $patches;
    }

    function arg_canGetAnimals($order) {
        $from = [];
        $nextZones = $this->getNextActionZones();
        foreach ($nextZones as $nz) {
            //actionStripZones
            $zoneType = $this->actionStripZones[$nz]["type"];
            if ($zoneType == ANIMAL) {
                $animal1 = $this->actionStripZones[$nz]["animals"][0];
                $animal2 = $this->actionStripZones[$nz]["animals"][1];
                if (
                    $this->arg_possibleTargetsForAnimal($order, $animal1)
                    || $this->arg_possibleTargetsForAnimal($order,  $animal2)
                    || $this->arg_dismissToChooseAnimalType([$animal1, $animal2], $order) //choosable animals
                ) {
                    $from[] = $nz;
                }
            }
        }
        //self::dump("*****************arg_canGetAnimals*", $from);
        return $from;
    }

    function getPlayerHouses($playerOrder) {
        $players = $this->loadPlayersBasicInfos();
        $playerCount = count($players);
        $houseCount = $this->isSoloMode() ?  $this->boards[$playerCount][$this->getSoloHousesCount()]["houses"] : $this->boards[$playerCount][$playerOrder]["houses"];
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
        //self::dump("*****************getFreeHouses ", implode(",", $emptyHouses));
        return $emptyHouses;
    }

    function getHousesWithAnimalType($playerOrder, $animalType) {
        $houseTokens = $this->tokens->getTokensOfTypeInLocation($animalType, "house_" . $playerOrder . "%");
        $names = $this->getFieldValuesFromArray($houseTokens, "location", false);
        //self::dump("*****************getHousesWithAnimalType ", implode(",", $names));
        return $names;
    }

    function args_getHousesByAnimal($playerOrder) {
        $houseTokens = $this->tokens->getTokensInLocation("house_" . $playerOrder . "%");
        $houseTokens = $this->filterAnimals($houseTokens);
        $houses = [];
        foreach ($houseTokens as $key => $token) {
            $animalType = getpart($key, 0);
            if (!isset($houses[$animalType])) {
                $houses[$animalType] = [];
            }
            $houses[$animalType][] = $token["location"];
        }
        //self::dump("*****************args_getHousesByAnimal ", $houses);
        return $houses;
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
            case 1:
                return 0;
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
        $firstAnimal = true;

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
            $firstAnimal = false;
            $args["possibleAnimals"] = $this->filterAnimalType($args["possibleAnimals"], $fence["animal_type"]);
        }

        $args["possibleTargets"] =  $this->getFenceSquares($fenceTokenKey);
        $args["canDismiss"] = !$firstAnimal;
        //self::dump('*******************arg_populateNewFence', $args);
        return $args;
    }

    function getFenceTokenKey($fenceTechId) {
        return self::getUniqueValueFromDB("SELECT token_key FROM fence where id = $fenceTechId");
    }

    /**
     * Place animals after taking them.
     */
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
            $args["choosableAnimals"] = $this->arg_dismissToChooseAnimalType([$this->getAnimalName($first, true), $this->getAnimalName($second, true)], $order);
        } else {
            $args["canDismiss"] = true;
        }
        $args["canUndoElephantMoveToAnimals"] = boolval(self::getGameStateValue(GS_CAN_UNDO_ACQUISITION_MOVE));
        $args["houses"] = $this->args_getHousesByAnimal($order);

        return $args;
    }

    /**
     * Returns an array of choosable animals and their possible targets.
     */
    function arg_dismissToChooseAnimalType($toExclude, $playerOrder) {
        $args = [];
        foreach ($this->animals as $anmlType) {
            if (array_search($anmlType, $toExclude) === false) {
                $targets = $this->arg_possibleTargetsForAnimal($playerOrder, $anmlType);
                $canPlace = count($targets) != 0;
                if ($canPlace) {
                    $args[$anmlType]["possibleTargets"] =  $targets;
                    $args[$anmlType]["canPlace"] = $canPlace;
                }
            }
        }
        //self::dump('******************arg_dismissToChooseAnimalType*', $args);
        return $args;
    }

    function arg_possibleTargetsForAnimal($playerOrder, $animal) {
        $freeHouses = $this->getFreeHouses($playerOrder);
        $fencesAcceptingAnml = $this->getSquaresInFencesAccepting($playerOrder, $animal); //remove attraction squares
        return array_merge($freeHouses, $fencesAcceptingAnml);
    }

    function getSquaresInFencesAccepting($playerOrder, $animal) {
        $sql = "SELECT square FROM fence_squares JOIN fence on fence.token_key = fence_squares.token_key WHERE player_order=$playerOrder AND bonus=false AND animal_type in('none', '$animal')";
        $allSquares = self::getObjectListFromDB($sql, true);
        $freeSquares = $this->filterFreeSquares($allSquares);
        return array_map(fn ($sqre) => $this->replaceGridSquareByAnimalSquare($sqre), $freeSquares);
    }

    function filterFreeSquares($squares) {
        $allSquaresParam = $this->dbArrayParam($squares);
        $sql = "SELECT token_location FROM token WHERE token_location in($allSquaresParam) AND token_key not like 'patch%'";
        $occupied = self::getObjectListFromDB($sql, true);
        return array_values(array_diff($squares, $occupied));
    }

    function filterOccupiedSquares($squares) {
        $allSquaresParam = $this->dbArrayParam($squares);
        $sql = "SELECT token_location FROM token WHERE token_location in($allSquaresParam) AND token_key not like 'patch%'";
        $occupied = self::getObjectListFromDB($sql, true);
        //self::dump('***************filterOccupiedSquares****', $occupied);
        return $occupied;
    }
    function getAnimalsByFence($playerOrder) {
        $sql = "SELECT fence.token_key fence_key, token.token_key animal_key FROM token JOIN fence_squares on token.token_location = replace(fence_squares.square, 'square_', 'anml_square_') JOIN fence on fence.token_key = fence_squares.token_key WHERE token.token_key not like 'patch%' AND token.token_location like 'anml_square%' AND player_order= $playerOrder GROUP BY fence.token_key, token.token_key";
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

    function getFencesInfo($playerOrder) {
        $sql = "SELECT token_key, id, animal_type, animals_added FROM fence WHERE player_order= $playerOrder";
        $fences = self::getCollectionFromDB($sql, false);
        $animalsByFence = $this->getAnimalsByFence($playerOrder);
        foreach ($fences as $fenceKey => &$fence) {
            if (isset($animalsByFence[$fenceKey])) {
                $fence["animals"] = $animalsByFence[$fenceKey];
            } else {
                $fence["animals"] = [];
            }
            $fence["squares"] = $this->getFenceSquares($fenceKey);
            $fence["freeSquares"] = $this->filterFreeSquares($fence["squares"]);
        }
        //self::dump('*******************getFencesInfo', $fences);
        return $fences;
    }

    function getPossibleBreedingsCount($playerOrder, $animalType) {
        return min(count($this->getFencesAvailableForBreeding($playerOrder, $animalType)), 2);
    }

    function getFencesAvailableForBreeding($playerOrder, $animalType) {
        $fences = $this->getFencesInfo($playerOrder);
        return array_filter($fences, function ($f) use ($animalType) {
            return $f["animal_type"] == $animalType && count($f["animals"]) >= 2 && count($f["freeSquares"]) >= 0;
        });
    }
    function getFreeSquaresAvailableForBreeding($playerOrder, $animalType) {
        $fences = $this->getFencesAvailableForBreeding($playerOrder, $animalType);
        //self::dump('*******************fences', $fences);
        $freeSquares = [];
        foreach ($fences as $fenceKey => $fence) {
            $freeSquares[$fenceKey] = $fence["freeSquares"];
        }
        //self::dump('*******************freeSquares', $freeSquares);
        return $freeSquares;
    }

    function getFreeSquaresAvailableForBonusBreeding($playerOrder) {
        $fences = $this->getFencesInfo($playerOrder);
        $fences = array_filter($fences, function ($f) {
            return $f["animals_added"] == 0 && count($f["freeSquares"]) >= 0 && count($f["animals"]) >= 2;
        });
        $freeSquares = [];
        foreach ($fences as $fenceKey => $fence) {
            $freeSquares[$fenceKey] = $fence["freeSquares"];
        }
        //self::dump('*******************getFreeSquaresAvailableForBonusBreeding', $freeSquares);
        return $freeSquares;
    }


    function dbArrayParam($arrayp) {
        return '"' . implode($arrayp, '","') . '"';
    }

    function isBonusBreeding() {
        return intval(self::getGameStateValue(GS_BONUS_BREEDING)) != 0;
    }

    function arg_chooseFences() {
        $args = [];
        $playerOrder = $this->getMostlyActivePlayerOrder();
        $anmlType = $this->getAnimalName(self::getGameStateValue(GS_BREEDING));
        $args["bonusBreeding"] = $this->isBonusBreeding();
        if ($args["bonusBreeding"]) {
            $args["squares"] = $this->getFreeSquaresAvailableForBonusBreeding($playerOrder);
            $args["possibleBreedings"] = 1;
        } else {
            $args["squares"] = $this->getFreeSquaresAvailableForBreeding($playerOrder, $anmlType);
            $args["possibleBreedings"] = $this->getPossibleBreedingsCount($playerOrder, $anmlType);
        }
        return $args;
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

    function st_gameTurnStart() {
        if ($this->isFastGame()) {
            //needs time to place all the start fences
            $players = $this->loadPlayersBasicInfos();
            foreach ($players as $player_id => $info) {
                $this->giveExtraTime($player_id);
            }
            $this->gamestate->setAllPlayersMultiactive();
            $this->gamestate->nextState(TRANSITION_PLACE_START_FENCES);
        } else {
            $this->gamestate->nextState(TRANSITION_NEXT_PLAYER);
        }
    }

    function isGameOver() {
        $endOfGame = false;
        $players = $this->loadPlayersBasicInfos();
        foreach ($players as $player_id => $info) {
            if (!$endOfGame) {
                $order = $info['player_no'];
                $occupdata = $this->arg_occupancyData($order);
                $occupancy = $this->matrix->occupancyMatrix($occupdata);
                $filled = $this->matrix->isFullyFilled($occupancy);
                $endOfGame = $filled;
            }
        }
        return $endOfGame;
    }

    function st_gameTurnNextPlayer() {
        if ($this->isGameOver()) {
            $this->saction_FinalScoring();
            $this->gamestate->nextState('last');
            return;
        }
        $this->activateNextPlayerCustom();
        $this->notifyWithName('message', clienttranslate('&#10148; Start of ${player_name}\'s turn'));
        $this->gamestate->nextState('next');
    }

    function st_playerTurn() {
        $this->dbResetAllFenceAnimalsAddedNumber(0);
        self::setGameStateValue(GS_BREEDING, 0);
        self::setGameStateValue(GS_BONUS_BREEDING, 0);
        //$this->dbEmptyTable("context_log");
        if($this->isSoloMode()){
            $used= $this->tokens->getTokensOfTypeInLocation("solo_token%", "used");
            if(count($used)==5){
                $ids = $this->getFieldValuesFromArray($used, "key");
                $this->dbSetTokensLocation($ids,"limbo");
            }
        }
    }

    function st_gameTurnNextBreeder() {
        $triggerPlayer = self::getGameStateValue(GS_BREED_TRIGGER);
        $players = array_values($this->getPlayingPlayersInOrder($triggerPlayer));
        //self::dump('*******************players', $players);
        $playerCount = count($players);
        $i = 0;
        while ($i < $playerCount) {
            $p = $players[$i];
            $playerId = $p["player_id"];
            $breedingRemaining = $this->dbGetPlayerFieldValue(intval($playerId), "player_breeding_remaining");
            //self::dump('*******************playerId', $playerId);
            //self::dump('*******************breedingRemaining', $breedingRemaining);
            //self::dump('*******************triggerPlayer', $triggerPlayer);
            if (intval($breedingRemaining) > 0) {
                if (intval($playerId) != $triggerPlayer) {
                    //self::dump('*******************activate', $playerId);
                    $this->gamestate->changeActivePlayer($playerId);
                }
                $this->gamestate->nextState(TRANSITION_CHOOSE_FENCE);
                return;
            }
            $i++;
        }
        self::setGameStateValue(GS_BREEDING, 0);
        $this->resolveLastContextIfAction(BREEDING);
        self::setGameStateValue(GS_RESOLVING_BREEDING, 0);

        //check if there is another breeding waiting
        if ($this->isBreedingNeeded()) {
            self::setGameStateValue(GS_RESOLVING_BREEDING, 2);
            $this->gamestate->nextState(TRANSITION_CHOOSE_FENCE);
        } else {
            //everyone has bred, see if bonus breeding needed
            if ($playerCount === 1 ||$playerCount === 2 || $playerCount === 3) {
                self::setGameStateValue(GS_BONUS_BREEDING, 1);
                $this->dbInsertContextLog(BONUS_BREEDING);
                self::notifyAllPlayers("breedingTime", clienttranslate('Bonus breeding time'), ["bonus" => true]);
                $this->gamestate->nextState(TRANSITION_NEXT_BONUS_BREEDER);
            } else {
                $this->dbUpdatePlayers("player_has_bred", 0);
                $this->gamestate->changeActivePlayer($triggerPlayer);
                $this->gamestate->nextState(TRANSITION_NEXT_PLAYER);
            }
        }
    }

    function st_gameNextBonusBreeder() {
        $triggerPlayer = self::getGameStateValue(GS_BREED_TRIGGER);
        $this->gamestate->changeActivePlayer($triggerPlayer);
        $players = array_values($this->getPlayingPlayersInOrder($triggerPlayer));
        $i = 0;
        //self::dump('*****************getPlayersInOrder**', $players);
        while ($i < count($players)) {
            $p = $players[$i];
            $playerId = $p["player_id"];
            $playerOrder = $p["player_no"];
            $hasBred = $this->dbGetPlayerFieldValue(intval($playerId), "player_has_bred");
            $hasBonusBred = $this->dbGetPlayerFieldValue(intval($playerId), "player_has_bonus_bred");
            //self::dump('*****************hasBred**', $hasBred);
            //self::dump('****************hasBonusBred**', $hasBonusBred);
            if (!$hasBonusBred) {
                //self::dump('*****************NOT hasBonusBred**', $p);
                if ($hasBred && !empty($this->getFreeSquaresAvailableForBonusBreeding($playerOrder))) {
                    //self::dump('*****************CAN BonusBred**', $playerId);
                    if (intval($playerId) != $triggerPlayer) {
                        $this->gamestate->changeActivePlayer($playerId);
                        //self::dump('*****************changeActivePlayer**', $playerId);
                    }
                    $this->gamestate->nextState(TRANSITION_CHOOSE_FENCE);
                    return;
                } else {
                    $this->dbUpdatePlayer($playerId, "player_has_bonus_bred", 1);
                    self::notifyAllPlayers("msg", clienttranslate('${player_name} can not use the bonus breeding'), array(
                        'player_name' => self::getPlayerName($playerId),
                    ));
                }
            }
            $i++;
        }
        //everyone has bonus bred
        //self::setGameStateValue(GS_BREEDING, 0);
        self::setGameStateValue(GS_BONUS_BREEDING, 0);
        $this->resolveLastContextIfAction(BONUS_BREEDING);
        //self::setGameStateValue(GS_RESOLVING_BREEDING, 0);
        $this->dbUpdatePlayers("player_has_bred", 0);
        $this->dbUpdatePlayers("player_has_bonus_bred", 0);
        $this->gamestate->changeActivePlayer($triggerPlayer);
        $this->gamestate->nextState(TRANSITION_NEXT_PLAYER);
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
                    $this->gamestate->jumpToState(STATE_GAME_TURN_NEXT_PLAYER);
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
    function fullFence($fenceKey, $forcedAnimalType = null, $leaveSpaces = 0) {
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
                if ($leaveSpaces && $minus < $leaveSpaces) {
                    $minus++;
                } else {
                    $filler = $this->tokens->getTokenOfTypeInLocation($type, "limbo");
                    if ($filler) {
                        $this->dbSetTokenLocation($filler["key"], $square);
                    }
                    // else{
                    //     throw new feException("No more animals in limbo of type: " . $type);       
                    // }
                }
            }
        }
        if ($forcedAnimalType)
            $this->dbUpdateFenceType($fenceKey, $forcedAnimalType);
    }

    /** Setup a double breeding situation */
    function dblBreeding($animalType = "meerkat", $playerOrder = null) {
        if (!$playerOrder)
            $playerOrder = $this->getMostlyActivePlayerOrder();

        $turn = $this->arg_playerTurn();
        $fences = $this->arg_canBuyPatches($playerOrder);
        $patch1 = array_shift($fences);
        $patch2 = array_shift($fences);

        $patchOneMoves = $turn['patches'][$patch1]['moves']["0_0"];
        $p1Move = array_shift($patchOneMoves);
        $this->saction_PlacePatch($playerOrder, $patch1, $p1Move, 0, 0);

        $turn = $this->arg_playerTurn();
        $patch2Moves = $turn['patches'][$patch2]['moves']["0_0"];
        $p2Move = array_shift($patch2Moves);
        $this->saction_PlacePatch($playerOrder, $patch2, $p2Move, 0, 0);

        $this->fullFence($patch1, $animalType, 1);
        $this->fullFence($patch2, $animalType, 1);

        $animalId = $this->tokens->getTokenOfTypeInLocation($animalType, "limbo")["key"];
        $this->dbSetTokenLocation($animalId, "house_" . $playerOrder . "_" . 3, null, '${player_name} places a ${token_name}', []);
        $this->saction_MoveNeutralToken("action_zone_anml_17");
    }

    function fullFences() {
        $playerOrder = $this->getMostlyActivePlayerOrder();
        $fences = $this->getFencesInfo($playerOrder);
        foreach ($fences as $fenceKey => $fence) {
            $this->fullFence($fenceKey, MEERKAT, 1);
        }
    }

    /** Places several patches on the active player board. */
    function placeFences($count = 10) {
        $playerOrder = $this->getMostlyActivePlayerOrder();

        for ($i = 0; $i < $count; $i++) {
            //self::dump('************************************************************************i', $i);
            $turn = $this->arg_playerTurn();
            $fences = $this->arg_canBuyPatches($playerOrder);
            //self::dump('*******************arg_canBuyPatches', $fences);
            $placed = false;
            $patch1 = array_shift($fences);
            //self::dump('*******************$patch1', $patch1);
            while (!$placed && $patch1 != null) {

                $patchOneMoves = $turn['patches'][$patch1]['moves']["0_0"];
                if (empty($patchOneMoves)) {
                    $patchOneMoves = $turn['patches'][$patch1]['moves']["90_0"];
                }
                if (empty($patchOneMoves)) {
                    $patchOneMoves = $turn['patches'][$patch1]['moves']["180_0"];
                }
                if (empty($patchOneMoves)) {
                    $patchOneMoves = $turn['patches'][$patch1]['moves']["270_0"];
                }
                //self::dump('*******************patchOneMoves', $patchOneMoves);
                if (!empty($patchOneMoves)) {
                    $p1Move = array_shift($patchOneMoves);
                    //self::dump('*******************patch1 placed', $patch1);
                    //self::dump('*******************to', $p1Move);
                    $this->saction_PlacePatch($playerOrder, $patch1, $p1Move, 0, 0);
                    $placed = true;
                }
                //$patch1 = array_shift($fences);
                //self::dump('*******************patch1 end', $patch1);
            }
        }
    }
}
