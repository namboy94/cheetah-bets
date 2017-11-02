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
use welwitschi\Authenticator;
use welwitschi\User;
use mysqli;

/**
 * Class Bet
 * Models a bet from the bets table
 * @package cheetah
 */
class Bet extends Model {

	/**
	 * @var int: The ID of the Bet in the Database
	 */
	public $id;

	/**
	 * @var User: The User that initiated this bet
	 */
	public $user;

	/**
	 * @var Match: The match on which was bet
	 */
	public $match;

	/**
	 * @var int: The score bet on the home team
	 */
	public $homeScore;

	/**
	 * @var int: The score bet on the away team
	 */
	public $awayScore;

	/**
	 * Bet constructor.
	 * @SuppressWarnings functionMaxParameters
	 * @param int $id: The bet's ID in the database
	 * @param User $user: The user that submitted this bet
	 * @param Match $match: The match on which was bet
	 * @param int $homeScore: The score bet on the home team
	 * @param int $awayScore: The score bet on the away team
	 */
	public function __construct(int $id,
								User $user,
								Match $match,
								int $homeScore,
								int $awayScore) {
		$this->id = $id;
		$this->user = $user;
		$this->match = $match;
		$this->homeScore = $homeScore;
		$this->awayScore = $awayScore;
	}

	/**
	 * Defines the table name for the Bet Class.
	 * @return string: 'bets'
	 */
	public static function tableName(): string {
		return "bets";
	}

	/**
	 * Generates a Bet object from the data of a row in the bets table
	 * @param mysqli $db: The database connection to use for further queries
	 * @param array $row: The row to convert into a Bet object
	 * @return Model: The generated Bet object
	 * @SuppressWarnings docBlocks
	 */
	public static function fromRow(mysqli $db, array $row) : Model {
		/** @noinspection PhpParamsInspection */
		return new Bet(
			(int)$row["id"],
			(new Authenticator($db))->getUserFromId((int)$row["user_id"]),
			Match::fromId($db, (int)$row["match_id"]),
			(int)$row["home_score"],
			(int)$row["away_score"]
		);
	}

	/**
	 * Retrieves a Match based on a Match ID and a User ID
	 * @param mysqli $db: The database connection to use
	 * @param int $userId: The User ID to look for
	 * @param int $matchId: The Match ID to look for
	 * @return Model|null: The Bet object found for the Match, or null if no
	 *                     match was found.
	 */
	public static function fromMatchAndUserId(mysqli $db,
											int $matchId,
											int $userId
											) : ? Model {
		$stmt = $db->prepare(
			"SELECT * FROM bets WHERE match_id=? AND user_id=?;"
		);
		$stmt->bind_param("ii", $matchId, $userId);
		$stmt->execute();
		$results = $stmt->get_result();

		if ($results->num_rows !== 1) {
			return null;
		} else {
			$data = $results->fetch_array(MYSQLI_ASSOC);
			return BET::fromRow($db, $data);
		}
	}

	/**
	 * Fetches all bets on a specific match
	 * @param mysqli $db: The Database connection to use
	 * @param int $matchId: The Match ID to search for
	 * @return array: An array of Bet object that are for this Match
	 */
	public static function getAllForMatch(mysqli $db, int $matchId) : array {
		$stmt = $db->prepare("SELECT * FROM bets WHERE match_id=?;");
		$stmt->bind_param("i", $matchId);
		$stmt->execute();
		$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

		$bets = [];
		foreach ($results as $result) {
			array_push($bets, Bet::fromRow($db, $result));
		}
		return $bets;
	}

	/**
	 * Evaluates the points of the bet once a match is finished.
	 * Points are granted as follows:
	 *     - 4 Points for the exactly correct result
	 *     - 3 Points for the correct goal difference, 2 for draws
	 *     - 2 Points for the correct winner
	 * Additionally, a bonus point is granted if you have at least one of the
	 * two scores correct.
	 * @return int: The amount of points the user earned with this bet, for
	 *              a maximum of 5.
	 */
	public function evaluate() : int {
		if (!$this->match->hasStarted()) {
			return 0;
		} else {
			$actualHome = $this->match->homeFtScore;
			$actualAway = $this->match->awayFtScore;

			// Bonus points for at least one correct score
			if ($actualHome === $this->homeScore ||
				$actualAway === $this->awayScore) {
				$bonus = 1;
			} else {
				$bonus = 0;
			}

			if ($actualHome === $this->homeScore &&
				$actualAway === $this->awayScore) {
				return 4 + $bonus;  // Exact Hit

			} elseif (($actualHome - $actualAway) ===
				($this->homeScore - $this->awayScore)) {
				if ($actualHome === $actualAway) {
					return 2 + $bonus;  // Correct Goal Difference (Draw)
				} else {
					return 3 + $bonus;  // Correct goal difference
				}

			} elseif (
				($actualHome === $actualAway &&
					$this->awayScore === $this->homeScore) ||
				($actualHome > $actualAway &&
					$this->homeScore > $this->awayScore) ||
				($actualHome < $actualAway &&
					$this->homeScore < $this->awayScore)) {
				return 2 + $bonus;  // Correct Winner

			} else {
				return $bonus;  // Nothing Special
			}
		}
	}

	/**
	 * @return array: The array representation of the Bet
	 */
	public function toArray() : array {
		return [
			"id" => $this->id,
			"home_score" => $this->homeScore,
			"away_score" => $this->awayScore,
			"match" => $this->match->toArray()
		];
	}
}