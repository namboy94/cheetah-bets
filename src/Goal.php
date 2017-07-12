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
 * Class Goal
 * Models a goal from the goals table
 * @package cheetah
 */
class Goal {

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

}