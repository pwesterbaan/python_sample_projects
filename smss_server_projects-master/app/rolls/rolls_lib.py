#!/var/www/mthsc/common/venv/bin/python3

import datetime
import sys

import cx_Oracle
import MySQLdb

sys.path.append("/var/www/mthsc/common") #TODO delete/comment out when running on apache server
from common_lib import commonFunctions

class rolls_lib(commonFunctions):
    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary
    #
    def get_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)


    #=============================================
    # accepts: none
    # returns: True if the rolls were downloaded; False otherwise
    #
    def download_current_rolls(self):
        year = self.get_current_year()
        semester = self.get_current_semester().lower()
        
        term_code = self.get_term_code(semester,year)

        # get the current rolls from Banner
        banner_cursor = self.get_banner_cursor()

        banner_cursor.execute("SELECT term_code, xid, subject_code, course_number, section_number, grade FROM sis_mthsc_student_registration WHERE subject_code IN ('MATH', 'STAT','DSA') AND term_code = '{}' AND course_registration_stat_code NOT IN ('AU', 'AW', 'DD', 'DE', 'DN', 'DW', 'WD', 'WL', 'WW')".format(term_code))

        results = banner_cursor.fetchall()

        # store the rolls in our database

        cursor = self.get_cursor()

        # we remove the rolls for the current term
        cursor.execute("DELETE FROM rolls WHERE term_code = '{}'".format(term_code))

        # PW 2022-05-19: modified to insert all results simultaneously
        # for record in results:
            # cursor.execute("""INSERT INTO rolls (term_code, xid, subject_code, course_number, section_number, univ_letter_grade) VALUES ('{}', '{}', '{}', '{}', '{}', IFNULL('{}',""))""".format(record[0], record[1], record[2], record[3], record[4], record[5]))

        query="""INSERT INTO rolls (term_code, xid, subject_code, course_number, section_number, univ_letter_grade) VALUES (%s, %s, %s, %s, %s, IFNULL(%s,""))"""
        cursor.executemany(query,results)

        return True # we should be checking to see if everything succeeded


    #=============================================
    # accepts: none
    # returns: True if the rolls were downloaded; False otherwise
    #
    def download_full_rolls(self):
        year = self.get_current_year()
        semester = self.get_current_semester().lower()
        term_code = self.get_term_code(semester,year)

        # # get the current rolls from Banner
        banner_cursor = self.get_banner_cursor()

        banner_cursor.execute("SELECT term_code, xid, subject_code, course_number, section_number, grade, course_registration_stat_code FROM sis_mthsc_student_registration WHERE subject_code IN ('MATH', 'STAT', 'MTHS','DSA') AND term_code = '{}' ".format(term_code))

        results = banner_cursor.fetchall()

        # store the rolls in our database
        cursor = self.get_cursor()

        # we remove the rolls for the current term
        cursor.execute("DELETE FROM full_rolls WHERE term_code = '{}'".format(term_code))

        # PW 2022-05-19: modified to insert all results simultaneously
        # for record in results:
            # cursor.execute("""INSERT INTO full_rolls (term_code, xid, subject_code, course_number, section_number, reg_stat_code, univ_letter_grade) VALUES ('{}', '{}', '{}', '{}', '{}', '{}', IFNULL('{}',""))""".format(record[0], record[1], record[2], record[3], record[4], record[6], record[5]))

        query="""INSERT INTO full_rolls (term_code, xid, subject_code, course_number, section_number, reg_stat_code, univ_letter_grade) VALUES (%s, %s, %s, %s, %s, %s, IFNULL(%s,""))"""
        cursor.executemany(query,results)

        return True # we should be checking to see if everything succeeded


    #=============================================
    # accepts: none
    # returns: list of students in classes
    #
    def get_current_rolls(self):
        year = self.get_current_year()
        semester = self.get_current_semester().lower()

        term_code = self.get_term_code(semester,year)

        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM rolls WHERE term_code = '{}' ORDER BY term_code, subject_code, course_number, section_number, xid".format(term_code))

        if cursor.rowcount == 0:
            return []
        else:
            return cursor.fetchall()


    #=============================================
    # accepts: none
    # returns: list of students in classes
    #
    def get_full_rolls(self):
        year = self.get_current_year()
        semester = self.get_current_semester().lower()
        term_code=self.get_term_code(semester,year)

        # get the current rolls from Banner
        cursor=self.get_banner_cursor()

        cursor.execute("SELECT term_code, xid, subject_code, course_number, section_number, grade, course_registration_stat_code FROM sis_mthsc_student_registration WHERE subject_code IN ('MTHS', 'STAT','DSA') AND term_code = '{}' ".format(term_code))

        return cursor.fetchall()


    #=============================================
    # accepts: none
    #          
    # returns: html to view rolls
    def get_html_for_view_rolls(self):
        content = ""

        rolls = self.get_current_rolls()

        if len(rolls) > 0:
            content += f"""
<table style="margin: 0px auto;">
    <tr>
        <td>Term</td>
        <td>Subject<br>Code</td>
        <td>Course<br>Number</td>
        <td>Section<br>Number</td>
        <td>XID</td>
    </tr>"""

            for record in rolls:
                content += f"""
    <tr>
        <td>{record.get('term_code')}</td>
        <td>{record.get('subject_code')}</td>
        <td>{record.get('course_number')}</td>
        <td>{record.get('section_number')}</td>
        <td>{record.get('xid')}</td>
    </tr>"""

            content += """
</table>
"""
        return content

    #=============================================
    # accepts: str info (optional)
    #          
    # returns: html to view rolls
    def get_html_for_settings(self,info=""):
        now = datetime.datetime.now()
        date_year = now.year

        content = f"""
<div style="font-weight: bold; font-size: 25px;">Current semester and year</div>
<table style="margin-left: 25px;">
    <tr>
        <td>semester</td>
        <td>year</td>
    </tr>
    <tr>
        <td>{self.get_semester_dropdown(self.get_current_semester())}</td>
        <td>{self.get_max_year_dropdown(self.get_current_year(), date_year + 1)}</td>
    </tr>
</table>
"""

        content = f"""
<div style="text-align: center; margin-top: 15px; margin-bottom: 15px; min-height: 30px;">
    <span id="info_box" style="padding: 15px; background: #FFFF99; border: solid 2px #000000;">{info}</span>
</div>
<form method="POST" action="settings">
{content}
<div style="margin-top: 25px;">
    <input type="submit" value="Save Settings">
    <input type="hidden" name="form_name" value="settings">
</div>
</form>
"""
        return content
