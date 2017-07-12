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
use welwitschi\User;

/**
 * Class BetManager
 * This class contains various methods to manage bets.
 * @package cheetah
 */
class BetManager {

	/**
	 * BetManager constructor.
	 * Stores a mysqli connection to interface with the database
	 * @param mysqli $db: The database connection used to read and write data
	 *                    into the database
	 */
	public function __construct(mysqli $db) {
		$this->db = $db;
	}

	/**
	 * Retrieves all bets for a specific user
	 * @SuppressWarnings checkInnerAssignment
	 * @param User $user: The user for which to fetch the bets
	 * @return array: An array of bets with the bet IDs acting as keys
	 */
	public function getAllBetsForUser(User $user) : array {
		$stmt = $this->db->prepare(
			""
		);
		$stmt->bind_param("i", $user->id);
		$stmt->execute();
		$results = $stmt->get_result();

		$bets = [];
		while ($betRow = $results->fetch_array()) {
			$bets[(int)$betRow["id"]] = new Bet(
				(int)$betRow["id"], $user,
				$this->getMatch((int)$betRow["match_id"]),
				(int)$betRow["home_score"], (int)$betRow["away_score"]);
		}

		return $bets;
	}

	/**
	 * Fetches all matches on a given match day
	 * @SuppressWarnings checkInnerAssignment
	 * @param int $matchday: The matchday for which to fetch the Matches
	 * @return array: An array of matches with the match IDs as keys
	 */
	public function getAllMatchesOnMatchday(int $matchday) : array {
		$stmt = $this->db->prepare(
			"SELECT * from matches WHERE matchday=?"
		);
		$stmt->bind_param("i", $matchday);
		$stmt->execute();
		$results = $stmt->get_result()->fetch_all();

		$matches = [];

		while ($match = $results->fetch_array()) {
			$matches[(int)$match["id"]] = new Match(
				(int)$match["id"],
				$this->getTeam($match["home_team"]),
				$this->getTeam($match["home_team"]),
				$matchday,
				(string)$match["kickoff"],
				(bool)$match["finished"]);
		}

		return $matches;

	}

	/**
	 * Fetches a specific match from the database
	 * @param int $id: The match ID with which to identify the match
	 * @return Match: The retrieved match. null if no match was found
	 */
	public function getMatch(int $id) : ? Match {
		$stmt = $this->db->prepare(
			"SELECT * from matches WHERE id=?"
		);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$data = $stmt->get_result()->fetch_array();

		if ($data === null) {
			return null;
		} else {
			return new Match(
				$id,
				$this->getTeam($data["home_team"]),
				$this->getTeam($data["away_team"]),
				(int)$data["matchday"],
				(string)$data["kickoff"],
				(bool)$data["finished"]
			);
		}
	}

	/**
	 * Fetches a team from the database using the unique team ID as an
	 * identifier
	 * @param int $id: The ID to search for
	 * @return Team: The retrieved Team object, or null if no team with the
	 *               specified ID was found
	 */
	public function getTeam(int $id) : ? Team {
		$stmt = $this->db->prepare(
			"SELECT * from teams WHERE id=?"
		);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$data = $stmt->get_result()->fetch_array();

		if ($data === null) {
			return null;
		} else {
			return new Team(
				$id,
				(string)$data["name"],
				(string)$data["shortname"],
				(string)$data["abbreviation"],
				(string)$data["icon"]
			);
		}
	}

	/**
	 * Places a bet on behalf of a user
	 * @param User $user: The user betting
	 * @param Match $match: The match on which the user is betting
	 * @param int $homeScore: The score bet on the home team
	 * @param int $awayScore: The score bet on the away team
	 * @return bool: true if the bet was placed successfully, false if not.
	 *               Bets will fail if the match has already started
	 */
	public function placeBet(
		User $user, Match $match, int $homeScore, int $awayScore) : bool {

		if ($match->hasStarted()) {
			return false;
		} else {
			$stmt = $this->db->prepare(
				"INSERT INTO bets " .
				"(user_id, match_id, home_score, away_score) " .
				"VALUES (?, ?, ?, ?) " .
				"ON DUPLICATE KEY UPDATE home_score=?, away_score=?;"
			);
			$stmt->bind_param("iiiiii", $user->id, $match->id,
				$homeScore, $awayScore, $homeScore, $awayScore);
			$stmt->execute();
			$this->db->commit();
			return true;
		}
	}
}