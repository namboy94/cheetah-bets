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
    update_db_matches(data, db)
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

        if not team["TeamId"] in current_data:

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
                         "(id, name, shortname, abbreviation, icon) "
                         "VALUES (%s, %s, %s, %s, %s);",
                         (int(team["TeamId"]),
                          teamname,
                          shortname,
                          abbreviation,
                          team["TeamIconUrl"]))
            committed = True

    if committed:
        db.commit()


def get_current_teams(db):
    """
    Retrieves the IDs of the teams currently present in the database
    :param db: The database connection
    :return: A list of team IDs currently in the database
    """

    stmt = db.cursor()
    stmt.execute("SELECT id FROM teams")
    current_data = stmt.fetchall()

    team_names = []
    for team in current_data:
        team_names.append(team[0])

    return team_names


def update_db_matches(data, db):
    """
    Updates the matches in the database
    :param data: The data from openligadb.de
    :param db: The database connection
    :return: None
    """

    for i, day in enumerate(data):

        for match in day:

            matchday = i + 1
            match_id = match["MatchID"]
                
            kickoff = match["MatchDateTimeUTC"]
            finished = match["MatchIsFinished"]

            home_id = match["Team1"]["TeamId"]
            away_id = match["Team2"]["TeamId"]

            if len(match["MatchResults"]) > 0:
                home_ht_score = match["MatchResults"][0]["PointsTeam1"]
                away_ht_score = match["MatchResults"][0]["PointsTeam2"]
            else:
                home_ht_score = None
                away_ht_score = None

            if len(match["MatchResults"]) > 1:
                home_ft_score = match["MatchResults"][1]["PointsTeam1"]
                away_ft_score = match["MatchResults"][1]["PointsTeam2"]
            else:
                home_ft_score = home_ht_score
                away_ft_score = away_ht_score

            sql = "INSERT INTO matches " \
                  "(id, home_id, away_id, matchday, " \
                  "home_ht_score, away_ht_score, " \
                  "home_ft_score, away_ft_score, " \
                  "kickoff, finished) " \
                  "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s) " \
                  "ON DUPLICATE KEY UPDATE " \
                  "home_ht_score=%s, away_ht_score=%s, " \
                  "home_ft_score=%s, away_ft_score=%s, " \
                  "kickoff=%s, finished=%s"

            variables = (match_id, home_id, away_id, matchday,
                         home_ht_score, away_ht_score,
                         home_ft_score, away_ft_score,
                         kickoff, finished,
                         home_ht_score, away_ht_score,
                         home_ft_score, away_ft_score,
                         kickoff, finished)

            stmt = db.cursor()
            stmt.execute(sql, variables)

    db.commit()


def update_db_goals(data, db):
    players = []

    for day in data:
        for match in day:

            match_id = match["MatchID"]
            goals = match["Goals"]

            for goal in goals:

                goal_id = goal["GoalID"]
                player_id = goal["GoalGetterID"];
                home_score = goal["ScoreTeam1"]
                away_score = goal["ScoreTeam2"]
                minute = goal["MatchMinute"]
                penalty = goal["IsPenalty"]
                owngoal = goal["IsOwnGoal"]

                invalid = False
                params = (
                goal_id, match_id, player_id, home_score, away_score,
                minute, penalty, owngoal)
                for param in params:
                    if param is None:
                        invalid = True
                if invalid:
                    continue

                players.append((goal["GoalGetterID"], goal["GoalGetterName"]))
                stmt = db.cursor()
                stmt.execute(
                    "REPLACE INTO goals (id, match_id, scorer, home_score, away_score, minute, penalty, owngoal)" \
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
        stmt.execute(
            "REPLACE INTO scorers (id, name, goals, penalties, owngoals)" \
            "VALUES (%s, %s, %s, %s, %s);",
            (player_id, player_name, goals, penalties, owngoals))

    db.commit()



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
