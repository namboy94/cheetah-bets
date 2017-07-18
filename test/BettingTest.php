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

use PHPUnit\Framework\TestCase;
use cheetah\SchemaCreator;
use cheetah\Match;
use cheetah\Bet;
use cheetah\BetManager;
use welwitschi\Authenticator;
use welwitschi\User;


/**
 * Class that tests the creation of the database tables required for the
 * bets library to work.
 */
final class BettingTest extends TestCase {

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
	 * @var BetManager: A BetManager object
	 */
	private $betManager;

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

		$authenticator = new Authenticator(self::$db);
		self::assertTrue($authenticator->createUser("A", "A", "A"));
		self::assertTrue($authenticator->createUser("B", "B", "B"));

		self::$userOne = $authenticator->getUserFromId(1);
		self::$userTwo = $authenticator->getUserFromId(2);
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
		$this->betManager = new BetManager(self::$db);
		self::$db->query("DELETE FROM bets;");
		self::$db->commit();
		$this->assertEquals(
			self::$db->query("SELECT * FROM bets")->num_rows, 0);
	}

	/**
	 * Tests two users placing bets.
	 */
	public function testPlacingBets() {

		$i = 0;
		$matches = Match::getAllForMatchday(self::$db, 1);
		foreach ($matches as $match) {

			// Override Time String to enable betting
			$match->kickoff = "3000-01-01T00:00:00Z";

			$this->assertTrue(
				$this->betManager->placeBetWithoutAuthentication(
					self::$userOne, $match, $i, $i + 1));
			$this->assertTrue(
				$this->betManager->placeBetWithoutAuthentication(
					self::$userTwo, $match, $i + 1, $i));
			$i++;
		}

		$betsOne = $this->betManager->getAllBetsForUser(self::$userOne);
		$betsTwo = $this->betManager->getAllBetsForUser(self::$userTwo);

		foreach ($betsOne as $bet) {
			$this->assertFalse(array_key_exists($bet->id, $betsTwo));
			$this->assertEquals($bet->homeScore, $bet->awayScore - 1);
		}
	}

	/**
	 * Tests placing a bet with negative values
	 */
	public function testBettingNegativeValues() {
		$matches = Match::getAllForMatchday(self::$db, 1);
		$match = array_pop($matches);

		// Override Time String to enable betting
		$match->kickoff = "3000-01-01T00:00:00Z";

		try {
			$this->betManager->placeBetWithoutAuthentication(
				self::$userOne, $match, -1, 0
			);
			$this->fail();
		} catch (InvalidArgumentException $e) {
			$this->assertEquals($e->getMessage(), "Negative Scores detected!");
		}

		try {
			$this->betManager->placeBetWithoutAuthentication(
				self::$userOne, $match, 0, -1
			);
			$this->fail();
		} catch (InvalidArgumentException $e) {
			$this->assertEquals($e->getMessage(), "Negative Scores detected!");
		}
	}

	/**
	 * Tests if the Login Authentication method works as intended
	 */
	public function testBettingUsingLoginMethod() {

		$this->assertTrue(
			self::$userOne->confirm(self::$userOne->confirmationToken));

		$matches = Match::getAllForMatchday(self::$db, 1);
		$match = array_pop($matches);

		// Override Time String to enable betting
		$match->kickoff = "3000-01-01T00:00:00Z";

		$this->assertFalse($this->betManager->placeBetWithLoginSession(
			self::$userOne, $match, 1, 2));

		$this->assertTrue(self::$userOne->login("A"));

		$this->assertTrue($this->betManager->placeBetWithLoginSession(
			self::$userOne, $match, 3, 4));

		$all = Bet::getAll(self::$db);
		$bet = array_pop($all);

		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->homeScore, 3);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->awayScore, 4);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->match->id, $match->id);
	}

	/**
	 * Tests placing a bet using the API
	 */
	public function testBettingUsingApi() {
		$apiKey = self::$userOne->generateNewApiKey();

		$matches = Match::getAllForMatchday(self::$db, 1);
		$match = array_pop($matches);

		// Override Time String to enable betting
		$match->kickoff = "3000-01-01T00:00:00Z";

		$this->assertFalse($this->betManager->placeBetWithApiKey(
			self::$userOne, "InvalidApiKey", $match, 1, 2));

		$this->assertTrue($this->betManager->placeBetWithApiKey(
			self::$userOne, $apiKey, $match, 3, 4));

		$all = Bet::getAll(self::$db);
		$bet = array_pop($all);

		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->homeScore, 3);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->awayScore, 4);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->match->id, $match->id);
	}

	/**
	 * Tests betting on a match that had already starte
	 */
	public function testBettingOnMatchWhichHasStarted() {

		$matches = Match::getAllForMatchday(self::$db, 1);
		$match = array_pop($matches);

		$this->assertFalse($this->betManager->placeBetWithoutAuthentication(
			self::$userOne, $match, 1, 2));

		// Override Time String to enable betting
		$match->kickoff = "3000-01-01T00:00:00Z";

		$this->assertTrue($this->betManager->placeBetWithoutAuthentication(
			self::$userOne, $match, 3, 4));

		$all = Bet::getAll(self::$db);
		$bet = array_pop($all);

		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->homeScore, 3);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->awayScore, 4);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->match->id, $match->id);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertTrue($bet->match->hasStarted());
	}

	/**
	 * Tests fetching all bets for a match
	 */
	public function testGettingBetsForMatch() {
		$matches = Match::getAllForMatchday(self::$db, 1);
		$match = array_pop($matches);
		// Override Time String to enable betting
		$match->kickoff = "3000-01-01T00:00:00Z";

		$bets = Bet::getAllForMatch(self::$db, $match->id);
		$this->assertEquals(count($bets), 0);

		$this->assertTrue($this->betManager->placeBetWithoutAuthentication(
			self::$userOne, $match, 3, 4));
		$this->assertTrue($this->betManager->placeBetWithoutAuthentication(
			self::$userTwo, $match, 10, 5));

		$bets = Bet::getAllForMatch(self::$db, $match->id);
		$this->assertEquals(count($bets), 2);
		$this->assertEquals($bets[0]->user->id, self::$userOne->id);
	}

	/**
	 * Tests retrieving a bet for a user and match
	 */
	public function testGettingBetsForMatchAndUser() {
		$matches = Match::getAllForMatchday(self::$db, 1);
		$match = array_pop($matches);
		// Override Time String to enable betting
		$match->kickoff = "3000-01-01T00:00:00Z";

		$bet = Bet::fromMatchAndUserId(
			self::$db, $match->id, self::$userOne->id);
		$this->assertNull($bet);

		$this->assertTrue($this->betManager->placeBetWithoutAuthentication(
			self::$userOne, $match, 3, 4));

		$bet = Bet::fromMatchAndUserId(
			self::$db, $match->id, self::$userOne->id);
		$this->assertNotNull($bet);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->homeScore, 3);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($bet->awayScore, 4);

	}
}