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
use InvalidArgumentException;
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
	 * @param User $user: The user for which to fetch the bets
	 * @return array: An array of bets with the bet IDs acting as keys
	 * @SuppressWarnings docBlocks
	 */
	public function getAllBetsForUser(User $user) : array {
		$stmt = $this->db->prepare("SELECT * FROM bets WHERE user_id=?;");
		$stmt->bind_param("i", $user->id);
		$stmt->execute();
		$results = $stmt->get_result();

		$bets = [];
		foreach ($results->fetch_all(MYSQLI_ASSOC) as $betRow) {
			/** @noinspection PhpParamsInspection */
			$bets[(int)$betRow["id"]] = new Bet(
				(int)$betRow["id"], $user,
				Match::fromId($this->db, (int)$betRow["match_id"]),
				(int)$betRow["home_score"], (int)$betRow["away_score"]);
		}

		return $bets;
	}

	/**
	 * Retrieves all matches for a user on a specified matchday
	 * @param User $user: The user for which to retrieve the bets for
	 * @param int $matchday: The matchday for which to retrieve the match for
	 * @return array: A List of Bets that qualify for the given parameters
	 */
	public function getAllBetsForUserOnMatchday(User $user, int $matchday)
	: array {
		$all = $this->getAllBetsForUser($user);
		$onMatchday = [];
		foreach ($all as $bet_id => $bet) {
			if ($bet->match->matchday == $matchday) {
				array_push($onMatchday, $bet);
			}
		}
		return $onMatchday;
	}

	/**
	 * Places a bet on behalf of a user without any authentication needed.
	 * Should generally not be used from outside this class.
	 * @param User $user: The user betting
	 * @param Match $match: The match on which the user is betting
	 * @param int $homeScore: The score bet on the home team
	 * @param int $awayScore: The score bet on the away team
	 * @return bool: true if the bet was placed successfully, false if not.
	 *               Bets will fail if the match has already started
	 */
	public function placeBetWithoutAuthentication(
		User $user, Match $match, int $homeScore, int $awayScore) : bool {

		if ($homeScore < 0 || $awayScore < 0) {
			throw new InvalidArgumentException("Negative Scores detected!");
		}

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

	/**
	 * Places a bet for a logged in User
	 * @param User $user: The User that places this bet
	 * @param Match $match: The match on which to place this bet
	 * @param int $homeScore: The score bet on the home team
	 * @param int $awayScore: The score bet on the away team
	 * @return bool: true if the bet was places successfully, else false
	 */
	public function placeBetWithLoginSession(
		User $user, Match $match, int $homeScore, int $awayScore) : bool {

		if ($user->isLoggedIn()) {
			return $this->placeBetWithoutAuthentication(
				$user, $match, $homeScore, $awayScore);
		} else {
			return false;
		}
	}

	/**
	 * Places a bet using an API key as authentication
	 * @param User $user: The User that places the bet
	 * @param string $apiKey: The API Key used for authentication
	 * @param Match $match: The match on which to bet
	 * @param int $homeScore: The score bet on the home team
	 * @param int $awayScore: The score bet on the away team
	 * @return bool: true if the bet placing was successful, false otherwise
	 * @SuppressWarnings functionMaxParameters
	 */
	public function placeBetWithApiKey(
		User $user, string $apiKey,
		Match $match, int $homeScore, int $awayScore) : bool {

		if ($user->verifyApiKey($apiKey)) {
			return $this->placeBetWithoutAuthentication(
				$user, $match, $homeScore, $awayScore);
		} else {
			return false;
		}
	}
}