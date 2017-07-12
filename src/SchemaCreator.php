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
 * Class SchemaCreator
 * Handles creating database tables for this library
 * @package cheetah
 */
class SchemaCreator {

	/**
	 * SchemaCreator constructor. Automatically creates all
	 * database tables if they do not yet exist.
	 * @param mysqli $db
	 */
	public function __construct(mysqli $db) {
		$this->db = $db;

		//Create accounts table because of dependency on user_id foreign key
		new Authenticator($db);

		// The Order here is very important due to dependencies between
		// tables.
		$this->createTeamsTable();
		$this->createMatchesTable();
		$this->createBetsTable();
		$this->createPlayersTable();
		$this->createGoalsTable();
	}

	/**
	 * Creates the bets table:
	 *
	 * bets:
	 *
	 * | id | user_id | match_id | home_score | away_score |
	 */
	public function createBetsTable() {
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS bets(" .
			"    id INTEGER NOT NULL AUTO_INCREMENT," .
			"    user_id INTEGER NOT NULL," .
			"    match_id INTEGER NOT NULL," .
			"    home_score INTEGER NOT NULL," .
			"    away_score INTEGER NOT NULL," .
			"    PRIMARY KEY(id)," .
			"    FOREIGN KEY(user_id) REFERENCES accounts(id)" .
			"        ON DELETE CASCADE" .
			"        ON UPDATE CASCADE," .
			"    FOREIGN KEY(match_id) REFERENCES matches(id)" .
			"        ON DELETE CASCADE" .
			"        ON UPDATE CASCADE);"
		);
		$this->db->commit();
	}

	/**
	 * Creates the matches table:
	 *
	 * matches:
	 *
	 * | id | home_id | away_id | home_score | away_score | matchday |
	 * | kickoff | finished |
	 */
	public function createMatchesTable() {
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS matches(" .
			"    id INTEGER NOT NULL AUTO_INCREMENT," .
			"    home_id INTEGER NOT NULL," .
			"    away_id INTEGER NOT NULL," .
			"    matchday INTEGER NOT NULL," .
			"    kickoff VARCHAR(255)," .
			"    finished BOOLEAN," .
			"    PRIMARY KEY(id)," .
			"    FOREIGN KEY(home_id) REFERENCES teams(id)" .
			"        ON DELETE CASCADE" .
			"        ON UPDATE CASCADE," .
			"    FOREIGN KEY(away_id) REFERENCES teams(id)" .
			"        ON DELETE CASCADE" .
			"        ON UPDATE CASCADE);"
		);
		$this->db->commit();
	}

	/**
	 * Creates the teams table:
	 *
	 * teams:
	 *
	 * | id | name | shortname | abbreviation | icon |
	 */
	public function createTeamsTable() {
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS teams(" .
			"    id INTEGER NOT NULL AUTO_INCREMENT," .
			"    name VARCHAR(128) NOT NULL," .
			"    shortname VARCHAR(128) NOT NULL," .
			"    abbreviation VARCHAR(128) NOT NULL," .
			"    icon VARCHAR(255)," .
			"    PRIMARY KEY(id, name, shortname, abbreviation));"
		);
		$this->db->commit();
	}

	/**
	 * Creates the goals table:
	 *
	 * goals:
	 *
	 * | id | match_id | player_id | minute | home_score | away_score |
	 * | penalty | own_goal |
	 */
	public function createGoalsTable() {
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS goals(" .
			"    id INTEGER NOT NULL AUTO_INCREMENT," .
			"    match_id INTEGER NOT NULL," .
			"    player_id INTEGER NOT NULL," .
			"    minute INTEGER NOT NULL," .
			"    home_score INTEGER NOT NULL," .
			"    away_score INTEGER NOT NULL," .
			"    penalty BOOLEAN NOT NULL," .
			"    own_goal BOOLEAN NOT NULL," .
			"    PRIMARY KEY(id)," .
			"    FOREIGN KEY(match_id) REFERENCES matches(id)" .
			"        ON DELETE CASCADE" .
			"        ON UPDATE CASCADE," .
			"    FOREIGN KEY(player_id) REFERENCES players(id)" .
			"        ON DELETE CASCADE" .
			"        ON UPDATE CASCADE);"
		);
		$this->db->commit();
	}

	/**
	 * Creates the players table:
	 *
	 * players:
	 *
	 * | id | team_id | name |
	 */
	public function createPlayersTable() {
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS players(" .
			"    id INTEGER NOT NULL AUTO_INCREMENT," .
			"    team_id INTEGER NOT NULL," .
			"    name VARCHAR(255)," .
			"    PRIMARY KEY(id)," .
			"    FOREIGN KEY(team_id) REFERENCES teams(id)" .
			"        ON DELETE CASCADE" .
			"        ON UPDATE CASCADE);"
		);
		$this->db->commit();
	}
}
