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
 * Class Team
 * Models a team from the teams table
 * @package cheetah
 */
class Team {

	/**
	 * Team constructor.
	 * @SuppressWarnings functionMaxParameters
	 * @param int $id: The team ID in the database
	 * @param string $name: The full name of the team, ex: FC Bayern München
	 * @param string $shortname: The shortform name of the team, ex: FC Bayern
	 * @param string $abbreviation: A 3-letter abbreviation, ex: FCB
	 * @param string $icon: Path to an icon file for the team
	 */
	public function __construct(int $id,
								string $name,
								string $shortname,
								string $abbreviation,
								string $icon) {
		$this->id = $id;
		$this->name = $name;
		$this->shortname = $shortname;
		$this->abbreviation = $abbreviation;
		$this->icon = $icon;
	}

	/**
	 * Generates a new Team object from a row in the database.
	 * The row must be from the teams database table
	 * @param array $team: The row in the database
	 * @return Team: The generated Team object
	 */
	public static function fromRow(array $team) : Team {
		return new Team($team["id"], $team["name"], $team["shortname"],
			$team["abbreviation"], $team["icon"]);
	}

	/**
	 * Fetches a team from the database using the unique team ID as an
	 * identifier
	 * @param mysqli $db: The database connection used to fetch the data
	 * @param int $id : The ID to search for
	 * @return Team|null : The retrieved Team object, or null if no team with
	 *                     the specified ID was found
	 */
	public static function getTeam(mysqli $db, int $id) : ? Team {
		$stmt = $db->prepare(
			"SELECT * from teams WHERE id=?"
		);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$data = $stmt->get_result()->fetch_array();

		if ($data === null) {
			return null;
		} else {
			return self::fromRow($data);
		}
	}

	/**
	 * Fetches all teams currently in the database table `teams`.
	 * @param mysqli $db: The database used to fetch the data
	 * @return array : An array of Team objects with their database IDs as keys
	 */
	public static function getAllTeams(mysqli $db) : array {
		$result = $db->query("SELECT * FROM teams;");

		$teams = [];

		while ($row = $result->fetch_array()) {
			$teams[$row["id"]] = self::fromRow($row);
		}

		return $teams;
	}
}