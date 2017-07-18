<?php
/**
 * Copyright Hermann Krumrey <hermann@krumreyh.com> 2017
 *
 * This file is part of cheetah-bets.
 *
 * cheetah-bets is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * cheetah-bets is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with cheetah-bets.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace cheetah;
use mysqli;


/**
 * Class Player
 * Models a player from the players table
 * @package cheetah
 */
class Player extends Model {

	/**
	 * @var int: The ID of the player in the database
	 */
	public $id;

	/**
	 * @var Team: The team with which the player is currently affiliated
	 */
	public $team;

	/**
	 * @var string: The name of the player
	 */
	public $name;

	/**
	 * Player constructor.
	 * @param int $id: The player's ID in the database
	 * @param Team $team: The team affiliated with the player
	 * @param string $name: The name of the player
	 */
	public function __construct(int $id, Team $team, string $name) {
		$this->id = $id;
		$this->team = $team;
		$this->name = $name;
	}

	/**
	 * Defines the table name for the Player Class.
	 * @return string: 'players'
	 */
	public static function tableName(): string {
		return "players";
	}

	/**
	 * Generates a Player object from an associative array representing a
	 * row in the players table in the database
	 * @param mysqli $db: The database connection to use for further queries
	 * @param array $row: The database row to convert into a Player object
	 * @return Model: The generated Player object
	 * @SuppressWarnings docBlocks
	 */
	public static function fromRow(mysqli $db, array $row) : Model {
		/** @noinspection PhpParamsInspection */
		return new Player(
			(int)$row["id"],
			Team::fromId($db, (int)$row["team_id"]),
			(string)$row["name"]);
	}
}