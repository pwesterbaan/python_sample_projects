#!/var/www/mthsc/common/venv/bin/python3

import os
import sys
import MySQLdb

sys.path.append("/var/www/mthsc/common") #delete/comment out when running on apache server
from common_lib import commonFunctions

class vote_lib(commonFunctions):

    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary for the syllabus_rep database
    #
    def get_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)

    #=============================================
    # accepts: str username
    # returns: true if this person can vote; false if not
    #
    def is_eligible(self,username):
        cursor = self.get_cursor()

        sql = "SELECT COUNT(username) AS valid FROM cgsg_senators_voters WHERE username = '%s'"
        cursor.execute(sql, username)

        if cursor.fetchone()["valid"] == 0:
            return True
        else:
            return False

    #=============================================
    # accepts: str username
    #          list of votes
    #          str comments
    # returns: true if the responses is saved, false otherwise
    #
    def save_response(self,username, crunkleton, finney, sotherden):
        cursor = self.get_cursor()

        sql = "INSERT INTO cgsg_senators_responses (crunkleton, finney, sotherden)  VALUES (%s, %s, %s)"
        cursor.execute(sql, (crunkleton, finney, sotherden))

        # mark the user as having voted
        cursor.execute("INSERT INTO cgsg_senators_voters (username) VALUES (%s)", username)

        return True

    #=============================================
    # accepts: nothing
    # returns: stats for voting
    #
    def get_stats(self):
        cursor = self.get_cursor()

        sql = """
SELECT
COUNT(response_id) AS total_votes,
SUM(crunkleton) as crunkleton, SUM(finney) AS finney, SUM(sotherden) AS sotherden
FROM cgsg_senators_responses
"""
        cursor.execute(sql)

        return cursor.fetchone()
