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
 * newyorkzoo.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in newyorkzoo_newyorkzoo.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");
define("CELL_WIDTH", 42);

class view_newyorkzoo_newyorkzoo extends game_view {
  function getGameName() {
    return "newyorkzoo";
  }

  function getTemplateName() {
    return self::getGameName() . "_" . self::getGameName();
  }



  function processPlayerBlock($player_id, $player, $player_count) {
    $order = $player['player_no'];
    $name = $player['player_name'];
    global $g_user;
    $current_player = $g_user->get_id();
    // Create squares
    $this->page->reset_subblocks('square');
    $this->page->reset_subblocks('anml_square');
    $this->page->reset_subblocks('highlight_square');
    $this->page->reset_subblocks('house');
    $hor_scale = CELL_WIDTH;
    $ver_scale = CELL_WIDTH;

    $houses = $this->game->isSoloMode() ?  $this->game->boards[$player_count][$this->game->getSoloHousesCount()]["houses"] : $this->game->boards[$player_count][$order]["houses"];
    for ($x = 1; $x <= $houses; $x++) {
      $classes = 'house_' . $x;
      $this->page->insert_block("house", array(
        'X' => $x, 'Y' => 0, 'LEFT' => round(($x) * $hor_scale),
        'TOP' => round((0) * $ver_scale), 'CLASSES' => $classes, "PLAYER_ORDER" => $order, "HOUSE_INDEX" => $x
      ));
    }

    $gridSize = $this->game->getGridSize();
    for ($x = 0; $x < $gridSize[0]; $x++) {
      for ($y = 0; $y < $gridSize[1]; $y++) {
        $classes = '';
        $this->page->insert_block("square", array(
          'X' => $x, 'Y' => $y, 'LEFT' => round(($x) * $hor_scale),
          'TOP' => round(($y) * $ver_scale), 'CLASSES' => $classes, "ORDER" => $order
        ));
      }
    }

    for ($x = 0; $x < $gridSize[0]; $x++) {
      for ($y = 0; $y < $gridSize[1]; $y++) {
        $classes = '';
        $this->page->insert_block("anml_square", array(
          'X' => $x, 'Y' => $y, 'LEFT' => round(($x) * $hor_scale),
          'TOP' => round(($y) * $ver_scale), 'CLASSES' => $classes, "ORDER" => $order
        ));
      }
    }

    $own = $player_id == $current_player;
    if ($own) {
      for ($x = 0; $x < $gridSize[0]; $x++) {
        for ($y = 0; $y < $gridSize[1]; $y++) {
          $classes = '';
          $this->page->insert_block("highlight_square", array(
            'X' => $x, 'Y' => $y, 'LEFT' => round(($x) * $hor_scale),
            'TOP' => round(($y) * $ver_scale), 'CLASSES' => $classes, "ORDER" => $order
          ));
        }
      }
    }

    $this->page->insert_block("player_board", array(
      "ORDER" => $order, "PLAYER_NAME" => $name,
      "PLAYER_ID" => $player_id, "OWN" => $own ? "own" : "",
      "PLAYER_COUNT" => $player_count,
      "SOLO_BOARD" => $this->game->getSoloBoard($player_count),
    ));
  }

  function build_page($viewArgs) {
    // Get players & players number
    $players = $this->game->loadPlayersBasicInfos();
    $players_nbr = count($players);
    /**
     * ********* Place your code below: ***********
     */
    $template = self::getTemplateName();
    $num = $players_nbr;
    $this->tpl['PLS'] = $num;
    global $g_user;
    $this->tpl['PCOLOR'] = 'ffffff'; // spectator
    $current_player = $g_user->get_id();

    if ($players_nbr == 2 && $this->game->isFastGame()) {
      $this->page->begin_block($template, "handMarket");
      foreach ($players as $player_info) {
        if (isset($players[$current_player])) { // may be not set if spectator
          $this->page->insert_block("handMarket", array(
            "PLAYER_ID" => $player_info['player_id'],
            "OTHER_CLASSES" => $player_info['player_id'] != $current_player ? "opponent" : "",
          ));
        }
      }
    }

    $this->page->begin_block($template, "patch");
    $this->page->begin_block($template, "patchcss");
    $CARDS_W = 1000;
    $CARDS_H = 1500;
    $COLS = 5;
    $CELL = CELL_WIDTH;


    foreach ($this->game->token_types as $id => &$info) {
      if (startsWith($id, 'patch')) {
        $num = $this->game->getRulesFor($id, 'num');
        $mask = $this->game->getRulesFor("patch_$num", 'mask');
        $occ = $this->game->getRulesFor($id, 'occurrences');
        $color = $this->game->getRulesFor($id, 'color');
        $matrix = [];
        $coords = $this->game->matrix->toPolygon($mask, CELL_WIDTH, $matrix);
        $h = count($matrix);
        $w = count($matrix[0]);
        $points = '';
        $clippoints = '';
        foreach ($coords as list($x, $y)) {
          $points .= "$x,$y ";
          $px = (int)(100 * $x / CELL_WIDTH / $w);
          $py = (int)(100 * $y / CELL_WIDTH / $h);
          $clippoints .= "$px% $py%,";
        }
        $clippoints = substr($clippoints, 0, strlen($clippoints) - 1);
        $patchId = $id;
        for ($i = 0; $i < $occ; $i++) {
          if ($occ != 1) {
            $patchId = $id . "_" . ($i + 1);
          }
          $classes = "";
          $faceClasses = "";
          if ($color === "filler") {
            $classes = "filler";
            $faceClasses = "filler_face";
          }
          $this->page->insert_block("patch", ['PATCH_ID' => $patchId, 'NUM' => $num, 'POL_POINTS' => $points, 'CLIP_POINTS' => $clippoints, 'PATCH_CLASSES' => $classes, 'PATCH_FACE_CLASSES' => $faceClasses]);
        }

        $fw = $w * CELL_WIDTH;
        $fh = $h * CELL_WIDTH;
        $i = ($num - 1) % $COLS;
        $j = floor(($num - 1) / $COLS);

        $this->page->insert_block("patchcss", [
          'NUM' => $num, 'CLIP_POINTS' => $clippoints,
          'W' => $fw, 'H' => $fh,
        ]);
      }
    }
    if ($this->game->isSoloMode()) {
      $this->page->begin_block($template, "actionStripZoneSolo");
      foreach ($this->game->actionStripZones as $id => &$zone) {
        $this->page->insert_block("actionStripZoneSolo", ['ID' => $id, 'X' => $zone['topX'], 'Y' => $zone['topY'], 'WIDTH' => $zone['width'], 'HEIGHT' => $zone['height'], 'ANIMAL_ZONE' => $zone['type'] === ANIMAL ? "nyz_animal_action_zone" : ""]);
      }
    } else {
      $this->page->begin_block($template, "actionStripZone");
      foreach ($this->game->actionStripZones as $id => &$zone) {
        $this->page->insert_block("actionStripZone", ['ID' => $id, 'X' => $zone['topX'], 'Y' => $zone['topY'], 'WIDTH' => $zone['width'], 'HEIGHT' => $zone['height'], 'ANIMAL_ZONE' => $zone['type'] === ANIMAL ? "nyz_animal_action_zone" : ""]);
      }
    }

    $this->page->begin_block($template, "birthZone");
    foreach ($this->game->birthZones as $id => &$zone) {
      $this->page->insert_block("birthZone", ['ID' => $id, 'X' => $zone['topX'], 'Y' => $zone['topY'], 'WIDTH' => $zone['width'], 'HEIGHT' => $zone['height']]);
    }

    $this->page->begin_block($template, "bonus-mask");
    $attractions = $this->game->mtCollectAllWithFieldValue("color", "bonus");
    $uniqueMasks = array_unique(array_map(fn ($a) => $a["mask"], $attractions));
    usort($uniqueMasks, function ($a, $b) {
      return substr_count($b, "1") - substr_count($a, "1");
    });
    foreach ($uniqueMasks as $i => $mask) {
      $this->page->insert_block("bonus-mask", ['COUNTER' => $i, 'MASK' => $mask,]);
    }


    $this->page->begin_block($template, "square");
    $this->page->begin_block($template, "anml_square");
    $this->page->begin_block($template, "highlight_square");
    $this->page->begin_block($template, "house");
    $this->page->begin_block($template, "player_board");
    // inner blocks in player blocks
    // ...
    // player blocks
    if (isset($players[$current_player])) { // may be not set if spectator
      $curplayer_info = $players[$current_player];
      $this->tpl['PCOLOR'] = $curplayer_info['player_color'];
      $this->processPlayerBlock($current_player, $curplayer_info, $players_nbr);
    }
    // remaining boards in players order
    foreach ($players as $player_info) {
      if ($player_info['player_id'] != $current_player)
        $this->processPlayerBlock($player_info['player_id'], $player_info, $players_nbr);
    }

    /*********** Do not change anything below this line  ************/
  }
}
