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
 * Class Match
 * Models a match from the matches table
 * @package cheetah
 */
class Match extends Model {

	/**
	 * @var int: The ID of the Match in the database
	 */
	public $id;

	/**
	 * @var Team: The Home Team
	 */
	public $homeTeam;

	/**
	 * @var Team: The Away Team
	 */
	public $awayTeam;

	/**
	 * @var int: The Home Team's half time score
	 */
	public $homeHtScore;

	/**
	 * @var int: The Away Team's half time score
	 */
	public $awayHtScore;

	/**
	 * @var int: The Home Team's full time / current score
	 */
	public $homeFtScore;

	/**
	 * @var int: The Away Team's half time / current score
	 */
	public $awayFtScore;

	/**
	 * @var int: The match day of this match.
	 * A value between 1 and 34 for the Bundesliga, for example
	 */
	public $matchday;

	/**
	 * @var string: The kickoff time for this match
	 */
	public $kickoff;

	/**
	 * @var bool: Indicates if the match is finished or not
	 */
	public $finished;

	/**
	 * Match constructor.
	 * NULL values are detected and automatically converted to sensible
	 * defaults.
	 * @SuppressWarnings functionMaxParameters
	 * @param int $id : The ID of the match in the database
	 * @param Team $homeTeam : The Home team
	 * @param Team $awayTeam : The Away team
	 * @param int|null $homeHtScore: The Home team score at half time
	 * @param int|null $awayHtScore: The Away team score at half time
	 * @param int|null $homeFtScore: The full-time score of the home team
	 * @param int|null $awayFtScore: The full-time score of the away team
	 * @param int $matchday : The matchday on which this match was held
	 * @param string|null $kickoff : The kickoff date and time
	 * @param bool $finished : Indicates if the match is
	 *                         already finished or not
	 */
	public function __construct(int $id,
								Team $homeTeam,
								Team $awayTeam,
								? int $homeHtScore, // Can be NULL, do not cast
								? int $awayHtScore, // Can be NULL, do not cast
								? int $homeFtScore, // Can be NULL, do not cast
								? int $awayFtScore, // Can be NULL, do not cast
								int $matchday,
								? string $kickoff,  // Can be NULL, do not cast
								bool $finished) {
		$this->id = $id;
		$this->homeTeam = $homeTeam;
		$this->awayTeam = $awayTeam;
		$this->matchday = $matchday;
		$this->homeHtScore = $homeHtScore;
		$this->awayHtScore = $awayHtScore;
		$this->homeFtScore = $homeFtScore;
		$this->awayFtScore = $awayFtScore;
		$this->kickoff = $kickoff;
		$this->finished = $finished;

		// Detect NULL values
		$this->homeHtScore = ($homeHtScore === null) ? 0 : (int)$homeHtScore;
		$this->awayHtScore = ($awayHtScore === null) ? 0 : (int)$awayHtScore;
		$this->homeFtScore = ($homeFtScore === null) ?
			(int)$homeHtScore : (int)$homeFtScore;
		$this->awayFtScore = ($awayFtScore === null) ?
			(int)$awayHtScore : (int)$awayFtScore;
		$this->kickoff = ($kickoff === null) ? "---" : (string)$kickoff;
	}

	/**
	 * Defines the table name for the Model subclass.
	 * @return string: The table name
	 */
	public static function tableName(): string {
		return "matches";
	}

	/**
	 * Generate a Match object from a row of data from the
	 * database
	 * @param mysqli $db: The database connection used for further queries
	 * @param array $row: An associative array containing the match data
	 * @return Model: The generated Match object
	 * @SuppressWarnings docBlocks
	 */
	public static function fromRow(mysqli $db, array $row) : Model {
		/** @noinspection PhpParamsInspection */
		return new Match(
			(int)$row["id"],
			Team::fromId($db, (int)$row["home_id"]),
			Team::fromId($db, (int)$row["away_id"]),
			$row["home_ht_score"],  // Can be NULL => No Cast
			$row["away_ht_score"],  // Can be NULL => No Cast
			$row["home_ft_score"],  // Can be NULL => No Cast
			$row["away_ft_score"],  // Can be NULL => No Cast
			(int)$row["matchday"],
			$row["kickoff"],        // Can be NULL => No Cast
			(bool)$row["finished"]
		);
	}

	/**
	 * Fetches all matches on a given match day
	 * @param mysqli $db: The database connection used to fetch the data
	 * @param int $matchday: The matchday for which to fetch the Matches
	 * @return array: An array of matches with the match IDs as keys
	 */
	public static function getAllForMatchday(
		mysqli $db, int $matchday): array {

		$stmt = $db->prepare("SELECT * from matches WHERE matchday=?");
		$stmt->bind_param("i", $matchday);
		$stmt->execute();
		$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

		$matches = [];
		foreach ($results as $match) {
			$matches[(int)$match["id"]] = self::fromRow($db, $match);
		}
		return $matches;
	}

	/**
	 * Checks if a match has already started.
	 * @return bool: true if the match has started, false otherwise
	 */
	public function hasStarted() : bool {
		$startTime = strtotime($this->kickoff);
		$currentTime = time();
		return $currentTime > $startTime;
	}

	/**
	 * @return array: The array representation of the Match
	 */
	public function toArray() : array {
		return [
			"id" => $this->id,
			"home_team" => $this->homeTeam->toArray(),
			"away_team" => $this->awayTeam->toArray(),
			"home_ht_score" => $this->homeHtScore,
			"away_ht_score" => $this->awayHtScore,
			"home_ft_score" => $this->homeFtScore,
			"away_ft_score" => $this->awayFtScore,
			"matchday" => $this->matchday,
			"kickoff" => $this->kickoff,
			"finished" => $this->finished,
			"started" => $this->hasStarted()
		];
	}
}