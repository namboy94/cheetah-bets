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

use cheetah\Bet;
use cheetah\Goal;
use cheetah\Match;
use cheetah\Player;
use cheetah\Team;
use PHPUnit\Framework\TestCase;
use welwitschi\User;


/**
 * Class ArrayRepresentationTest
 * Tests the array representation of the models
 */
class ArrayRepresentationTest extends TestCase {

	/**
	 * @var Team: The Home team
	 */
	private $homeTeam;

	/**
	 * @var Team: The Away Team
	 */
	private $awayTeam;

	/**
	 * @var Match: The Match
	 */
	private $match;

	/**
	 * @var Player: The Goal Scorer
	 */
	private $player;

	/**
	 * @var Player: The Goal
	 */
	private $goal;

	/**
	 * @var Bet: The Bet
	 */
	private $bet;

	/**
	 * @var User: The User
	 */
	private $user;


	/**
	 * Initializes a couple of models
	 */
	public function setUp() {

		$db = new mysqli(
			"localhost",
			"phpunit",
			getenv("TEST_DB_PASS"), // Uses environment variable
			"cheetah_bets_test");
		$db->close();

		$this->user = new User($db, 1, "U", "u@u.com", "AAA", "confirmed");

		$this->homeTeam = new Team(1, "AAA", "AA", "A", "a");
		$this->awayTeam = new Team(2, "BBB", "BB", "B", "b");
		$this->match = new Match(1, $this->homeTeam, $this->awayTeam,
			0, 0, 1, 0, 1, "2000-01-01T00:00:00Z", true);
		$this->player = new Player(1, $this->homeTeam, "X");
		$this->goal =
			new Goal(1, $this->match, $this->player, 60, 0, 1, false, false);
		$this->bet = new Bet(1, $this->user, $this->match, 0, 2);
	}

	/**
	 * Tests the array representation of the bet
	 */
	public function testBetRepresentation() {
		$repr = $this->bet->toArray();
		$this->assertEquals($repr["id"], 1);
		$this->assertEquals($repr["match"], $this->match->toArray());
		$this->assertEquals($repr["home_score"], 0);
		$this->assertEquals($repr["away_score"], 2);
	}

	/**
	 * Tests the array representation of the goal
	 */
	public function testGoalRepresentation() {
		$repr = $this->goal->toArray();
		$this->assertEquals($repr["id"], 1);
		$this->assertEquals($repr["match"], $this->match->toArray());
		$this->assertEquals($repr["player"], $this->player->toArray());
		$this->assertEquals($repr["minute"], 60);
		$this->assertEquals($repr["home_score"], 0);
		$this->assertEquals($repr["away_score"], 1);
		$this->assertEquals($repr["owngoal"], false);
		$this->assertEquals($repr["penalty"], false);
	}

	/**
	 * Tests the array representation of the goal
	 */
	public function testMatchRepresentation() {
		$repr = $this->match->toArray();
		$this->assertEquals($repr["id"], 1);
		$this->assertEquals($repr["home_team"], $this->homeTeam->toArray());
		$this->assertEquals($repr["away_team"], $this->awayTeam->toArray());
		$this->assertEquals($repr["home_ht_score"], 0);
		$this->assertEquals($repr["away_ht_score"], 0);
		$this->assertEquals($repr["home_ft_score"], 1);
		$this->assertEquals($repr["away_ft_score"], 0);
		$this->assertEquals($repr["matchday"], 1);
		$this->assertEquals($repr["kickoff"], "2000-01-01T00:00:00Z");
		$this->assertEquals($repr["finished"], true);
		$this->assertEquals($repr["started"], true);
	}

	/**
	 * Tests the array representation of the player
	 */
	public function testPlayerRepresentation() {
		$repr = $this->player->toArray();
		$this->assertEquals($repr["id"], 1);
		$this->assertEquals($repr["name"], "X");
		$this->assertEquals($repr["team"], $this->homeTeam->toArray());
	}

	/**
	 * Tests the array representation of the team
	 */
	public function testTeamRepresentation() {
		$repr = $this->homeTeam->toArray();
		$this->assertEquals($repr["id"], 1);
		$this->assertEquals($repr["name"], "AAA");
		$this->assertEquals($repr["shortname"], "AA");
		$this->assertEquals($repr["abbreviation"], "A");
		$this->assertEquals($repr["icon"], "a");
	}
}