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
use cheetah\SchemaCreator;
use PHPUnit\Framework\TestCase;
use cheetah\SeasonManager;


/**
 * Class SeasonManagerTest
 * Tests for the SeasonManager class
 */
class SeasonManagerTest extends TestCase {

	/**
	 * @var mysqli: The database connection to use
	 */
	private static $db;

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
	 * Tests retrieving the last match day
	 */
	public function testGettingMaxMatchday() {
		$seasonManager = new SeasonManager(self::$db);
		$this->assertEquals($seasonManager->getMaxMatchday(), 34);
	}

	/**
	 * Tests retrieving the current match day
	 */
	public function testGettingCurrentMatchday() {
		$seasonManager = new SeasonManager(self::$db);
		$this->assertEquals($seasonManager->getCurrentMatchday(), 34);

		self::$db->query("UPDATE matches SET finished=0 WHERE matchday > 10");
		$this->assertEquals($seasonManager->getCurrentMatchday(), 11);
	}

}