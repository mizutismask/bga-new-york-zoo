
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- NewYorkZoo implementation : © Séverine Kamycki severinek@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- Example 1: create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):

-- CREATE TABLE IF NOT EXISTS `card` (
--   `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--   `card_type` varchar(16) NOT NULL,
--   `card_type_arg` int(11) NOT NULL,
--   `card_location` varchar(16) NOT NULL,
--   `card_location_arg` int(11) NOT NULL,
--   PRIMARY KEY (`card_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- Example 2: add a custom field to the standard "player" table
ALTER TABLE `player` ADD `player_breeding_remaining` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_has_bred` INT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_has_bonus_bred` INT(1) UNSIGNED NOT NULL DEFAULT '0';

 CREATE TABLE IF NOT EXISTS `token` (
 `token_key` varchar(32) NOT NULL,
 `token_location` varchar(32) NOT NULL,
 `token_state` int(10),
 PRIMARY KEY (`token_key`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  CREATE TABLE IF NOT EXISTS `fence` (
 `id` int NOT NULL AUTO_INCREMENT,
 `player_order` int(1) NOT NULL,
 `token_key` varchar(32) NOT NULL,
 `animal_type` enum('none','meerkat','flamingo','kangaroo','penguin','fox') NOT NULL DEFAULT 'none',
 `animals_added` int(1) NOT NULL DEFAULT 0,
 `animals_added_by_breeding` int(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 UNIQUE(`token_key`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

   CREATE TABLE IF NOT EXISTS `fence_squares` (
 `token_key` varchar(32) NOT NULL,
 `square` varchar(13) NOT NULL, 
 `bonus` INT(1) UNSIGNED NOT NULL DEFAULT '0',
 PRIMARY KEY (`token_key`, `square`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--Action log to resolve things in correct order
CREATE TABLE IF NOT EXISTS `context_log` (
 `id` int NOT NULL AUTO_INCREMENT,
 `player` int(10) NOT NULL,
 `state` varchar(32) NOT NULL, 
 `action` varchar(32) NOT NULL,
 `param1` varchar(20),
 `param2` varchar(20),
 `param3` varchar(20),
 `resolved` INT(1) UNSIGNED NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
