<?php
define("APP_GAMEMODULE_PATH", "../misc/"); // include path to stubs, which defines "table.game.php" and other classes
require_once('../newyorkzoo.game.php');

class NewYorkZooSoloToken4Tests extends NewYorkZoo { // this is your game class defined in ggg.game.php
    function __construct() {
        // parent::__construct();
        include '../material.inc.php'; // this is how this normally included, from constructor
    }

    function getNeutralPositionNumber() {
        return "17";
    }
    function getNeutralTokenPosition() {
        return "action_zone_anml_17";
    }

    function isFenceActionZoneWithoutFences($nextZone) {
        return false;
    }

    function isSoloMode() {
        return true;
    }

    function getSoloTokensAvailable() {
        return [
            [
                "key" => "solo_token_4",
                "location" => "solo_tokens_hand",
            ],
        ];
    }

    // class tests
    function testGetNextActionZones() {

        $next = array_keys($this->getNextActionZones());

        $expected = ["action_zone_21", "action_zone_anml_22", "action_zone_23", "action_zone_24", "action_zone_anml_25", "action_zone_1", "action_zone_anml_2", "action_zone_3", "action_zone_4", "action_zone_anml_5", "action_zone_6", "action_zone_anml_7", "action_zone_8", "action_zone_9", "action_zone_anml_10", "action_zone_11", "action_zone_anml_12", "action_zone_13", "action_zone_14", "action_zone_anml_15", "action_zone_16"];
        $equal = $next === $expected;

        if ($equal) {
            echo "getNextActionZones: PASSED\n";
        } else {
            echo "getNextActionZones: FAILED\n";
            echo "Expected: ";
            var_dump($expected);
            echo "given: ";
            var_dump($next);
        }
    }

    function testAll() {
        $this->testGetNextActionZones();
    }
}

$test1 = new NewYorkZooSoloToken4Tests();
$test1->testAll();
