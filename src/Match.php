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


/**
 * Class Match
 * Models a match from the matches table
 * @package cheetah
 */
class Match {

	/**
	 * Match constructor.
	 * @SuppressWarnings functionMaxParameters
	 * @param int $id : The ID of the match in the database
	 * @param Team $homeTeam : The Home team
	 * @param Team $awayTeam : The Away team
	 * @param int $homeHtScore: The Home team score at half time
	 * @param int $awayHtScore: The Away team score at half time
	 * @param int $homeFtScore: The full-time/current score of the home team
	 * @param int $awayFtScore: The full-time/current score of the home team
	 * @param int $matchday : The matchday on which this match was held
	 * @param string $kickoff : The kickoff date and time
	 * @param bool $finished : Indicates if the match is
	 *                         already finished or not
	 */
	public function __construct(int $id,
								Team $homeTeam,
								Team $awayTeam,
								int $homeHtScore,
								int $awayHtScore,
								int $homeFtScore,
								int $awayFtScore,
								int $matchday,
								string $kickoff,
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
	}

	/**
	 * Checks if a match has already started.
	 * @SuppressWarnings showTODOs
	 * @return bool: true if the match has started, false otherwise
	 */
	public function hasStarted() : bool {
		return false;  // TODO Implement
	}
}