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

/**
 * Class that tests the creation of the database tables required for the
 * bets library to work.
 * @property mysqli db: The database connection used while testing.
 */
final class DatabaseTableCreationTest extends TestCase {

	/**
	 * Initializes the Database connection and deletes all
	 * database tables that will be created during testing
	 */
	public function setUp() {
		parent::setUp();
		$this->db = new mysqli(
			"localhost",
			"phpunit",
			getenv("TEST_DB_PASS"), // Uses environment variable
			"welwitschi_auth_test");
		//$this->db->query("DROP TABLE IF EXISTS " .
		//	"bets, goals, players, matches, teams, sessions, accounts");
		$this->db->commit();
	}

	/**
	 * Deletes any tables created during testing
	 */
	public function tearDown() {
		//$this->db->query("DROP TABLE IF EXISTS " .
		//	"bets, goals, players, matches, teams, sessions, accounts");
		$this->db->commit();
		$this->db->close();
		parent::tearDown();
	}

	/**
	 * Tests creating the database tables required byy cheetah-bets
	 */
	public function testCreatingTables() {

		$this->assertFalse($this->db->query("SELECT * FROM bets;"));
		$this->assertFalse($this->db->query("SELECT * FROM matches;"));
		$this->assertFalse($this->db->query("SELECT * FROM teams;"));
		$this->assertFalse($this->db->query("SELECT * FROM players;"));
		$this->assertFalse($this->db->query("SELECT * FROM goals;"));
		new SchemaCreator($this->db);
		$this->assertNotFalse($this->db->query("SELECT * FROM bets;"));
		$this->assertNotFalse($this->db->query("SELECT * FROM matches;"));
		$this->assertNotFalse($this->db->query("SELECT * FROM teams;"));
		$this->assertNotFalse($this->db->query("SELECT * FROM players;"));
		$this->assertNotFalse($this->db->query("SELECT * FROM goals;"));

	}
}