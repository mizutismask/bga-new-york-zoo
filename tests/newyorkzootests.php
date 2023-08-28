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
        echo $this->checkIfBreedingLineCrossed(3, 4) ? "CheckIfBreedingLineCrossed: PASSED\n" : "CheckIfBreedingLineCrossed 1: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(23, 24) ? "CheckIfBreedingLineCrossed: PASSED\n" : "CheckIfBreedingLineCrossed 2: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(23, 2) ? "CheckIfBreedingLineCrossed: PASSED\n" : "CheckIfBreedingLineCrossed 3: FAILED\n";
        echo !$this->checkIfBreedingLineCrossed(4, 8) ? "CheckIfBreedingLineCrossed: PASSED\n" : "CheckIfBreedingLineCrossed 4: FAILED\n";
        echo !$this->checkIfBreedingLineCrossed(24, 1) ? "CheckIfBreedingLineCrossed: PASSED\n" : "CheckIfBreedingLineCrossed 5: FAILED\n";
        echo !$this->checkIfBreedingLineCrossed(14, 18) ? "CheckIfBreedingLineCrossed: PASSED\n" : "CheckIfBreedingLineCrossed 6: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(25, 4) ? "CheckIfBreedingLineCrossed: PASSED\n" : "CheckIfBreedingLineCrossed 7: FAILED\n";
        echo !$this->checkIfBreedingLineCrossed(24, 3) ? "CheckIfBreedingLineCrossed: PASSED\n" : "CheckIfBreedingLineCrossed 8: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(24, 4) ? "CheckIfBreedingLineCrossed: PASSED\n" : "CheckIfBreedingLineCrossed 9: FAILED\n";
        echo $this->checkIfBreedingLineCrossed(8, 10) ? "CheckIfBreedingLineCrossed: PASSED\n" : "CheckIfBreedingLineCrossed 10: FAILED\n";
    }

    function testRelativeDistanceFromBreedingLineToOldPosition() {
        //move 23->4
        echo $this->relativeDistanceFromBreedingLineToOldPosition(23, 4) == -6 ? "CheckRelativeDistance: PASSED\n" : "CheckRelativeDistance 1: FAILED\n"; //kangaroo
        echo $this->relativeDistanceFromBreedingLineToOldPosition(4, 4) == 0 ? "CheckRelativeDistance: PASSED\n" : "CheckRelativeDistance 2: FAILED\n"; //kangaroo
        echo $this->relativeDistanceFromBreedingLineToOldPosition(1, 4) == -3 ? "CheckRelativeDistance: PASSED\n" : "CheckRelativeDistance 3: FAILED\n"; //kangaroo

        echo $this->relativeDistanceFromBreedingLineToOldPosition(23, 24) == -1 ? "CheckRelativeDistance: PASSED\n" : "CheckRelativeDistance 4: FAILED\n"; //flamingo
        echo $this->relativeDistanceFromBreedingLineToNewPosition(2, 24) == 3 ? "CheckRelativeDistance: PASSED\n" : "CheckRelativeDistance 5: FAILED\n"; //flamingo
    }

    function testGetShortestDistance() {
        echo $this->getShortestDistance(1) == 1 ? "testGetShortestDistance: PASSED\n" : "testGetShortestDistance 1: FAILED\n";
        echo $this->getShortestDistance(-1) == -1 ? "testGetShortestDistance: PASSED\n" : "testGetShortestDistance 2: FAILED\n";
        echo $this->getShortestDistance(-24) == 1 ? "testGetShortestDistance: PASSED\n" : "testGetShortestDistance 3: FAILED\n";
    }

    function testAll() {
        $this->testGetPartMinus();
        $this->testNextAnmlZoneAfterFenceZone();
        $this->testNextFenceZoneAfterAnmlZone();
        $this->testGetNextActionZones();
        $this->testCheckIfBreedingLineCrossed();
        $this->testRelativeDistanceFromBreedingLineToOldPosition();
        $this->testGetShortestDistance();
    }
}

$test1 = new NewYorkZooTests();
$test1->testAll();
