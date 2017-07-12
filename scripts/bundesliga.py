#!/usr/bin/env python

""" Copyright Hermann Krumrey <hermann@krumreyh.com> 2017
    
    This file is part of cheetah-bets.
    
    cheetah-bets is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    
    cheetah-bets is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with cheetah-bets.  If not, see <http://www.gnu.org/licenses/>.
"""

import sys
import time
import json
import MySQLdb  # Python 3: pip install mysqlclient
import requests
import argparse


bundesliga_names = {
    "Bayern München": ("FC Bayern München", "FC Bayern", "FCB"),
    "Bayer 04 Leverkusen": ("Bayer 04 Leverkusen", "Leverkusen", "B04"),
    "TSG 1899 Hoffenheim": ("TSG 1899 Hoffenheim", "Hoffenheim", "TSG"),
    "Werder Bremen": ("SV Werder Bremen", "Bremen", "SVW"),
    "Hertha BSC": ("Hertha BSC Berlin", "Hertha", "BSC"),
    "VFB Stuttgart": ("VFB Stuttgart", "Stuttgart", "VFB"),
    "Hamburger SV": ("Hamburger SV", "Hamburg", "HSV"),
    "FC Augsburg": ("FC Augsburg", "Augsburg", "FCA"),
    "1. FSV Mainz 05": ("1. FSV Mainz 05", "Mainz", "M05"),
    "Hannover 96": ("Hannover 96", "Hannover", "H96"),
    "VFL Wolfsburg": ("VFL Wolfsburg", "Wolfsburg", "WOB"),
    "Borussia Dortmund": ("Borussia Dortmund", "Dortmund", "BVB"),
    "FC Schalke 04": ("FC Schalke 04", "Schalke", "S04"),
    "RB Leibzig": ("Red Bull Leibzig :)", "Red Bull :)", "RBL"),
    "SC Freiburg": ("SC Freiburg", "Freiburg", "SCF"),
    "Eintracht Frankfurt": ("Eintracht Frankfurt", "Frankfurt", "SGE"),
    "Borussia Mönchengladbach":
        ("Borussia Mönchengladbach", "Gladbach", "BMG"),
    "1. FC Köln": ("1. FC Köln'", "Köln", "KOE")
}


def parse_args():
    """
    Parses the command line arguments
    :return: A tuple consisting of the database username, password and name
    """

    parser = argparse.ArgumentParser()
    parser.add_argument("username", help="The username for the database")
    parser.add_argument("password", help="The password for the database")
    parser.add_argument("database", help="The database to use")
    args = parser.parse_args()
    return args.username, args.password, args.database


def connect_db():
    """
    Connects the database
    :return: The initialized database connection
    """
    username, password, database = parse_args()
    db = MySQLdb.connect("localhost", username, password, database)

    return db


def load_data(season='2017', league='bl1', matchday_amount=34):
    """
    This method downloads the currently available data from openligadb.de
    with default for the current Bundesliga season
    :param season: The season. 2017 indicates the 2017/18 season
    :param league: The league to fetch data for
    :param matchday_amount: The amount of matchdays the specified league has
    :return: A list of lists of json data retrieved from openligadb.de
    """

    base_url = "https://www.openligadb.de/api/getmatchdata/" + league + "/"
    matchdays = []
    data = json.loads(requests.get(base_url + season).text)

    for day in range(1, matchday_amount + 1):
        matchday = []
        for match in data:
            if match["Group"]["GroupOrderID"] == day:
                matchday.append(match)
        matchdays.append(matchday)

    return matchdays


def update_db():
    """
    Updates the database with newly fetched data
    :return: None
    """

    db = connect_db()
    data = load_data()

    update_db_teams(data, db)
    # update_db_matches(data, db)
    # update_db_goals(data, db)

    db.close()


def update_db_teams(data, db):
    """
    Updates the teams table in the database
    :param data: The openligadb data
    :param db: The database connection
    :return: None
    """

    current_data = get_current_teams(db)
    committed = False

    teams = []
    for match in data[0]:
        teams.append(match["Team1"])
        teams.append(match["Team2"])

    for team in teams:

        if not team["TeamName"] in current_data:

            try:
                team_names = bundesliga_names[team["TeamName"]]
                teamname, shortname, abbreviation = team_names
            except KeyError:
                teamname = team["TeamName"]
                if team["ShortName"]:
                    shortname = team["ShortName"]
                else:
                    shortname = team["TeamName"]
                abbreviation = teamname[0:3].upper()

            stmt = db.cursor()
            stmt.execute("INSERT INTO teams "
                         "(name, shortname, abbreviation, icon) "
                         "VALUES (%s, %s, %s, %s);",
                         (teamname,
                          shortname,
                          abbreviation,
                          team["TeamIconUrl"]))
            committed = True

    if committed:
        db.commit()


def get_current_teams(db):
    """
    Retrieves the names of the teams currently present in the database
    :param db: The database connection
    :return: A list of team names currently in the database
    """

    stmt = db.cursor()
    stmt.execute("SELECT name FROM teams")
    current_data = stmt.fetchall()

    team_names = []
    for team in current_data:
        team_names.append(team[0])

    return team_names


def update_db_goals(data, db):

    players = []

    for day in data:
        for match in day:

            match_id = match["MatchID"]
            goals = match["Goals"]

            for goal in goals:
                

                goal_id = goal["GoalID"]
                player_id = goal["GoalGetterID"];
                team_one_score = goal["ScoreTeam1"]
                team_two_score = goal["ScoreTeam2"]
                minute = goal["MatchMinute"]
                penalty = goal["IsPenalty"]
                owngoal = goal["IsOwnGoal"]

                invalid = False
                params = (goal_id, match_id, player_id, team_one_score, team_two_score, minute, penalty, owngoal)
                for param in params:
                    if param is None:
                        invalid = True
                if invalid:
                    continue

                players.append((goal["GoalGetterID"], goal["GoalGetterName"]))
                stmt = db.cursor()
                stmt.execute("REPLACE INTO goals (id, match_id, scorer, team_one_score, team_two_score, minute, penalty, owngoal)"\
                             "VALUES (%s, %s, %s, %s, %s, %s, %s, %s);",
                             params)

    db.commit()
    goaldata = get_goal_data(db)

    for player in players:

        player_id = player[0]

        if player_id == 0:
            player_name = "Unknown"
        else:
            player_name = player[1]

        player_goals = goaldata[player_id]

        goals = len(player_goals)
        penalties = len(list(filter(lambda x: x[0] == True, player_goals)))
        owngoals = len(list(filter(lambda x: x[1] == True, player_goals)))

        stmt = db.cursor()
        stmt.execute("REPLACE INTO scorers (id, name, goals, penalties, owngoals)"\
                     "VALUES (%s, %s, %s, %s, %s);",
                     (player_id, player_name, goals, penalties, owngoals))

    db.commit()


def update_db_matches(data, db):

    committed = False
    current_data = get_current_matches(db)

    for i, day in enumerate(data):

        for match in day:

            matchday = i + 1
            match_id = match["MatchID"]

            if match["Location"] is not None:
                match_location_city = match["Location"]["LocationCity"]
                match_location_stadium = match["Location"]["LocationStadium"]
            else:
                match_location_city = "Unknown"
                match_location_stadium = "Unknown"

            match_time = match["MatchDateTimeUTC"]
            match_finished = match["MatchIsFinished"]
            team_one = match["Team1"]["TeamId"]
            team_two = match["Team2"]["TeamId"]

            if len(match["MatchResults"]) > 0:
                team_one_halftime_points = \
                    match["MatchResults"][0]["PointsTeam1"]
                team_two_halftime_points = \
                    match["MatchResults"][0]["PointsTeam2"]
            else:
                team_one_halftime_points = -1
                team_two_halftime_points = -1

            if len(match["MatchResults"]) > 1:
                team_one_points = match["MatchResults"][1]["PointsTeam1"]
                team_two_points = match["MatchResults"][1]["PointsTeam2"]
            else:
                team_one_points = -1
                team_two_points = -1

            last_update = match["LastUpdateDateTime"]

            sql = ""
            variables = (match_id, matchday, match_location_city,
                         match_location_stadium, match_time, match_finished,
                         team_one, team_two, team_one_halftime_points,
                         team_two_halftime_points, team_one_points,
                         team_two_points, str(last_update))

            if match_id not in current_data:
                sql = "INSERT INTO matches (id, matchday, city, stadium, "\
                      "matchtime, finished, team_one, team_two, team_one_ht, "\
                      "team_two_ht, team_one_ft, team_two_ft, updated) "\
                      "VALUES (%s, %s, %s, %s, %s, "\
                      "%s, %s, %s, %s, %s, %s, %s, %s);"

            elif current_data[match_id] != last_update:
                sql = "UPDATE matches SET id=%s, matchday=%s, city=%s, "\
                      "stadium=%s, matchtime=%s, finished=%s, team_one=%s, "\
                      "team_two=%s, team_one_ht=%s, team_two_ht=%s, "\
                      "team_one_ft=%s, team_two_ft=%s, updated=%s WHERE id=%s"
                variables += (match_id,)

            if sql != "":

                stmt = db.cursor()
                stmt.execute(sql, variables)
                committed = True
    if committed:
        db.commit()

def get_current_matches(db):
    stmt = db.cursor()
    stmt.execute("SELECT id, updated FROM matches")
    current_data = stmt.fetchall()

    formatted_data = {}

    for match in current_data:
        formatted_data[match[0]] = match[1]

    return formatted_data



def get_goal_data(db):
    stmt = db.cursor()
    stmt.execute("SELECT scorer, penalty, owngoal FROM goals")
    current_data = stmt.fetchall()

    goals = {}
    for goal in current_data:
        goals[goal[0]] = []
    for goal in current_data:
        goals[goal[0]].append((goal[1], goal[2]))

    return goals





if __name__ == "__main__":
    try:
        update_db()
        print("Update: " + str(time.time()))
    except Exception as e:
        print(str(e))
        raise e
