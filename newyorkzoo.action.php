<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * NewYorkZoo implementation : © Séverine Kamycki severinek@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * newyorkzoo.action.php
 *
 * NewYorkZoo main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/newyorkzoo/newyorkzoo/myAction.html", ...)
 *
 */


class action_newyorkzoo extends APP_GameAction {
  // Constructor: please do not modify
  public function __default() {
    if (self::isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
    } else {
      $this->view = "newyorkzoo_newyorkzoo";
      self::trace("Complete reinitialization of board game");
    }
  }

  public function place() {
    self::setAjaxMode();
    $token = self::getArg('token', AT_alphanum, true);
    $rotateY = self::getArg('rotateY', AT_int, true);
    $rotateZ = self::getArg('rotateZ', AT_int, true);
    $dropTarget = self::getArg('dropTarget', AT_alphanum, true);
    $this->game->action_place($token, $dropTarget, $rotateZ, $rotateY);
    self::ajaxResponse();
  }

  public function placeStartFence() {
    self::setAjaxMode();
    $token = self::getArg('token', AT_alphanum, true);
    $rotateY = self::getArg('rotateY', AT_int, true);
    $rotateZ = self::getArg('rotateZ', AT_int, true);
    $dropTarget = self::getArg('dropTarget', AT_alphanum, true);
    $this->game->action_placeStartFence($token, $dropTarget, $rotateZ, $rotateY);
    self::ajaxResponse();
  }

  public function resetStartFences() {
    self::setAjaxMode();
    $this->game->action_resetStartFences();
    self::ajaxResponse();
  }

  public function getAnimals() {
    self::setAjaxMode();
    $actionZone = self::getArg('actionZone', AT_alphanum, true);
    $soloToken = self::getArg('soloToken', AT_alphanum, false);
    $this->game->action_getAnimals($actionZone, $soloToken);
    self::ajaxResponse();
  }

  public function placeAnimal() {
    self::setAjaxMode();
    $from = self::getArg('from', AT_alphanum, false);
    $to = self::getArg('to', AT_alphanum, true);
    $animalType = self::getArg('animalType', AT_alphanum, false);
    $animalId = self::getArg('animalId', AT_alphanum, false);
    $this->game->action_placeAnimal($from, $to, $animalType, $animalId);
    self::ajaxResponse();
  }

  public function placeAnimalFromHouse() {
    self::setAjaxMode();
    $this->game->action_placeAnimalFromHouse();
    self::ajaxResponse();
  }

  public function undoElephantMoveToAnimals() {
    self::setAjaxMode();
    $this->game->action_undoElephantMoveToAnimals();
    self::ajaxResponse();
  }

  public function keepAnimalFromFullFence() {
    self::setAjaxMode();
    $this->game->action_keepAnimalFromFullFence();
    self::ajaxResponse();
  }

  public function dismiss() {
    self::setAjaxMode();
    $this->game->action_dismissAnimal();
    self::ajaxResponse();
  }

  public function dismissAttraction() {
    self::setAjaxMode();
    $this->game->action_dismissAttraction();
    self::ajaxResponse();
  }

  public function chooseFences() {
    self::setAjaxMode();
    $tokenIdsRaw = self::getArg("squares", AT_alphanum, true);
    $tokenIdsRaw = trim($tokenIdsRaw);
    if ($tokenIdsRaw == '')
      $tokenIds = array();
    else
      $tokenIds = explode(' ', $tokenIdsRaw);
    $this->game->action_chooseFences($tokenIds);
    self::ajaxResponse();
  }

  public function endGame() {
    self::setAjaxMode();
    $this->game->action_endGame();
    self::ajaxResponse();
  }

  public function loadBugSQL() {
    self::setAjaxMode();
    $reportId = (int) self::getArg('report_id', AT_int, true);
    $this->game->loadBugSQL($reportId);
    self::ajaxResponse();
  }

  /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */
}
