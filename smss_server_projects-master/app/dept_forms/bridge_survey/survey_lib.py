#!/var/www/mthsc/common/venv/bin/python3

import os
import sys
import MySQLdb

sys.path.append("/var/www/mthsc/common") #delete/comment out when running on apache server
from common_lib import commonFunctions

class survey_lib(commonFunctions):

    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary for the syllabus_rep database
    #
    def get_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)


    #=============================================
    # accepts: str/int info from form
    # returns: true if the responses is saved, false otherwise
    #
    def save_response(self,form):
        cursor = self.get_cursor()

        sql = "INSERT INTO bridge_course_survey_responses (description, participation, MATH_853_info, MATH_821_info, MATH_860_info, MATH_810_info, MATH_804_info, MATH_853_topics, MATH_821_topics, MATH_860_topics, MATH_810_topics, MATH_804_topics, lectures, textbooks, time_management, reviewing_notes, knowledge, peers, grad_program, background, participation_other, time, funding, timing, not_aware, not_participate_other, suggestions, email)  VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
        cursor.execute(sql, (form.get('description'),
                             form.get('participation'),
                             form.get('MATH_853_info'),
                             form.get('MATH_821_info'),
                             form.get('MATH_860_info'),
                             form.get('MATH_810_info'),
                             form.get('MATH_804_info'),
                             form.get('MATH_853_topics'),
                             form.get('MATH_821_topics'),
                             form.get('MATH_860_topics'),
                             form.get('MATH_810_topics'),
                             form.get('MATH_804_topics'),
                             form.get('lectures'),
                             form.get('textbooks'),
                             form.get('time_management'),
                             form.get('reviewing_notes'),
                             form.get('knowledge'),
                             form.get('peers'),
                             form.get('grad_program'),
                             form.get('background'),
                             form.get('participation_other'),
                             form.get('time'),
                             form.get('funding'),
                             form.get('timing'),
                             form.get('not_aware'),
                             form.get('not_participate_other'),
                             form.get('suggestions'),
                             form.get('email')))

        return True


    #=============================================
    # accepts:
    # returns: list of dictionaries containing the stored data
    #
    def get_survey_data(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT response_id, description, participation, MATH_853_info, MATH_821_info, MATH_860_info, MATH_810_info, MATH_804_info, MATH_853_topics, MATH_821_topics, MATH_860_topics, MATH_810_topics, MATH_804_topics, lectures, textbooks, time_management, reviewing_notes, knowledge, peers, grad_program, background, participation_other, time, funding, timing, not_aware, not_participate_other, suggestions, email FROM bridge_course_survey_responses")

        return cursor.fetchall()
