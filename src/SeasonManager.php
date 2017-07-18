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
 * Class SeasonManager
 * Manages an overall season, keeping track of the matchdays
 * @package cheetah
 */
class SeasonManager {

	/**
	 * SeasonManager constructor.
	 * @param mysqli $db: The database connection to use
	 */
	function __construct(mysqli $db) {
		$this->db = $db;
	}

	/**
	 * @return int: The last matchday
	 */
	public function getMaxMatchday() : int {
		return (int) $this->db->query(
			"SELECT MAX(matchday) AS matchday FROM matches;"
		)->fetch_array(MYSQLI_ASSOC)["matchday"];
	}

	/**
	 * @return int: The current matchday,
	 *              or the last matchday if the season is already over
	 */
	public function getCurrentMatchday() {
		$result = $this->db->query(
			"SELECT MIN(matchday) AS matchday " .
			"FROM matches WHERE finished=0;"
		)->fetch_array(MYSQLI_ASSOC);
		if ($result["matchday"] === null) {
			return $this->getMaxMatchday();
		} else {
			return $result["matchday"];
		}
	}
}