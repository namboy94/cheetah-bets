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


use welwitschi\User;

/**
 * Class Bet
 * Models a bet from the bets table
 * @package cheetah
 */
class Bet {

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

}