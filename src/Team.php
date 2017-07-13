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
class Team extends Model {

	/**
	 * @var int: The ID of the Team in the database
	 */
	public $id;

	/**
	 * @var string: The full name of the Team
	 */
	public $name;

	/**
	 * @var string: A shortened version of the team's name
	 */
	public $shortname;

	/**
	 * @var string: A 3-character abbreviation associated with the Team
	 */
	public $abbreviation;

	/**
	 * @var string: A path to an image of the Team's logo
	 */
	public $icon;

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
	 * Defines the table name for the Team Class.
	 * @return string: 'teams'
	 */
	public static function tableName(): string {
		return "teams";
	}

	/**
	 * Generates a new Team object from a row in the database.
	 * The row must be from the teams database table
	 * @param $db: A database connection for further queries
	 * @param array $team: The row in the database
	 * @return Model: The generated Team object
	 */
	public static function fromRow(mysqli $db, array $team) : Model {
		return new Team(
			(int)$team["id"],
			(string)$team["name"],
			(string)$team["shortname"],
			(string)$team["abbreviation"],
			(string)$team["icon"]);
	}
}