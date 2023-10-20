<?php
define("APP_GAMEMODULE_PATH", "../misc/"); // include path to stubs, which defines "table.game.php" and other classes
require_once('../newyorkzoo.game.php');

class NewYorkZooSoloToken3Tests extends NewYorkZoo { // this is your game class defined in ggg.game.php
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
                "key" => "solo_token_3",
                "location" => "solo_tokens_hand",
            ],
        ];
    }

    // class tests
    function testGetNextActionZones() {

        $next = $this->getNextActionZones();

        $expected = ["action_zone_anml_20",]; 
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

$test1 = new NewYorkZooSoloToken3Tests();
$test1->testAll();
