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
 * Class Goal
 * Models a goal from the goals table
 * @package cheetah
 */
class Goal extends Model {

	/**
	 * @var int: The ID of the Goal object in the database
	 */
	public $id;

	/**
	 * @var Match: The match in which the goal occured
	 */
	public $match;

	/**
	 * @var Player: The player that shot this goal
	 */
	public $player;

	/**
	 * @var int: The minute in which the goal fell
	 */
	public $minute;

	/**
	 * @var int: The score of the home team after this goal
	 */
	public $homeScore;

	/**
	 * @var int: The score of the away team after this goal
	 */
	public $awayScore;

	/**
	 * @var bool: Indicates if this goal is a penalty or not
	 */
	public $penalty;

	/**
	 * @var bool: Indicates if the goal is an own goal or not
	 */
	public $owngoal;

	/**
	 * Goal constructor.
	 * @SuppressWarnings functionMaxParameters
	 * @param int $id: The ID of the goal
	 * @param Match $match: The match in which this goal took place
	 * @param Player $player: The player that scored this goal
	 * @param int $minute: The minute in which the goal fell
	 * @param int $homeScore: The Score of the Home team after this goal
	 * @param int $awayScore: The Score of the Away team after this goal
	 * @param bool $penalty: Indicates if the goal was a penalty or not
	 * @param bool $owngoal: Indicates if the goal was an own goal or not
	 */
	public function __construct(int $id,
								Match $match,
								Player $player,
								int $minute,
								int $homeScore,
								int $awayScore,
								bool $penalty,
								bool $owngoal) {
		$this->id = $id;
		$this->match = $match;
		$this->player = $player;
		$this->minute = $minute;
		$this->homeScore = $homeScore;
		$this->awayScore = $awayScore;
		$this->penalty = $penalty;
		$this->owngoal = $owngoal;
	}

	/**
	 * Defines the table name for the Goal class.
	 * @return string: 'goals'
	 */
	public static function tableName(): string {
		return "goals";
	}

	/**
	 * Generates a Goal object from a database row
	 * @param mysqli $db: The database to use for additional queries
	 * @param array $row: The row of data from the goals table
	 *                    as an associative array
	 * @return Model: The generated Goal object
	 * @SuppressWarnings docBlocks
	 */
	public static function fromRow(mysqli $db, array $row) : Model {
		/** @noinspection PhpParamsInspection */
		return new Goal(
			(int)$row["id"],
			Match::fromId($db, $row["match_id"]),
			Player::fromId($db, $row["player_id"]),
			(int)$row["minute"],
			(int)$row["home_score"],
			(int)$row["away_score"],
			(bool)$row["penalty"],
			(bool)$row["owngoal"]
		);
	}

	/**
	 * Retrieves all goals in a match
	 * @param mysqli $db: The database connection to use
	 * @param int $matchId: The Match ID to search for
	 * @return array: An array of goals that fell during the match
	 */
	public static function getFromMatchId(mysqli $db, int $matchId) : array {
		$stmt = $db->prepare(
			"SELECT * FROM goals WHERE match_id=? " .
			"ORDER BY minute ASC;");

		$stmt->bind_param("i", $matchId);
		$stmt->execute();
		$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

		$goals = [];
		foreach ($results as $result) {
			array_push($goals, Goal::fromRow($db, $result));
		}
		return $goals;
	}

	/**
	 * @return array: The array representation of the Goal
	 */
	public function toArray() : array {
		return [
			"id" => $this->id,
			"match" => $this->match->toArray(),
			"player" => $this->player->toArray(),
			"minute" => $this->minute,
			"penalty" => $this->penalty,
			"owngoal" => $this->owngoal,
			"home_score" => $this->homeScore,
			"away_score" => $this->awayScore,
		];
	}
}