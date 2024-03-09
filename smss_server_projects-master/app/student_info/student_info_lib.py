#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

import MySQLdb

sys.path.append("/var/www/mthsc/common") #delete/comment out when running on apache server
from common_lib import commonFunctions

class student_info_lib(commonFunctions):

    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary
    #
    def get_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)


    #=============================================
    # accepts: int selected
    # returns: the html for the tab menu
    #
    def get_tab_menu(self,selected):
        classes = ["", ""]
        classes[selected] = "class=\"selected_tab\""
        return """
<div class="tab_menu">
        <ul>
            <li """ + classes[0] + """><a href="download_student_info">Download Data</a></li>
            <li """ + classes[1] + """><a href="view_student_info">View Data</a></li>
        </ul>
</div>
"""


    #=============================================
    # accepts: nothing
    # returns: the html for view_student_info page
    #
    def get_view_student_info_html(self):
        html=""
        info = self.get_student_info()

        if len(info) > 0:
            html = """<table style="border-spacing: 0px; border-collapse: collapse; font-size: 11pt;">
    <tr>
        <td class="display" style="text-align: center; font-weight: bold;">XID</td>
        <td class="display" style="text-align: center; font-weight: bold;">username</td>
        <td class="display" style="text-align: center; font-weight: bold;">name</td>
    </tr>
"""
            for student in info:
                html += f"""
    <tr>
        <td class="display">{student.get('xid')}</td>
        <td class="display">{student.get('username')}</td>
        <td class="display">{student.get('name')}</td>
    </tr>
"""

        html += "</table>"
        return html


    #=============================================
    # accepts: nothing
    # returns: the html for download_student_info page
    #
    def get_download_student_info_html(self):
        html=f"""
<form action="download_student_info" method="POST">
<div style="margin: 10px;">Click the button below to download the student info from Banner for <span style="font-weight: bold; color: #FF6633;">{self.get_current_semester().capitalize()} {self.get_current_year()}</span>. At the moment we use this to get the username for a student from their XID for prereq checking and the student names for the grade collection system.</div>
<div style="text-align: center; padding: 10px;">
	<input type="submit" value="Get Banner Student Info">
	<input type="hidden" name="load_info_submit" value=True>
</div>
</form>
"""
        return html


    #=============================================
    # accepts: str semester
    #          str year
    # returns: True if the info was downloaded, False otherwise
    #
    def download_student_info(self,semester, year):
        cursor=self.get_banner_cursor()

        term = self.get_term_code(semester,year)

        cursor.execute("""SELECT XID, USERNAME_STUDENT, STUDENT_NAME, LAST_NAME, FIRST_NAME, PREFERRED_NAME FROM SIS_MTHSC_STUDENT_INFORMATION WHERE TERM_CODE = '%s'""" % term)

        enrolled_students = cursor.fetchall()

        self.update_Banner_download_running(True)
        mthsc_cursor = self.get_cursor()

        for student in enrolled_students:
            if student[1] is not None:
                self.add_update_student_info(mthsc_cursor, student[0], student[1], student[2], student[3], student[4], student[5])

        self.update_Banner_download_running(False)

        return True # we really should be checking to see if this succeeded


    #=============================================
    # accepts: str semester
    #          str year
    # returns: True if the info was downloaded, False otherwise
    #
    def download_accepted_student_info(self,term):
        cursor=self.get_banner_cursor()

        cursor.execute("""SELECT XID, USERNAME_STUDENT, STUDENT_NAME, LAST_NAME, FIRST_NAME, PREFERRED_NAME FROM SIS_MTHSC_ACCEPTED_STUDENTS WHERE TERM_CODE_ENTRY = '%s'""" % term)

        accepted_students = cursor.fetchall()

        self.update_Banner_download_running(True)
        mthsc_cursor = self.get_cursor()

        for student in accepted_students:
            if student[1] is not None:
                add_update_student_info(mthsc_cursor, student[0], student[1], student[2], student[3], student[4], student[5])

        self.update_Banner_download_running(False)

        return True # we really should be checking to see if this succeeded


    #=============================================
    # accepts: str xid
    #          str username
    #          str name
    #          str last_name
    #          str first_name
    # returns: True if the info was saved, False otherwise
    #
    def add_update_student_info(self,cursor, xid, username, name, last_name, first_name, preferred_name):

        sql = "INSERT INTO student_info (xid, username, name, last_name, first_name, preferred_name) VALUES (%s, %s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE username = %s, name = %s, last_name = %s, first_name = %s, preferred_name  = %s"
        cursor.execute(sql, (xid, username.lower(), name, last_name, first_name, preferred_name, username.lower(), name, last_name, first_name, preferred_name))

        return True # we really should be checking to see if this succeeded


    #=============================================
    # accepts: bool Banner download status
    # returns: 1 if the Banner download status was updated
    #
    def update_Banner_download_running(self,status):
        return self.set_setting("Banner_download_running", status)


    #=============================================
    # accepts: nothing
    # returns: bool whether the Banner info download is running
    #
    def get_Banner_download_running_status(self):
        return self.get_setting("Banner_download_running")


    #=============================================
    # accepts: none
    # returns: the html for a table displaying the stored info
    #
    def get_student_info(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM student_info")

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []
