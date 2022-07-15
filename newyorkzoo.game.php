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
}

class NewYorkZoo extends EuroGame
{
    function __construct()
    {
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
        ));

        $this->tokens = new Tokens();
        $this->matrix = new PwMatrix($this);
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "newyorkzoo";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {
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

    function initTables()
    {
        // patches 
        foreach ($this->token_types as $id => &$info) {
            if (startsWith($id, 'patch')) {
                $occ = $this->getRulesFor($id, "occurrences");
                if ($occ > 1) {
                    $this->tokens->createTokensPack($id . "_{INDEX}", "limbo", $occ, 1);
                } else {
                    $this->tokens->createToken($id, "limbo", 1);
                }
            }
        }

        //animals
        $this->tokens->createTokensPack("meerkat_{INDEX}", "limbo", 28);
        $this->tokens->createTokensPack("flamingo_{INDEX}", "limbo", 26);
        $this->tokens->createTokensPack("kangaroo_{INDEX}", "limbo", 24);
        $this->tokens->createTokensPack("penguin_{INDEX}", "limbo", 24);
        $this->tokens->createTokensPack("fox_{INDEX}", "limbo", 24);
        $this->tokens->createToken("token_neutral", "action_zone_10", 0); //elephant


        //creates houses and gets animals from the board
        $players = $this->loadPlayersBasicInfos();
        //self::dump("**********************",$players);
        $i = 1;
        foreach ($players as $player_id => $player) {
            $this->tokens->createTokensPack($player_id . "_house_{INDEX}",  "house_" . $player_id, 3);
            $playerOrder = $player["player_no"];
            $boardConf = $this->boards[count($players)][$playerOrder];
            $h = 1;
            foreach ($boardConf["animals"] as $animal) {
                $this->tokens->moveToken($animal . '_' . $i, "house_" . $player_id . "_" . $h, 0);
                $i++;
                $h++;
            }
        }

        /*
        shuffle($patches);
        $i = 0;
        $this->tokens->moveToken('patch_1', "market", $i);
        $i++;
        $this->tokens->moveToken('token_neutral', "market", $i);
        $i++;
        foreach ($patches as $patch) {
            if ($patch !== "patch_1") {
                $this->tokens->moveToken($patch, "market", $i);
                $i++;
            }
        }*/
        $this->setupPatchesOnBoard();
    }

    function setupPatchesOnBoard()
    {
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
                $this->tokens->moveToken($patchId, "action_zone_" . $loc, $layers[$loc]);
            }
        }
    }
    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
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
            $color = $player_info['player_color'];
            $occupancy = $this->getOccupancyMatrix($color);
            $unoccup_count = $this->getOccupancyEmpty($occupancy);
            $this->setCounter($result['counters'], "empties_${color}_counter", $unoccup_count);
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
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */
    function getGridSize()
    {
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

    function getGridWidth()
    {
        $boardSize = $this->getGridSize();
        return $boardSize[0];
    }

    function getGridHeight()
    {
        $boardSize = $this->getGridSize();
        return $boardSize[1];
    }

    function getMatrixStart()
    {
        return 0 - OFFSET;
    }

    function getMatrixWidthEnd()
    {
        return $this->getGridWidth() +  OFFSET;
    }
    function getMatrixHeightEnd()
    {
        return $this->getGridHeight();
        +OFFSET;
    }

    function getPolyominoesCount($color)
    {
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
    function getPolyominoesLocationOnBoard($color)
    {
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

    function mtCollectWithField($field, $callback = null)
    {
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

    function mtCollectWithFieldValue($field, $expectedValue, $callback = null)
    {
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

    function stateToRotor($state)
    {
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
    function action_place($token_id, $dropTarget, $rotateZ, $rotateY)
    {
        $this->checkAction('place');

        $player_id = $this->getActivePlayerId();
        //$this->notifyWithName('message', clienttranslate('${player_name} plays pick and place action'));

        $buts = $this->getRulesFor($token_id, 'cost');
        $time = $this->getRulesFor($token_id, 'time');

        $color = $this->getPlayerColor($player_id);
        $canBuy  = $this->arg_canBuyPatches($color);
        $this->userAssertTrue(self::_("Cannot buy this patch Yet"), array_search($token_id, $canBuy) !== false);
        $pos = $this->tokens->getTokenState($token_id);
        $this->saction_PlacePatch($color, $token_id, $dropTarget, $rotateZ, $rotateY);

        $this->gamestate->nextState('next');
    }

    function saction_PlacePatch($color, $token_id, $dropTarget, $rotateZ, $rotateY)
    {
        $rotateZ = $rotateZ % 360;
        $rotateY = $rotateY % 360;
        $rotor = "${rotateZ}_$rotateY";
        $occupancy = $this->getOccupancyMatrix($color);
        $moves = $this->arg_possibleMoves($token_id, $color, $rotor, $occupancy)[$rotor];
        $valid = array_search($dropTarget, $moves) !== false;
        $this->userAssertTrue(self::_("Not possible to place patch: illegal move"), $valid);
        $state = $rotateZ / 90 + $rotateY / 180 * 4;
        $buts = $this->getRulesFor($token_id, 'cost');
        $time = $this->getRulesFor($token_id, 'time');
        $message = clienttranslate('${player_name} places patch paying ${but} buttons and ${time} time ${token_div}');
        $this->dbSetTokenLocation(
            $token_id,
            $dropTarget,
            $state,
            $message,
            ['but' => $buts, 'time' => $time]
        );
        $occupancy = $this->getOccupancyMatrix($color);
        $unoccup_count = $this->getOccupancyEmpty($occupancy);
        $this->notifyCounterDirect("empties_${color}_counter", $unoccup_count, '');
    }

    function saction_FinalScoring()
    {
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $info) {
            $this->dbSetScore($player_id, 0);
            $color = $info['player_color'];

            // empty spaces
            $occupancy = $this->getOccupancyMatrix($color);
            $unoccup_count = $this->getOccupancyEmpty($occupancy);
            $this->dbIncScoreValueAndNotify($player_id, -$unoccup_count * 2, clienttranslate('${player_name} loses ${mod} point(s) for empty spaces'), 'game_empty_slot');
        }
        $firstOnSpot = $this->dbGetFirstPlayerId();
        $this->dbSetAuxScore($firstOnSpot, 1);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////
    function arg_playerTurn()
    {
        $player_id = $this->getActivePlayerId();
        $color = $this->getPlayerColor($player_id);
        $res = [];
        $patches = $this->arg_canBuyPatches($color);
        $curbuttons = $this->tokens->getTokensInLocation("buttons_$color");
        $buttons = count($curbuttons);
        $canUseAny = false;
        $occupancy = $this->getOccupancyMatrix($color);
        foreach ($patches as $patch) {
            $moves = $this->arg_possibleMoves($patch, $color, null, $occupancy);
            $canPlace = false;
            foreach ($moves as $arr) {
                if (count($arr) > 0) {
                    $canPlace = true;
                    break;
                }
            }
            $res['patches'][$patch]['moves'] = $moves;
            $res['patches'][$patch]['canPlace'] = $canPlace;
            $cost = $this->getRulesFor($patch, 'cost');
            $canPay = $cost <= $buttons;
            $res['patches'][$patch]['canPay'] = $canPay;
            $canUse = $canPay && $canPlace;
            $res['patches'][$patch]['canUse'] = $canPay && $canUse;
            $canUseAny = $canUseAny || $canUse;
        }
        // $advance = $this->dbGetAdvance($player_id);
        $advance = 0; //todo
        $res += ['advance' => $advance];
        $res += ['buttons' => $buttons];
        $res['canPatch'] = $canUseAny;

        return $res;
    }

    function arg_occupancyData($color)
    {
        $tokens = $this->tokens->getTokensOfTypeInLocation("patch", "square_${color}%");
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

    function getOccupancyMatrix($color)
    {
        $occupdata = null;
        if ($color !== null) {
            $occupdata = $this->arg_occupancyData($color);
        }
        $occupancy = $this->matrix->occupancyMatrix($occupdata);

        return $occupancy;
    }

    function getOccupancyEmpty($occupancy)
    {
        $unoccup = $this->matrix->remap($occupancy, '', 0);
        $unoccup_count = count($unoccup);
        $empty = $unoccup_count;
        return $empty;
    }

    function arg_possibleMoves($patch, $color = null, $rotor = null, $occupancy = null)
    {
        if ($color !== null) {
            $prefix = "square_${color}_";
        } else
            $prefix = '';

        if ($occupancy == null) {
            $occupancy = $this->getOccupancyMatrix($color);
        }
        $mask = $this->getRulesFor($patch, 'mask');
        return $this->matrix->possibleMoves($mask, $prefix, $rotor, $occupancy);
    }

    function arg_canBuyPatches($color)
    {
        //$patches = ['patch_16', 'patch_1', 'patch_18'];
        $patchesall = $this->tokens->getTokensInLocation('market', null, 'token_state');
        $keys = $this->tokens->toTokenKeyList($patchesall);
        $index = array_search('token_neutral', $keys);
        array_splice($keys, $index, 1);
        $size = count($keys);
        if ($size <= 3)
            return $keys;
        $patches = [];
        for ($i = 0; $i < 3; $i++) {
            $patches[] = $keys[($index + $i) % $size];
        }
        return $patches;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    function st_gameTurnNextPlayer()
    {
        $endOfGame = false;
        if ($endOfGame) {
            $this->saction_FinalScoring();
            $this->gamestate->nextState('last');
            return;
        }
        $this->activateNextPlayerCustom();
        $this->gamestate->nextState('next');
    }

    function st_playerTurn()
    {
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

    function zombieTurn($state, $active_player)
    {
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

    function upgradeTableDb($from_version)
    {
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
}
