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
use cheetah\LeaderBoard;
use PHPUnit\Framework\TestCase;
use cheetah\SchemaCreator;
use cheetah\BetManager;
use cheetah\Match;
use welwitschi\Authenticator;
use welwitschi\User;


/**
 * Class that tests the creation of the database tables required for the
 * bets library to work.
 */
final class LeaderBoardTest extends TestCase {

	/**
	 * @var mysqli: The database connection to use
	 */
	private static $db;

	/**
	 * @var User: A betting user
	 */
	private static $userOne;

	/**
	 * @var User: Another betting user
	 */
	private static $userTwo;

	/**
	 * @var User: And another one
	 */
	private static $userThree;

	/**
	 * @var BetManager: A BetManager object
	 */
	private static $betManager;

	/**
	 * @var LeaderBoard: A LeaderBoard object
	 */
	private $leaderBoard;

	/**
	 * Initializes the database and fills it with data
	 * @SuppressWarnings checkProhibitedFunctions
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$db = new mysqli(
			"localhost",
			"phpunit",
			getenv("TEST_DB_PASS"), // Uses environment variable
			"cheetah_bets_test");
		new SchemaCreator(self::$db);
		exec("python scripts/leaguegetter.py phpunit " .
			getenv("TEST_DB_PASS") . " cheetah_bets_test -s 2016");

		self::$betManager = new BetManager(self::$db);
		$authenticator = new Authenticator(self::$db);

		self::assertTrue($authenticator->createUser("A", "A", "A"));
		self::assertTrue($authenticator->createUser("B", "B", "B"));
		self::assertTrue($authenticator->createUser("C", "C", "C"));

		self::$userOne = $authenticator->getUserFromId(1);
		self::$userTwo = $authenticator->getUserFromId(2);
		self::$userThree = $authenticator->getUserFromId(3);
	}

	/**
	 * Deletes all generated database tables
	 */
	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::$db->query("DROP TABLE IF EXISTS " .
			"bets, goals, players, matches, teams, sessions, accounts");
		self::$db->commit();
		self::$db->close();
	}

	/**
	 * Deletes all bet entries before testing
	 */
	public function setUp() {
		parent::setUp();

		// In setup for constructor test coverage
		$this->leaderBoard = new LeaderBoard(self::$db);
		self::$db->query("DELETE FROM bets;");
		self::$db->commit();
		$this->assertEquals(
			self::$db->query("SELECT * FROM bets")->num_rows, 0);
	}

	/**
	 * Tests if the Leaderboard generates a correct ranking
	 */
	public function testLeaderboardGeneration() {

		$matches = Match::getAllForMatchday(self::$db, 1);
		$match = array_pop($matches);

		// Override Time String to enable betting
		$match->kickoff = "3000-01-01T00:00:00Z";

		$home = $match->homeFtScore;
		$away = $match->awayFtScore;

		$this->assertTrue(self::$betManager->placeBetWithoutAuthentication(
			self::$userOne, $match, $home + 1, $away + 1));

		$this->assertTrue(self::$betManager->placeBetWithoutAuthentication(
			self::$userTwo, $match, $home, $away + 10));

		$this->assertTrue(self::$betManager->placeBetWithoutAuthentication(
			self::$userThree, $match, $home, $away));

		$ranking = $this->leaderBoard->generateRanking();

		$this->assertEquals(count($ranking), 3);
		$this->assertEquals($ranking[1][0]->id, self::$userThree->id);
		$this->assertEquals($ranking[2][0]->id, self::$userOne->id);
		$this->assertEquals($ranking[3][0]->id, self::$userTwo->id);
		$this->assertEquals($ranking[1][1], 5);
		$this->assertEquals($ranking[2][1], 3);
		// userTwo's actual score is unknown

	}

	/**
	 * Tests the evaluation of bets
	 */
	public function testBetEvaluation() {

		$matches = Match::getAllForMatchday(self::$db, 1);
		$match = array_pop($matches);

		// Set some values that are known
		$match->homeFtScore = 0;
		$match->awayFtScore = 0;

		$bet = new Bet(1, self::$userOne, $match, 0, 0);

		$this->assertEquals($bet->evaluate(), 5);

		$bet->homeScore = 1;
		$bet->awayScore = 1;

		$this->assertEquals($bet->evaluate(), 2);

		$bet->homeScore = 1;
		$bet->awayScore = 0;

		$this->assertEquals($bet->evaluate(), 1);

		$bet->match->homeFtScore = 2;
		$bet->match->awayFtScore = 0;

		$this->assertEquals($bet->evaluate(), 3);

		$bet->homeScore = 0;
		$bet->awayScore = 3;

		$this->assertEquals($bet->evaluate(), 0);

	}

	/**
	 * Tests that bets with unfinished matches evaluate to 0
	 */
	public function testEvaluatingUnfinishedMatch() {

		$matches = Match::getAllForMatchday(self::$db, 1);
		$match = array_pop($matches);
		$match->homeFtScore = 1;
		$match->awayFtScore = 1;
		$match->finished = false;

		$bet = new Bet(1, self::$userOne, $match, 1, 1);

		$this->assertEquals($bet->evaluate(), 0);
		$bet->match->finished = true;
		$this->assertEquals($bet->evaluate(), 5);
	}
}