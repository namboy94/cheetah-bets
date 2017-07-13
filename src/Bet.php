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
	private $id;

	/**
	 * @var User: The User that initiated this bet
	 */
	private $user;

	/**
	 * @var Match: The match on which was bet
	 */
	private $match;

	/**
	 * @var int: The score bet on the home team
	 */
	private $homeScore;

	/**
	 * @var int: The score bet on the away team
	 */
	private $awayScore;

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
}