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

require_once __DIR__ . "/../src/SchemaCreator.php";
use cheetah\Match;
use cheetah\Team;
use PHPUnit\Framework\TestCase;
use cheetah\SchemaCreator;


/**
 * Class that tests the creation of the database tables required for the
 * bets library to work.
 */
final class FetcherTest extends TestCase {

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
			getenv("TEST_DB_PASS") . " cheetah_bets_test");
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
	 * Tests if the python fetcher script works without any problems
	 */
	public function testIfFetcherScriptWorks() {

		$this->assertEquals(
			self::$db->query("SELECT * FROM teams;")->num_rows, 18
		);
		$this->assertEquals(
			self::$db->query("SELECT * FROM matches;")->num_rows, 306
		);
	}

	/**
	 * Tests fetching all teams and each team individually
	 */
	public function testFetchingTeams() {
		$teams = Team::getAll(self::$db);
		$this->assertEquals(count($teams), 18);
		foreach ($teams as $id => $team) {
			$individualTeam = Team::fromId(self::$db, $id);
			$this->assertNotNull($individualTeam);
			/** @noinspection PhpUndefinedFieldInspection */
			$this->assertEquals($team->name, $individualTeam->name);
		}
		$this->assertNull(Team::fromId(self::$db, -1));
	}

	/**
	 * Tests fetching the matches in the database
	 */
	public function testFetchingMatches() {
		$matches = Match::getAll(self::$db);
		$this->assertEquals(count($matches), 306);

		for ($i = 1; $i < 35; $i++) {
			$matchdayMatches = Match::getAllForMatchday(self::$db, $i);
			$this->assertEquals(count($matchdayMatches), 9);

			foreach ($matchdayMatches as $id => $match) {
				$individualMatch = Match::fromId(self::$db, $id);

				$this->assertNotNull($individualMatch);

				/** @noinspection PhpUndefinedFieldInspection */
				$this->assertEquals(
					$match->homeTeam->id, $individualMatch->homeTeam->id);
				/** @noinspection PhpUndefinedFieldInspection */
				$this->assertEquals(
					$match->awayTeam->id, $individualMatch->awayTeam->id);
				/** @noinspection PhpUndefinedFieldInspection */
				$this->assertEquals(
					$match->matchday, $individualMatch->matchday);

				$this->assertEquals(
					$match->homeTeam->id, $matches[$id]->homeTeam->id);
				$this->assertEquals(
					$match->awayTeam->id, $matches[$id]->awayTeam->id);
				$this->assertEquals(
					$match->matchday, $matches[$id]->matchday);
			}
		}

		$this->assertNull(Match::fromId(self::$db, -1));
	}
}