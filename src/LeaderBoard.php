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
use welwitschi\Authenticator;

/**
 * Class LeaderBoard
 * This class calculates the points for the individual users' bets and orders
 * them accordingly
 * @package cheetah
 */
class LeaderBoard {

	/**
	 * LeaderBoard constructor.
	 * @param mysqli $db: The Database connection to use
	 */
	public function __construct(mysqli $db) {
		$this->db = $db;
	}

	/**
	 * Generates the ranking.
	 * @return array: the ranking, which is represented by an array with
	 *                the positions as keys.
	 *                The values are also arrays/lists consisting of the User
	 *                and the current points of that user
	 * @SuppressWarnings docBlocks
	 */
	public function generateRanking() : array {
		$users = (new Authenticator($this->db))->getAllUsers();
		$betManager = new BetManager($this->db);

		$unsortedRanking = [];

		foreach ($users as $user) {
			$bets = $betManager->getAllBetsForUser($user);
			$points = 0;
			foreach ($bets as $bet) {
				/** @noinspection PhpUndefinedMethodInspection */
				$points += $bet->evaluate();
			}
			array_push($unsortedRanking, [$user, $points]);
		}

		return $this->sortRanking($unsortedRanking);
	}

	/**
	 * Sorts the unsorted leaderboard
	 * @param array $array: The unsorted leaderboard
	 * @return array: The sorted leaderboard
	 * @SuppressWarnings functionInsideLoop
	 */
	public function sortRanking(array $array) : array {

		$i = 1;
		$sorted = [];
		while (count($array) > 0) {
			$highest = -1;
			$highestIndex = -1;
			foreach ($array as $index => $user) {
				if ($highest < $user[1]) {
					$highest = $user[1];
					$highestIndex = $index;
				}
			}
			$sorted[$i] = $array[$highestIndex];
			$i++;
			unset($array[$highestIndex]);
		}
		return $sorted;
	}

}