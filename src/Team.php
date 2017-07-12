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
 * Class Team
 * Models a team from the teams table
 * @package cheetah
 */
class Team {

	/**
	 * Team constructor.
	 * @param int $id: The team ID in the database
	 * @param string $name: The full name of the team, ex: FC Bayern München
	 * @param string $shortname: The shortform name of the team, ex: FC Bayern
	 * @param string $abbreviation: A 3-letter abbreviation, ex: FCB
	 * @param string $icon: Path to an icon file for the team
	 */
	public function __construct(int $id,
								string $name,
								string $shortname,
								string $abbreviation,
								string $icon) {
		$this->id = $id;
		$this->name = $name;
		$this->shortname = $shortname;
		$this->abbreviation = $abbreviation;
		$this->icon = $icon;
	}

}