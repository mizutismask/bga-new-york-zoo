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
define("CELL_WIDTH",43);

class view_newyorkzoo_newyorkzoo extends game_view
{
  function getGameName()
  {
    return "newyorkzoo";
  }

  function getTemplateName()
  {
    return self::getGameName() . "_" . self::getGameName();
  }



  function processPlayerBlock($player_id, $player, $player_count)
  {
    $color = $player['player_color'];
    $name = $player['player_name'];
    $no = $player['player_no'];
    global $g_user;
    $current_player = $g_user->get_id();
    // Create squares
    $this->page->reset_subblocks('square');
    $hor_scale = CELL_WIDTH;
    $ver_scale = CELL_WIDTH;
    $gridSize = $this->game->getGridSize();
    for ($x = 0; $x < $gridSize[0]; $x++) {
      for ($y = 0; $y < $gridSize[1]; $y++) {
        $classes = '';
        $this->page->insert_block("square", array(
          'X' => $x, 'Y' => $y, 'LEFT' => round(($x) * $hor_scale),
          'TOP' => round(($y) * $ver_scale), 'CLASSES' => $classes, "COLOR" => $color
        ));
      }
    }
    $own = $player_id == $current_player;
    $this->page->insert_block("player_board", array(
      "COLOR" => $color, "PLAYER_NAME" => $name, "PLAYER_NO" => $no,
      "PLAYER_ID" => $player_id, "OWN" => $own ? "own" : "",
      "PLAYER_COUNT" => $player_count,
    ));
  }

  function build_page($viewArgs)
  {
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

    /*$this->page->insert_block("actionStrip", [
      'NUM' => $num, 'CLIP_POINTS' => $clippoints,
      'W' => $fw, 'H' => $fh,
    ]);*/
/**/ 
$this->page->begin_block($template, "patchTest");
    for ($num = 1; $num <= 61; $num++) {
      $mask = $this->game->getRulesFor("patch_$num", 'mask');
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
      $this->page->insert_block("patchTest", ['NUM' => $num, 'POL_POINTS' => $points, 'CLIP_POINTS' => $clippoints]);

      
    }

/**/ 

    $this->page->begin_block($template, "patch");
    $this->page->begin_block($template, "patchcss");
    $CARDS_W = 1000;
    $CARDS_H = 1500;
    $COLS = 5;
    $CELL = CELL_WIDTH;

    for ($num = 1; $num <= 61; $num++) {
      $mask = $this->game->getRulesFor("patch_$num", 'mask');
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
      $this->page->insert_block("patch", ['NUM' => $num, 'POL_POINTS' => $points, 'CLIP_POINTS' => $clippoints]);

      $fw = $w * CELL_WIDTH;
      $fh = $h * CELL_WIDTH;
      $i = ($num - 1) % $COLS;
      $j = floor(($num - 1) / $COLS);

      $this->page->insert_block("patchcss", [
        'NUM' => $num, 'CLIP_POINTS' => $clippoints,
        'W' => $fw, 'H' => $fh,
      ]);
    }
    $this->page->begin_block($template, "actionStripZone");
    foreach ($this->game->actionStripZones as $id => &$zone){
      $this->page->insert_block("actionStripZone", ['ID' => $id, 'X' => $zone['topX'], 'Y' => $zone['topY'], 'WIDTH' => $zone['width'],'HEIGHT' => $zone['height']]);
    }

    $this->page->begin_block($template, "square");
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