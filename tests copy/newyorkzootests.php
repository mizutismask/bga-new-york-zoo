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

        $expected = ["action_zone_6", "action_zone_anml_7", "action_zone_8"]; //3 zones for 5 players
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

    function testCheckIfBreedingLineCrossed() {
        echo $this->checkIfBreedingLineCrossed(3, 4, 1) ? "CheckIfBreedingLineCrossed 1: PASSED\n" : "CheckIfBreedingLineCrossed 1: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(23, 24, 1) ? "CheckIfBreedingLineCrossed 2: PASSED\n" : "CheckIfBreedingLineCrossed 2: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(23, 2, 4) ? "CheckIfBreedingLineCrossed 3: PASSED\n" : "CheckIfBreedingLineCrossed 3: FAILED\n";
        echo !$this->checkIfBreedingLineCrossed(4, 8, 4) ? "CheckIfBreedingLineCrossed 4: PASSED\n" : "CheckIfBreedingLineCrossed 4: FAILED\n";
        echo !$this->checkIfBreedingLineCrossed(24, 1, 2) ? "CheckIfBreedingLineCrossed 5: PASSED\n" : "CheckIfBreedingLineCrossed 5: FAILED\n";
        echo !$this->checkIfBreedingLineCrossed(14, 18, 4) ? "CheckIfBreedingLineCrossed 6: PASSED\n" : "CheckIfBreedingLineCrossed 6: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(25, 4, 4) ? "CheckIfBreedingLineCrossed 7: PASSED\n" : "CheckIfBreedingLineCrossed 7: FAILED\n";
        echo !$this->checkIfBreedingLineCrossed(24, 3, 4) ? "CheckIfBreedingLineCrossed 8: PASSED\n" : "CheckIfBreedingLineCrossed 8: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(24, 4, 5) ? "CheckIfBreedingLineCrossed 9: PASSED\n" : "CheckIfBreedingLineCrossed 9: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(8, 10, 2) ? "CheckIfBreedingLineCrossed 10: PASSED\n" : "CheckIfBreedingLineCrossed 10: FAILED\n";
        echo !$this->checkIfBreedingLineCrossed(9, 12, 3) ? "CheckIfBreedingLineCrossed 11: PASSED\n" : "CheckIfBreedingLineCrossed 11: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(5, 9, 4) ? "CheckIfBreedingLineCrossed 12: PASSED\n" : "CheckIfBreedingLineCrossed 11: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(17, 20, 3) ? "CheckIfBreedingLineCrossed 13: PASSED\n" : "CheckIfBreedingLineCrossed 13: FAILED\n";
    }

    function testAll() {
        $this->testGetPartMinus();
        $this->testNextAnmlZoneAfterFenceZone();
        $this->testNextFenceZoneAfterAnmlZone();
        $this->testGetNextActionZones();
        $this->testCheckIfBreedingLineCrossed();
    }
}

$test1 = new NewYorkZooTests();
$test1->testAll();
