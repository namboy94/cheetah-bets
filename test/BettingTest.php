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
	private static $betManager;

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
		self::$betManager = new BetManager(self::$db);
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
	 * Tests two users placing bets.
	 */
	public function testPlacingBets() {

		$i = 0;
		$matches = Match::getAllForMatchday(self::$db, 1);
		foreach ($matches as $match) {
			$this->assertTrue(
				self::$betManager->placeBet(
					self::$userOne, $match, $i, $i + 1));
			$this->assertTrue(
				self::$betManager->placeBet(
					self::$userTwo, $match, $i + 1, $i));
			$i++;
		}

		$betsOne = self::$betManager->getAllBetsForUser(self::$userOne);
		$betsTwo = self::$betManager->getAllBetsForUser(self::$userTwo);

		foreach ($betsOne as $bet) {
			$this->assertFalse(array_key_exists($bet->id, $betsTwo));
			$this->assertEquals($bet->homeScore, $bet->awayScore - 1);
			$this->assertFalse($bet->match->hasStarted());
		}

	}

}