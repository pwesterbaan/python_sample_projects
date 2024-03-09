#!/var/www/mthsc/common/venv/bin/python3

import os
from pathlib import Path
import random
import string
import sys

import MySQLdb

sys.path.append("/var/www/mthsc/common") #TODO delete/comment when running on apache server
from common_lib import commonFunctions

class ferpa_lib(commonFunctions):
    #============================================
    # dictionary for page links and names
    #
    nav = [{'name': 'View Forms', 'url': 'ferpa_view'},
           {'name': 'Upload Forms', 'url': 'ferpa_upload'}]

    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary for the dept_forms database
    #

    def get_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)

    #=============================================
    # accepts: str semester
    #               int year
    #               str cuid
    #               str first name
    #               str last name
    #               file ferpa_file
    #               str file type
    #               str uploaded by (username)
    # returns: True/False
    #
    def save_ferpa_form(self,semester, year, cuid, first_name, last_name, ferpa_file, file_type, uploaded_by):
        new_filename=self.generate_new_filename("ferpa_data","filename",file_type)
        cursor = self.get_cursor()

        # # generate out a new random filename for this FERPA file
        # new_filename = ""

        # while new_filename == "":
        #     for i in range(0,10):
        #         new_filename += random.choice(string.ascii_letters)
        #     new_filename += "." + file_type

        #     cursor.execute("SELECT COUNT(filename) AS is_valid_name FROM ferpa_data WHERE filename='{}'".format(new_filename))

        #     valid = int(cursor.fetchone()["is_valid_name"])

        #     if valid >= 1:
        #         new_filename = ""

        cursor.execute("INSERT INTO ferpa_data (semester, year, CUID, first_name, last_name, filename, uploaded_by) VALUES ('{}', '{}', UPPER('{}'), '{}', '{}', '{}', UPPER('{}'))".format(semester, year, cuid, first_name, last_name, new_filename, uploaded_by))
        return new_filename # we should really be checking to see if this succeeded


    #=============================================
    # accepts: int form id
    #               str semester
    #               int year
    #               str cuid
    #               str first name
    #               str last name
    #               str uploaded by (username)
    # returns: True/False
    #
    def update_ferpa_form(self,form_id, semester, year, cuid, first_name, last_name, uploaded_by):
        cursor = self.get_cursor()

        cursor.execute("UPDATE ferpa_data SET semester = '{}', year = '{}', CUID = '{}', first_name = '{}', last_name = '{}', uploaded_by = UPPER('{}') WHERE form_id = '{}'".format(semester, year, cuid, first_name, last_name, uploaded_by, form_id))

        return True # we should really be checking to see if this succeeded


    #=============================================
    # accepts: int form id
    # returns: dict of form info
    #
    def get_form_info(self,form_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM ferpa_data WHERE form_id = {}".format(form_id))

        if cursor.rowcount == 1:
            return cursor.fetchone()
        else:
            return {}


    #=============================================
    # accepts: int form id
    # returns: True if the form was deleted
    #
    def delete_ferpa_form(self,form_id):
        cursor = self.get_cursor()
        form_info = self.get_form_info(form_id)
        cwd=os.path.dirname(__file__)

        if len(form_info) > 0:
            # delete the uploaded form from the disk
            if os.path.exists(os.path.join(cwd,f"ferpa_forms/{form_info.get('filename')}")):
                os.remove(os.path.join(cwd,f"ferpa_forms/{form_info.get('filename')}"))

            cursor.execute("DELETE FROM ferpa_data WHERE form_id = '{}'".format(form_id))

            return True # we should really be checking to see if this succeeded
        else:
            return False


    #=============================================
    # accepts: str semester
    #               int year
    #               str last_name
    #               str cuid
    # returns: list of form data that match the given criteria
    #
    def view_forms(self, semester, year, last_name, cuid):
        cursor = self.get_cursor()

        if semester == "any":
            semester = "%"

        if year == 0:
            year = "%"

        if len(last_name) == 0:
            last_name = "%"

        if len(cuid) == 0:
            cuid = "%"

        cursor.execute("""SELECT * FROM ferpa_data WHERE semester LIKE '{}' AND year LIKE '{}' AND last_name LIKE '{}' AND cuid LIKE '{}' ORDER BY year, FIELD(semester, "spring", "summer I", "summer II", "fall"), CUID, date_uploaded""".format(semester, year, last_name, cuid))
        return cursor.fetchall()


    #=============================================
    # accepts: string student username
    # returns: person id of the student or 0 if not found
    #
    def get_person_id_from_student_username(self,student_username):
        cursor = self.get_cursor()

        # there should only be one person for a given username
        cursor.execute("SELECT person_id FROM dept_info.students WHERE student_username = '{}' LIMIT 1".format(student_username))

        if cursor.rowcount == 1:
            return cursor.fetchone()["person_id"]
        else:
            return 0


    #=============================================
    # accepts: string employee username
    # returns: person id of the employee or 0 if not found
    #
    def get_person_id_from_employee_username(self,employee_username):
        cursor = self.get_cursor()

        # there should only be one person for a given username
        cursor.execute("SELECT person_id FROM dept_info.employees WHERE employee_username = '{}' LIMIT 1".format(employee_username))

        if cursor.rowcount == 1:
            return cursor.fetchone()["person_id"]
        else:
            return 0

    #=============================================
    # accepts: string employee username
    # returns: string role (admin, staff), the empty string if it is not found
    #
    def get_person_role_from_username(self,username):
        cursor = self.get_cursor()

        # there should only be one person for a given username
        cursor.execute("SELECT role FROM users WHERE username = '{}' LIMIT 1".format(username))

        if cursor.rowcount == 1:
            return cursor.fetchone()["role"]
        else:
            return ""


    #=============================================
    # accepts: int person id
    # returns: string username, the empty string if it is not found
    #
    def get_employee_username_from_person_id(self,person_id):
        cursor = self.get_cursor()

        # there should only be one person for a given username
        cursor.execute("SELECT employee_username FROM dept_info.employees WHERE person_id = '{}' LIMIT 1".format(person_id))

        if cursor.rowcount == 1:
            return cursor.fetchone()["employee_username"]
        else:
            return ""


    #=============================================
    # accepts: str username
    # returns: string employee username, the empty string if it is not found
    #
    def get_employee_username(self,username):
        cursor = self.get_cursor()

        # we first guess that this is an employee username already
        person_id = get_person_id_from_employee_username(username)

        if person_id == 0:
            # we see if this username is a student username
            person_id = get_person_id_from_student_username(username)

        if person_id == 0:
            # this person is not in the math department
            return ""
        else:
            # this person is in the math department (but may not be an employee)
            return get_employee_username_from_person_id(person_id)
