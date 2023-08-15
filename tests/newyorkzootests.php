<?php
define("APP_GAMEMODULE_PATH", "../misc/"); // include path to stubs, which defines "table.game.php" and other classes
require_once('../newyorkzoo.game.php');

class NewYorkZooTests  extends NewYorkZoo { // this is your game class defined in ggg.game.php
    function __construct() {
        // parent::__construct();
        include '../material.inc.php'; // this is how this normally included, from constructor
    }

    function getNeutralPositionNumber() {
        return "5";
    }

    function isFenceActionZoneWithoutFences($nextZone) {
        return false;
    }

    // class tests
    function testGetPartMinus() {

        $given = getPart("action_zone_1", -2);

        $expected = "zone";
        $equal = $given === $expected;

        if ($equal) {
            echo "testGetPartMinus: PASSED\n";
        } else {
            echo "testGetPartMinus: FAILED\n";
            echo "Expected: $expected, value: $given\n";
        }
    }
    function testNextAnmlZoneAfterFenceZone() {

        $next = $this->getNextActionZone("action_zone_1");

        $expected = "action_zone_anml_2";
        $equal = $next === $expected;

        if ($equal) {
            echo "testNextAnmlZoneAfterFenceZone: PASSED\n";
        } else {
            echo "testNextAnmlZoneAfterFenceZone: FAILED\n";
            echo "Expected: $expected, value: $next\n";
        }
    }

    function testNextFenceZoneAfterAnmlZone() {

        $next = $this->getNextActionZone("action_zone_anml_2");

        $expected = "action_zone_3";
        $equal = $next === $expected;

        if ($equal) {
            echo "testNextFenceZoneAfterAnmlZone: PASSED\n";
        } else {
            echo "testNextFenceZoneAfterAnmlZone: FAILED\n";
            echo "Expected: $expected, value: $next\n";
        }
    }
    function testGetNextActionZones() {

        $next = $this->getNextActionZones();

        $expected = ["action_zone_6", "action_zone_anml_7", "action_zone_8"];//3 zones for 5 players
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
        $this->testGetPartMinus();
        $this->testNextAnmlZoneAfterFenceZone();
        $this->testNextFenceZoneAfterAnmlZone();
        $this->testGetNextActionZones();
    }
}

$test1 = new NewYorkZooTests();
$test1->testAll();
