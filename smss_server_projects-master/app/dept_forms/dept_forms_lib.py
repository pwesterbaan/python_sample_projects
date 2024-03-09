#!/var/www/mthsc/common/venv/bin/python3

import calendar
import datetime
import smtplib
import sys
import time
import types

import MySQLdb
from email.mime import text as MIMEText

sys.path.append("/var/www/mthsc/common") #delete/comment out when running on apache server
from common_lib import commonFunctions

# from .candidate_lib import candidate_lib
# import candidate_lib as cand_lib
# cand_lib=candidate_lib()

class dept_forms_lib(commonFunctions):

    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary for the syllabus_rep database
    #
    def get_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)

    #=============================================
    # accepts: none
    # returns: a list of people
    #
    def get_person_list(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM person ORDER BY last_name, first_name")

        result = cursor.fetchall()

        return result

    #=============================================
    # accepts: int person id
    # returns: dict of the person
    #
    def get_person(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM person WHERE person_id = %s", (person_id,))

        if cursor.rowcount == 1:
            return cursor.fetchone()
        else:
            return {"person_id": 0, "pref_name": "", "first_name": "", "middle_name": "", "last_name": "", "suffix": "", "pref_name": "", "display_name": "", "maiden_name": "", "sex": "", "username": ""}

    #=============================================
    # accepts: none
    # returns: a list of people
    #
    def get_student_person_list(self):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM dept_info.students AS s LEFT JOIN person AS p ON p.person_id = s.person_id ORDER BY last_name, first_name""")

        result = cursor.fetchall()

        return result

    #=============================================
    # accepts: none
    # returns: a list of people
    #
    def get_employee_person_list(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM dept_info.employees AS e LEFT JOIN person AS p ON p.person_id = e.person_id ORDER BY last_name, first_name")

        result = cursor.fetchall()

        return result

    #=============================================
    # accepts: none
    # returns: a list of people
    #
    def get_current_student_list(self):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM dept_info.people_to_lists_link AS pll LEFT JOIN lists AS l ON l.list_id = pll.list_id LEFT JOIN person AS p ON p.person_id = pll.person_id WHERE l.list_name = "current students" ORDER BY last_name, first_name""")

        result = cursor.fetchall()

        return result

    # TODO: not sure why this uses the employees table?
    #=============================================
    # accepts: none
    # returns: a list of people
    #
    def get_current_faculty_list(self):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM dept_info.people_to_lists_link AS pll LEFT JOIN lists AS l ON l.list_id = pll.list_id LEFT JOIN person AS p ON p.person_id = pll.person_id LEFT JOIN employees AS e ON e.person_id = p.person_id WHERE l.list_name = "current faculty" ORDER BY last_name, first_name""")

        result = cursor.fetchall()

        return result

    #=============================================
    # accepts: none
    # returns: a list of people
    #
    def get_current_emeriti_faculty_list(self):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM dept_info.people_to_lists_link AS pll LEFT JOIN lists AS l ON l.list_id = pll.list_id LEFT JOIN person AS p ON p.person_id = pll.person_id WHERE l.list_name = "current faculty emeriti" ORDER BY last_name, first_name""")

        result = cursor.fetchall()

        return result

    #=============================================
    # accepts: nothing
    # returns: a list of the lists
    #
    def get_lists(self):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM dept_info.lists ORDER BY list_name""")

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int list id
    # returns: str name of the list
    #
    def get_list_name(self,list_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT list_name FROM dept_info.lists WHERE list_id = %s""", (list_id,))

        if cursor.rowcount > 0:
            return cursor.fetchone()["list_name"]
        else:
            return ""

    #=============================================
    # accepts: str name of the list
    # returns: int list id, 0 if it is not found
    #
    def get_list_id(self,list_name):
        cursor = self.get_cursor()

        cursor.execute("""SELECT list_id FROM dept_info.lists WHERE list_name = %s""", (list_name,))

        if cursor.rowcount > 0:
            return cursor.fetchone()["list_id"]
        else:
            return 0

    #=============================================
    # accepts: int list_id
    # returns: a list of people in the list
    #
    def get_people_in_list(self,list_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT pll.id AS list_link_id, p.person_id, p.first_name, p.last_name FROM dept_info.people_to_lists_link AS pll LEFT JOIN person AS p ON p.person_id = pll.person_id WHERE pll.list_id = %s ORDER BY last_name, first_name""", (list_id,))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int person_id
    #          int list_id
    # returns: 1 if the link was added, 0 if not
    #
    def add_list_link(self,person_id, list_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM people_to_lists_link WHERE person_id = %s AND list_id = %s", (person_id, list_id))
        if cursor.rowcount == 0:
            # we only add them if they aren't there already
            cursor.execute("INSERT INTO people_to_lists_link (person_id, list_id) VALUES (%s, %s)", (person_id, list_id))

        return 1 # we really should be checking to see if this succeeded

    #=============================================
    # accepts: int person_id
    #          int list_id
    # returns: int the list_link_id, 0 if it is not found
    #
    def get_list_link_id(person_id, list_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT id FROM people_to_lists_link WHERE person_id = %s AND list_id = %s", (person_id, list_id))

        if cursor.rowcount == 1:
            return cursor.fetchone()["id"]
        else:
            return 0

    #=============================================
    # accepts: int list_id
    # returns: 1 if the link was deleted, 0 if not
    #
    def delete_list_link(self,list_link_id):
        cursor = self.get_cursor()

        cursor.execute("""DELETE FROM people_to_lists_link WHERE id = %s""", (list_link_id,))

        return 1 # we really should be checking to see if this succeeded

    #=============================================
    # accepts: int person_id
    # returns: string student username
    #
    def get_student_username(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT old_student_username FROM person WHERE person_id = %s", (person_id,))
        record = cursor.fetchone()

        if cursor.rowcount == 0:
            return ""
        else:
            return record.get('old_student_username')

    #=============================================
    # accepts: int person_id
    # returns: string employee username
    #
    def get_employee_username(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT old_employee_username FROM person WHERE person_id = %s", (person_id,))
        record = cursor.fetchone()

        if cursor.rowcount == 0:
            return ""
        else:
            return record.get('old_employee_username')

    #=============================================
    # accepts: string student username
    # returns: person id of the student or 0 if not found
    #
    def get_person_id_from_student_username(self,student_username):
        cursor = self.get_cursor()

        # there should only be one person for a given username
        cursor.execute("SELECT person_id FROM person WHERE old_student_username = %s LIMIT 1", (student_username,))

        if cursor.rowcount == 1:
            return cursor.fetchone().get('person_id')
        else:
            return 0

    #=============================================
    # accepts: string employee username
    # returns: person id of the employee or 0 if not found
    #
    def get_person_id_from_employee_username(self,employee_username):
        cursor = self.get_cursor()

        # there should only be one person for a given username
        cursor.execute("SELECT person_id FROM person WHERE old_employee_username = %s LIMIT 1", (employee_username,))

        if cursor.rowcount == 1:
            return cursor.fetchone().get('person_id')
        else:
            return 0

    #=============================================
    # accepts: string username
    # returns: person id of the person or 0 if not found
    #
    def get_person_id_from_username(self,username):
        cursor = self.get_cursor()

        # there should only be one person for a given username
        cursor.execute("SELECT person_id FROM person WHERE username = %s LIMIT 1", (username,))

        if cursor.rowcount == 1:
            return cursor.fetchone().get('person_id')
        else:
            return 0

    #=============================================
    # accepts: int person id
    # returns: True if the person is a current student, False if not
    #
    def is_cur_student(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT COUNT(person_id) AS valid FROM dept_info.people_to_lists_link WHERE person_id = %s AND list_id = (SELECT list_id FROM dept_info.lists WHERE list_name = "current students")""", (person_id,))

        if cursor.fetchone().get('valid') == 1:
            return True
        else:
            return False

    #=============================================
    # accepts: int person id
    # returns: True if the person is a current faculty member, False if not
    #
    def is_cur_faculty(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT COUNT(person_id) AS valid FROM dept_info.people_to_lists_link WHERE person_id = %s AND list_id = (SELECT list_id FROM dept_info.lists WHERE list_name = "current faculty")""", (person_id,))

        if cursor.fetchone().get('valid') == 1:
            return True
        else:
            return False

    #=============================================
    # accepts: int person id
    # returns: True if the person is a current staff member, False if not
    # ++
    def is_cur_staff(self,mperson_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT COUNT(person_id) AS valid FROM dept_info.people_to_lists_link WHERE person_id = %s AND list_id = (SELECT list_id FROM dept_info.lists WHERE list_name = "current staff")""", (person_id,))

        if cursor.fetchone().get('valid') == 1:
            return True
        else:
            return False

    #=============================================
    # accepts: int person id
    # returns: True if the person is a current faculty member, False if not
    #
    def is_cur_faculty_emeriti(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT COUNT(person_id) AS valid FROM dept_info.people_to_lists_link WHERE person_id = %s AND list_id = (SELECT list_id FROM dept_info.lists WHERE list_name = "current faculty emeriti")""", (person_id,))

        if cursor.fetchone().get('valid') == 1:
            return True
        else:
            return False

    #=============================================
    # accepts: none
    # returns: a list of people info
    #
    def get_person_info_list(self):
        cursor = self.get_cursor()

        cursor.execute("""SELECT person_id, first_name, last_name, sex, IFNULL(old_employee_username, "") AS employee_username, IFNULL(old_student_username, "") AS student_username FROM person ORDER BY last_name, first_name""")

        result = cursor.fetchall()

        return result

    #=============================================
    # accepts: str first name
    #          str middle name
    #          str last name
    #          str suffix
    #          str pref name
    #          str maiden name
    #          str sex
    #          str username
    # returns: int the person_id of the new person, 0 if the person was not added
    #
    def add_person(self,first_name, middle_name, last_name, suffix, pref_name, display_name, maiden_name, sex, username):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO person (first_name, middle_name, last_name, suffix, pref_name, display_name, maiden_name, sex, username) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, UPPER(%s))", (first_name, middle_name, last_name, suffix, pref_name, display_name, maiden_name, sex, username))

        person_id = cursor.lastrowid

        return person_id # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    #          str first name
    #          str middle name
    #          str last name
    #          str suffix
    #          str pref name
    #          str maiden name
    #          str sex
    #          str username
    # returns: 1 if the person was updated, 0 if not
    #
    def update_person(self,person_id, first_name, middle_name, last_name, suffix, pref_name, display_name, maiden_name, sex, username):
        cursor = self.get_cursor()

        cursor.execute("UPDATE person SET first_name = %s, middle_name = %s, last_name = %s, suffix = %s, pref_name = %s, display_name = %s, maiden_name = %s, sex = %s, username = UPPER(%s) WHERE person_id = %s", (first_name, middle_name, last_name, suffix, pref_name, display_name, maiden_name, sex, username, person_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person_id
    # returns: 1 if the person was deleted, 0 if the person was not
    #
    def delete_person(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM person WHERE person_id = %s", (person_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    #          str student username
    # returns: 1 if the username was added, 0 if not
    #
    def add_student_username(self,person_id, student_username):
        cursor = self.get_cursor()

        #cursor.execute("INSERT INTO students (person_id, student_username) VALUES (%s, UPPER(%s)) ON DUPLICATE KEY UPDATE student_username = UPPER(%s)", (person_id, student_username, student_username))

        cursor.execute("UPDATE person SET old_student_username = UPPER(%s) WHERE person_id = %s", (student_username, person_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    # returns: 1 if the username was deleted, 0 if not
    #
    def delete_student_username(self,person_id):
        cursor = self.get_cursor()

        #cursor.execute("DELETE FROM students WHERE person_id = %s", person_id)
        cursor.execute("UPDATE person SET old_student_username = '' WHERE person_id = %s", (person_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    #           str employee username
    # returns: 1 if the username was added, 0 if not
    #
    def add_employee_username(self,person_id, employee_username):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO employees (person_id, employee_username) VALUES (%s, UPPER(%s)) ON DUPLICATE KEY UPDATE employee_username = UPPER(%s)", (person_id, employee_username, employee_username))

        cursor.execute("UPDATE person SET old_employee_username = UPPER(%s) WHERE person_id = %s", (employee_username, person_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    # returns: 1 if the username was deleted, 0 if not
    #
    def delete_employee_username(self,person_id):
        cursor = self.get_cursor()

        #cursor.execute("DELETE FROM employees WHERE person_id = %s", person_id)
        cursor.execute("UPDATE person SET old_employee_username = '' WHERE person_id = %s", (person_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: string student username
    # returns: list of benchmarks attempted
    #
    def get_benchmarks(self,username):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT * FROM benchmarks AS b INNER JOIN benchmark_types AS bt
ON
 bt.benchmark_type_id = b.benchmark_type_id
WHERE student_username = %s
ORDER BY attempt_date""", username)

        return cursor.fetchall()

    #=============================================
    # accepts: int benchmark id
    # returns: benchmark info
    #
    def get_benchmark(self,benchmark_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT * FROM benchmarks AS b LEFT JOIN benchmark_types AS bt
ON
 bt.benchmark_type_id = b.benchmark_type_id
WHERE benchmark_id = %s""", (benchmark_id,))

        return cursor.fetchone()

    #=============================================
    # accepts: int benchmark id
    # returns: 1 if the benchmark was deleted, 0 if not
    #
    def delete_benchmark(self,benchmark_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM benchmarks WHERE benchmark_id = %s", (benchmark_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #          int benchmark type id
    #          date attempt date
    #          bool passed
    # returns: 1 if the benchmark was added, 0 if not
    #
    def add_benchmark(self,student_username, benchmark_type_id, attempt_date, passed):
        cursor = self.get_cursor()

        # note that we have to escape the mysql % signs
        cursor.execute("INSERT INTO benchmarks (student_username, benchmark_type_id, attempt_date, passed) VALUES (UPPER(%s), %s, STR_TO_DATE(%s,'%%Y-%%b-%%d %%l:%%i %%p'), %s)", (student_username, benchmark_type_id, attempt_date, passed))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int benchmark id
    # returns: 1 if the benchmark was added, 0 if not
    #
    def update_benchmark(self,benchmark_id, benchmark_type_id, attempt_date, passed):
        cursor = self.get_cursor()

        # note that we have to escape the mysql % signs
        cursor.execute("""UPDATE benchmarks SET benchmark_type_id = %s, attempt_date = STR_TO_DATE(%s,'%%Y-%%b-%%d %%l:%%i %%p'), passed = %s WHERE benchmark_id = %s""", (benchmark_type_id, attempt_date, passed, benchmark_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    # returns: list of forms
    #
    def get_forms(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM dept_info.forms WHERE student_username = %s ORDER BY form_date
""", (student_username,))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int form id
    # returns: dict of form info
    #
    def get_form(self,form_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT form_name, degree, form_date FROM dept_info.forms WHERE form_id = %s""", (form_id,))

        if cursor.rowcount > 0:
            return cursor.fetchone()
        else:
            return {}

    #=============================================
    # accepts: int form id
    # returns: 1 if the form was deleted, 0 if not
    #
    def delete_form(self,form_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM forms WHERE form_id = %s", (form_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #          str form name
    #          str degree
    #          date form date
    # returns: 1 if the form was added, 0 if not
    #
    def add_form(self,student_username, form_name, degree, form_date):
        cursor = self.get_cursor()

        # note that we have to escape the mysql % signs
        cursor.execute("INSERT INTO forms (student_username, form_name, degree, form_date) VALUES (%s, %s, %s, STR_TO_DATE(%s,'%%Y-%%b-%%d'))", (student_username, form_name, degree, form_date))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int form id
    #              str form_name
    #              str degree
    #              date form date
    # returns: 1 if the form was updated, 0 if not
    #
    def update_form(self,form_id, form_name, degree, form_date):
        cursor = self.get_cursor()

        cursor.execute("""UPDATE forms SET form_name = %s, degree = %s, form_date = STR_TO_DATE(%s,'%%Y-%%b-%%d') WHERE form_id = %s""", (form_name, degree, form_date, form_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    # returns: list of papers
    #
    def get_degree_papers(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM dept_info.degree_paper WHERE student_username = %s ORDER BY degree""", (student_username,))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int paper id
    # returns: dict of paper info
    #
    def get_degree_paper(self,paper_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT degree, paper_type, title FROM dept_info.degree_paper WHERE paper_id = %s""", (paper_id,))

        if cursor.rowcount > 0:
            return cursor.fetchone()
        else:
            return {}

    #=============================================
    # accepts: int paper id
    # returns: 1 if the paper was deleted, 0 if not
    #
    def delete_degree_paper(self,paper_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM degree_paper WHERE paper_id = %s", (paper_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #          str degree
    #          str paper type
    #          str paper title
    # returns: 1 if the paper was added, 0 if not
    #
    def add_degree_paper(self,student_username, degree, paper_type, title):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO degree_paper (student_username, degree, paper_type, title) VALUES (%s, %s, %s, %s)", (student_username, degree, paper_type, title))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int paper id
    #          str degree
    #          str paper type
    #          str title
    # returns: 1 if the paper was updated, 0 if not
    #
    def update_degree_paper(self,paper_id, degree, paper_type, title):
        cursor = self.get_cursor()

        cursor.execute("""UPDATE degree_paper SET degree = %s, paper_type = %s, title = %s WHERE paper_id = %s""", (degree, paper_type, title, paper_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int speak id
    # returns: speak test attempt info
    #
    def get_speak_test_attempt(self,speak_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM SPEAK_test_attempts WHERE speak_id = %s""", (speak_id,))

        return cursor.fetchone()

    #=============================================
    # accepts: int speak id
    # returns: 1 if the attempt was deleted, 0 if not
    #
    def delete_speak_test_attempt(self,speak_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM SPEAK_test_attempts WHERE speak_id = %s", (speak_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int speak id
    # returns: 1 if the attempt was added, 0 if not
    #
    def add_speak_test_attempt(self,student_username, attempt_date, passed):
        cursor = self.get_cursor()

        # note that we have to escape the mysql % signs
        cursor.execute("INSERT INTO SPEAK_test_attempts (student_username, attempt_date, passed) VALUES (UPPER(%s), STR_TO_DATE(%s,'%%Y-%%b-%%d'), %s)", (student_username, attempt_date, passed))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int speak id
    # returns: 1 if the attempt was updated, 0 if not
    #
    def update_speak_test_attempt(self,speak_id, attempt_date, passed):
        cursor = self.get_cursor()

        # note that we have to escape the mysql % signs
        cursor.execute("""UPDATE SPEAK_test_attempts SET attempt_date = STR_TO_DATE(%s,'%%Y-%%b-%%d'), passed = %s WHERE speak_id = %s""", (attempt_date, passed, speak_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    # returns: list of initial employment positions
    #
    def get_initial_employment_positions(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM dept_info.initial_employment WHERE student_username = %s ORDER BY employer""", (student_username,))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int employment id
    # returns: dict of position info
    #
    def get_initial_employment_position(self,employment_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT position_type, employer, position_title FROM dept_info.initial_employment WHERE employment_id = %s""", (employment_id,))

        if cursor.rowcount > 0:
            return cursor.fetchone()
        else:
            return {}

    #=============================================
    # accepts: int employment id
    # returns: 1 if the employment was deleted, 0 if not
    #
    def delete_initial_employment_position(self,employment_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM initial_employment WHERE employment_id = %s", (employment_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #          str position type
    #          str employer
    #          str position title
    # returns: 1 if the employment position was added, 0 if not
    #
    def add_initial_employment_position(self,student_username, position_type, employer, position_title):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO initial_employment (student_username, position_type, employer, position_title) VALUES (%s, %s, %s, %s)", (student_username, position_type, employer, position_title))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int employment id
    #          str position type
    #          str employer
    #          str position title
    # returns: 1 if the employment position was updated, 0 if not
    #
    def update_initial_employment_position(self,employment_id, position_type, employer, position_title):
        cursor = self.get_cursor()

        cursor.execute("""UPDATE initial_employment SET position_type = %s, employer = %s, position_title = %s WHERE employment_id = %s""", (position_type, employer, position_title, employment_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    # returns: date visa expiration, or -1 if none exists
    #
    def get_visa_expiration(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT expiration_date FROM visa WHERE person_id = %s """, (person_id,))

        if cursor.rowcount == 1:
            return cursor.fetchone()["expiration_date"]
        else:
            return -1

    #=============================================
    # accepts: int person id
    # returns: 1 if the visa expiration date was deleted, 0 if not
    #
    def delete_visa_expiration(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM visa WHERE person_id = %s", (person_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    #          str expiration date
    # returns: 1 if the visa expiration date was added, 0 if not
    #
    def add_visa_expiration(self,person_id, expiration_date):
        cursor = self.get_cursor()

        # note that we have to escape the mysql % signs
        cursor.execute("INSERT INTO visa (person_id, expiration_date) VALUES (UPPER(%s), STR_TO_DATE(%s,'%%Y-%%b-%%d'))", (person_id, expiration_date))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    #          str expiration date
    # returns: 1 if the expiration date was updated, 0 if not
    #
    def update_visa_expiration(self,person_id, expiration_date):
        cursor = self.get_cursor()

        # note that we have to escape the mysql % signs
        cursor.execute("""UPDATE visa SET expiration_date = STR_TO_DATE(%s,'%%Y-%%b-%%d') WHERE person_id = %s""", (expiration_date, person_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person_id
    # returns: list of email addresses
    #
    def get_emails(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM email_addresses WHERE person_id = %s""", (person_id,))

        return cursor.fetchall()

    #=============================================
    # accepts: int email id
    # returns: email info
    #
    def get_email(self,email_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT * FROM email_addresses WHERE email_id = %s""", (email_id,))

        return cursor.fetchone()

    #=============================================
    # accepts: int email id
    # returns: 1 if the email was deleted, 0 if not
    #
    def delete_email(self,email_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM email_addresses WHERE email_id = %s", (email_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int email id
    #          str email address
    #          str email type
    # returns: 1 if the email was added, 0 if not
    #
    def add_email(self,person_id, email_address, email_type):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO email_addresses (person_id, email_address, email_type) VALUES (%s, %s, %s)", (person_id, email_address, email_type))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int email id
    #          str email address
    #          str email type
    # returns: 1 if the email address was updated, 0 if not
    #
    def update_email(self,email_id, email_address, email_type):
        cursor = self.get_cursor()

        cursor.execute("""UPDATE email_addresses SET email_address = %s, email_type = %s WHERE email_id = %s""", (email_address, email_type, email_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    # returns: list of numbers
    #
    def get_phone_numbers(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT
IF(number = "", "", CONCAT("(", SUBSTRING(number,1,3), ") ", SUBSTRING(number,4,3), "-", SUBSTRING(number, 7,4))) AS number, number_id, number_type FROM dept_info.phone_numbers WHERE person_id = %s ORDER BY number
""", (person_id,))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int number id
    # returns: dict of phone number info
    #
    def get_phone_number(self,number_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT number AS phone_number, number_type FROM dept_info.phone_numbers WHERE number_id = %s""", (number_id,))

        if cursor.rowcount > 0:
            return cursor.fetchone()
        else:
            return {}

    #=============================================
    # accepts: int number id
    # returns: 1 if the number was deleted, 0 if not
    #
    def delete_phone_number(self,number_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM phone_numbers WHERE number_id = %s", (number_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int number id
    #           str phone number
    #           str number type
    # returns: 1 if the number was added, 0 if not
    #
    def add_phone_number(self,person_id, phone_number, number_type):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO phone_numbers (person_id, number, number_type) VALUES (%s, %s, %s)", (person_id, phone_number, number_type))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int number id
    #          str phone number
    #          str number type
    # returns: 1 if the number was updated, 0 if not
    #
    def update_phone_number(self,number_id, phone_number, number_type):
        cursor = self.get_cursor()

        cursor.execute("""UPDATE phone_numbers SET number = %s, number_type = %s WHERE number_id = %s""", (phone_number, number_type, number_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person_id
    # returns: list of mailing addresses
    #
    def get_addresses(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM mail_addresses WHERE person_id = %s""", (person_id,))

        return cursor.fetchall()

    #=============================================
    # accepts: int address id
    # returns: address info
    #
    def get_address(self,address_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT * FROM mail_addresses WHERE address_id = %s""", (address_id,))

        return cursor.fetchone()

    #=============================================
    # accepts: int address id
    # returns: 1 if the address was deleted, 0 if not
    #
    def delete_address(self,address_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM mail_addresses WHERE address_id = %s", (address_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    #          str street_address_line_1
    #          str street_address_line_2
    #          str city
    #          str state
    #          str zip code
    #          str country
    #          str address type
    # returns: 1 if the address was added, 0 if not
    #
    def add_address(self,person_id, street_address_line_1, street_address_line_2, city, state, zip_code, country, address_type):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO mail_addresses (person_id, street_address_line_1, street_address_line_2, city, state, zip_code, country, address_type) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", (person_id, street_address_line_1, street_address_line_2, city, state, zip_code, country, address_type))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int address id
    # returns: 1 if the address was updated, 0 if not
    #
    def update_address(self,address_id, street_address_line_1, street_address_line_2, city, state, zip_code, country, address_type):
        cursor = self.get_cursor()

        cursor.execute("""UPDATE mail_addresses SET street_address_line_1 = %s, street_address_line_2 = %s, city = %s, state = %s, zip_code = %s, country = %s, address_type = %s WHERE address_id = %s""", (street_address_line_1, street_address_line_2, city, state, zip_code, country, address_type, address_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person_id
    # returns: list of education
    #
    def get_education_list(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT ed.education_id, ed.school_id, ed.semester, ed.year, ed.degree, ed.major, IF(ed.final_gpa = -1, "", ed.final_gpa) AS final_gpa, s.name AS school FROM education AS ed LEFT JOIN schools AS s ON ed.school_id = s.school_id WHERE person_id = %s ORDER BY year DESC, FIELD(ed.semester, "fall", "summer II", "summer I", "spring"), FIELD(ed.degree, "PhD", "MA", "ME", "MEd", "MS", "AB", "BA", "BS"), ed.major""", (person_id,))

        return cursor.fetchall()

    #=============================================
    # accepts: int education id
    # returns: education info
    #
    def get_education(self,education_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT school_id, degree, major, semester, year, IF(final_gpa = -1, "", final_gpa) AS final_gpa FROM education WHERE education_id = %s""", (education_id,))

        return cursor.fetchone()

    #=============================================
    # accepts: int education id
    # returns: 1 if the education was deleted, 0 if not
    #
    def delete_education(self,education_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM education WHERE education_id = %s", (education_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    #          int school id
    #          str major
    #          str semester
    #          int year
    #          float final gpa
    # returns: 1 if the education was added, 0 if not
    #
    def add_education(person_id, school_id, degree, major, semester, year, final_gpa):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO education (person_id, school_id, degree, major, semester, year, final_gpa) VALUES (%s, %s, %s, %s, %s, %s, %s)", (person_id, school_id, degree, major, semester, year, final_gpa))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int education id
    #          int school id
    #          str major
    #          str semester
    #          int year
    #          float final gpa
    # returns: 1 if the education was updated, 0 if not
    #
    def update_education(self,education_id, school_id, degree, major, semester, year, final_gpa):
        cursor = self.get_cursor()

        # note that we have to escape the mysql % signs
        cursor.execute("""UPDATE education SET school_id = %s, degree = %s, major = %s, semester = %s, year = %s, final_gpa = %s WHERE education_id = %s""", (school_id, degree, major, semester, year, final_gpa, education_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person_id
    # returns: list of advisors
    #
    def get_advisors(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("""SELECT advisor_id, first_name, last_name, advisor_type FROM advisors AS a INNER JOIN employees AS e ON e.employee_username = a.employee_username INNER JOIN person AS p ON p.person_id = e.person_id WHERE student_username = %s ORDER BY FIELD(advisor_type, "first", "MS", "PhD")""", (student_username,))

        return cursor.fetchall()

    #=============================================
    # accepts: int advisor id
    # returns: str employee username of advisor
    #
    def get_advisor_username(self,advisor_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT employee_username FROM advisors WHERE advisor_id = %s""", (advisor_id,))

        # we assume we are not given a bad advisor_id resulting in no records being returned
        return cursor.fetchone().get('employee_username')

    #=============================================
    # accepts: int advisor id
    # returns: str advisor type
    #
    def get_advisor_type(self,advisor_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT advisor_type FROM advisors WHERE advisor_id = %s""", (advisor_id,))

        # we assume we are not given a bad advisor_id resulting in no records being returned
        return cursor.fetchone().get('advisor_type')

    #=============================================
    # accepts: int advisor id
    # returns: 1 if the advisor was deleted, 0 if not
    #
    def delete_advisor(self,advisor_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM advisors WHERE advisor_id = %s", (advisor_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int advisor id
    # returns: 1 if the advisor was added, 0 if not
    #
    def add_advisor(self,student_username, employee_username, advisor_type):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO advisors (student_username, employee_username, advisor_type) VALUES (UPPER(%s), %s, %s)", (student_username, employee_username, advisor_type))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int advisor id
    # returns: 1 if the advisor was updated, 0 if not
    #
    def update_advisor(self,advisor_id, employee_username, advisor_type):
        cursor = self.get_cursor()

        # note that we have to escape the mysql % signs
        cursor.execute("""UPDATE advisors SET employee_username = %s, advisor_type = %s WHERE advisor_id = %s""", (employee_username, advisor_type, advisor_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str employee username
    # returns: a list of the advisors current student(s)
    #
    def get_advisors_current_students(self,employee_username):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT
 ad.student_username,
 s.person_id,
 IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name,
 IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name
FROM dept_info.advisors AS ad
LEFT JOIN dept_info.degree_program AS dp ON
dp.student_username = ad.student_username
LEFT JOIN dept_info.students AS s ON
s.student_username = ad.student_username
LEFT JOIN dept_info.person AS p ON
p.person_id = s.person_id
WHERE
 employee_username = %s AND
 advisor_type = cur_degree AND
 s.person_id IN (SELECT person_id FROM dept_info.people_to_lists_link AS pll WHERE pll.list_id = (SELECT list_id FROM dept_info.lists WHERE list_name = "current students"))""", (employee_username,))

        result = cursor.fetchall()

        output = []

        for record in result:
            output.append({
                "person_id": record.get('person_id'),
                "username": record.get('student_username'),
                "full_name": (record.get('first_name') + " " + record.get('last_name')).strip()
            })

        return output

    #=============================================
    # accepts: int person_id
    # returns: list of offices
    #
    def get_offices(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT pol.id AS link_id, o.office_id, o.description, o.office_type FROM people_to_offices_link AS pol INNER JOIN offices AS o ON o.office_id = pol.office_id WHERE person_id = %s ORDER BY o.description""", (person_id,))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int office link id
    # returns: int office id
    #
    def get_office_id(self,office_link_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT office_id FROM people_to_offices_link WHERE id = %s""", (office_link_id,))

        if cursor.rowcount > 0:
            return cursor.fetchone().get('office_id')
        else:
            return -1


    #=============================================
    # accepts: nothing
    # returns: list of offices, each entry is a
    #          dictionary with information about the office
    #
    def get_office_overview(self):
        cursor = self.get_cursor()

        cursor.execute("""SELECT LEFT(o.description, IF(LOCATE("-", o.description) > 0, LOCATE("-", o.description), LENGTH(o.description))) AS temp1, CONVERT(RIGHT(o.description, LENGTH(o.description) - LOCATE("-", o.description)), SIGNED) AS temp2, o.office_id, o.description, capacity, pol.id AS link_id, IFNULL(pol.person_id, -1) AS person_id, IF(ISNULL(suffix) OR suffix = "", CONCAT_WS(", ", last_name, first_name), CONCAT_WS(", ", CONCAT_WS(" ", last_name, suffix), first_name)) AS name  FROM offices AS o LEFT JOIN people_to_offices_link AS pol ON pol.office_id = o.office_id LEFT JOIN person AS p ON p.person_id = pol.person_id ORDER BY temp1, temp2, o.description, o.office_id, last_name, first_name""")

        results = cursor.fetchall()
        offices = []
        i = 0

        while i < len(results):
            record = results[i]
            cur_office_name = record.get('description')
            cur_office_id = record.get('office_id')
            cur_office_capacity = record.get('capacity')
            occupants = []

            while i < len(results) and results[i]["office_id"] == cur_office_id:
                record = results[i]
                if record["person_id"] != -1:
                    occupants.append({"name": record["name"], "person_id": record["person_id"], "link_id": record["link_id"]})
                i += 1

        offices.append({"office_desc": cur_office_name,
                        "office_id": cur_office_id,
                        "capacity": cur_office_capacity,
                        "occupants": occupants})

        return offices

    #=============================================
    # accepts: nothing
    # returns: list of offices, each entry is a
    #          dictionary with keys: "id" and "description"
    #
    def get_office_list(self):
        cursor = self.get_cursor()

        cursor.execute("""SELECT LEFT(description, IF(LOCATE("-", description) > 0, LOCATE("-", description), LENGTH(description))) AS temp1, CONVERT(RIGHT(description, LENGTH(description) - LOCATE("-", description)), SIGNED) AS temp2, office_id AS id, description, IF(number = "", "", CONCAT("(", SUBSTRING(number,1,3), ") ", SUBSTRING(number,4,3), "-", SUBSTRING(number, 7,4))) AS number, office_type FROM offices ORDER BY temp1, temp2, description, number""")

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int office_id
    # returns: dict describing the office
    #
    def get_office_details(self,office_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT office_id, description, number, capacity, office_type FROM offices WHERE office_id = %s""", (office_id,))

        if cursor.rowcount > 0:
            return cursor.fetchone()
        else:
            return {}

    #=============================================
    # accepts: string description
    #          string number
    #          int capacity
    #          string office_type
    # returns: 1 if the the new office was added, 0 if not
    #
    def add_new_office(self,description, number, capacity, office_type):
        cursor = self.get_cursor()

        cursor.execute("""INSERT INTO offices (description, number, capacity, office_type) VALUES (%s, %s, %s, %s)""", (description, number, capacity, office_type))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int office_id
    #           string description
    #           string number
    #           int capacity
    #           string office_type
    # returns: 1 if the details were updated successfully, 0 if not
    #
    def update_office_details(self,office_id, description, number, capacity, office_type):
        cursor = self.get_cursor()

        cursor.execute("""UPDATE offices SET description = %s, number = %s, capacity = %s, office_type = %s WHERE office_id = %s""", (description, number, capacity, office_type, office_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int office_id
    # returns: 1 if the office was deleted, 0 if not
    #
    def delete_office(self,office_id):
        cursor = self.get_cursor()

        # check if this office is empty
        cursor.execute("SELECT COUNT(person_id) AS occupant_total FROM people_to_offices_link AS pol WHERE pol.office_id = %s", (office_id,))

        record = cursor.fetchone()

        if record.get('occupant_total') == 0:
            cursor.execute("""DELETE FROM offices WHERE office_id = %s""", (office_id,))

            return 1 # we should be checking if this succeeded
        else:
            return 0

    # TODO: consider doing away with this option and just having them be added and deleted
    #=============================================
    # accepts: int link_id
    #          int office_id
    # returns: 1 if the office was updated successfully, 0 if not
    #
    def update_office_link(self,link_id, office_id):
        cursor = self.get_cursor()

        cursor.execute("UPDATE people_to_offices_link SET office_id = %s WHERE id = %s", (office_id, link_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int link_id
    # returns: 1 if the office link was deleted successfully, 0 if not
    #
    def delete_office_link(self,link_id):
        cursor = self.get_cursor()

        # remove the old phone number
        cursor.execute("""DELETE FROM phone_numbers WHERE (person_id, number) = (SELECT pol.person_id, o.number FROM people_to_offices_link AS pol LEFT JOIN offices AS o ON o.office_id = pol.office_id WHERE pol.id =  %s) AND number_type = "office" """, (link_id,))

        cursor.execute("DELETE FROM people_to_offices_link WHERE id = %s", (link_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person_id
    #          int office_id
    # returns: 1 if the office was added successfully, 0 if not
    #
    def add_office_link(self,person_id, office_id):
        cursor = self.get_cursor()

        # check if this person already has this phone number listed
        cursor.execute("""SELECT * FROM phone_numbers AS phn WHERE phn.person_id = %s AND phn.number_type = "office" AND phn.number = (SELECT o.number FROM offices AS o WHERE o.office_id = %s)""", (person_id, office_id))

        if cursor.rowcount == 0:
            # add the phone number of this office to their phone numbers (if the office has a default phone number)
            cursor.execute("SELECT o.number FROM offices AS o WHERE o.office_id = %s", (office_id,))

            if cursor.rowcount == 1:
                phone_number = cursor.fetchone().get('number')
                if len(phone_number) > 0:
                    add_phone_number(person_id, phone_number, "office")

        cursor.execute("INSERT INTO people_to_offices_link (person_id, office_id) VALUES (%s, %s)", (person_id, office_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int person id
    # returns: the name of the specified person
    #
    def get_person_name(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM person WHERE person_id = %s ORDER BY last_name, first_name", (person_id,))

        record = cursor.fetchone()

        if cursor.rowcount > 0:
            name = record.get('first_name') + " " + record.get('last_name')
            if len(record.get('suffix')) > 0:
                name += " " + record.get('suffix')
            return name
        else:
            return ""

    #=============================================
    # accepts: str student username
    # returns: str CUID
    #
    def get_cuid(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM CUIDs WHERE student_username = %s", (student_username,))

        if cursor.rowcount == 1:
            return cursor.fetchone()["CUID"]
        else:
            return ""

    #=============================================
    # accepts: str student username
    # returns: 1 if the CUID was deleted, 0 if not
    #
    def delete_cuid(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM CUIDs WHERE student_username = %s", (student_username,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #          str CUID
    # returns: 1 if the CUID was added, 0 if not
    #
    def add_cuid(self,student_username, cuid):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO CUIDs (student_username, CUID) VALUES (UPPER(%s), %s)", (student_username, cuid))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #           str CUID
    # returns: 1 if the CUID was updated, 0 if not
    #
    def update_cuid(self,student_username, cuid):
        cursor = self.get_cursor()

        cursor.execute("UPDATE CUIDs SET CUID = %s WHERE student_username = %s", (cuid, student_username))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    # returns: dict GRE score data
    #
    def get_gre_score(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM GRE_scores WHERE student_username = %s", (student_username,))

        if cursor.rowcount == 1:
            return cursor.fetchone()
        else:
            return {}

    #=============================================
    # accepts: str student username
    # returns: 1 if the GRE score was deleted, 0 if not
    #
    def delete_gre_score(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM GRE_scores WHERE student_username = %s", (student_username,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #          int verbal score
    #          int quantitative score
    #          float writing score
    # returns: 1 if the GRE score was added, 0 if not
    #
    def add_gre_score(self,student_username, verbal, quantitative, writing):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO GRE_scores (student_username, verbal_score, quantitative_score, writing_score) VALUES (UPPER(%s), %s, %s, %s)", (student_username, verbal, quantitative, writing))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #          int verbal score
    #          int quantitative score
    #          float writing score
    # returns: 1 if the GRE score was updated, 0 if not
    #
    def update_gre_score(self,student_username, verbal, quantitative, writing):
        cursor = self.get_cursor()

        cursor.execute("UPDATE GRE_scores SET verbal_score = %s, quantitative_score = %s, writing_score = %s WHERE student_username = %s", (verbal, quantitative, writing, student_username))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    # returns: int total TOEFL score for the student, or -1 if it is not found
    #
    def get_toefl_score(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM TOEFL_scores WHERE student_username = %s", (student_username,))

        if cursor.rowcount == 1:
            return cursor.fetchone().get('score')
        else:
            return -1

    #=============================================
    # accepts: str student username
    # returns: 1 if the GRE score was deleted, 0 if not
    #
    def delete_toefl_score(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM TOEFL_scores WHERE student_username = %s", (student_username,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #       int total TOEFL score
    # returns: 1 if the TOEFL score was added, 0 if not
    #
    def add_toefl_score(self,student_username, score):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO TOEFL_scores (student_username, score) VALUES (UPPER(%s), %s)", (student_username, score))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #          int total TOEFL score
    # returns: 1 if the TOEFL score was updated, 0 if not
    #
    def update_toefl_score(self,student_username, score):
        cursor = self.get_cursor()

        cursor.execute("UPDATE TOEFL_scores SET score = %s WHERE student_username = %s", (score, student_username))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    # returns: dict containing info about the student's degree program
    #
    def get_degree_program(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM degree_program WHERE student_username = %s", (student_username,))

        if cursor.rowcount > 0:
            return cursor.fetchone()
        else:
            return {}

    #=============================================
    # accepts: str student username
    # returns: 1 if the degree program was deleted, 0 if not
    #
    def delete_program(self,student_username):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM degree_program WHERE student_username = %s", (student_username,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #          str degree program
    #          str current degree
    #          str area
    #          str start semester
    #          int start year
    #          str end semester
    #          int end year
    # returns: 1 if the degree program was added, 0 if not
    #
    def add_program(self,student_username, program, cur_degree, area, start_sem, start_date, end_sem, end_date):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO degree_program(student_username, program, cur_degree, area, start_semester, start_year, end_semester, end_year) VALUES (UPPER(%s), %s, %s, %s, %s, %s, %s, %s)", (student_username, program, cur_degree, area, start_sem, start_date, end_sem, end_date))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str student username
    #          str degree program
    #          str current degree
    #          str area
    #          str start semester
    #          int start year
    #         str end semester
    #          int end year
    # returns: 1 if the degree program was updated, 0 if not
    #
    def update_program(student_username, program, cur_degree, area, start_sem, start_date, end_sem, end_date):
        cursor = self.get_cursor()

        cursor.execute("UPDATE degree_program SET program = %s, cur_degree = %s, area = %s, start_semester = %s, start_year = %s, end_semester = %s, end_year = %s WHERE student_username = %s", (program, cur_degree, area, start_sem, start_date, end_sem, end_date, student_username))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str employee username
    # returns: dict with the id (int) and the description (str)
    #
    def get_positions(self,employee_username):
        cursor = self.get_cursor()

        cursor.execute("SELECT fpl.id AS link_id, position_desc AS description FROM faculty_to_positions_link AS fpl INNER JOIN faculty_positions AS fp ON fpl.fac_position_id = fp.fac_position_id WHERE employee_username = %s ORDER BY position_desc", (employee_username,))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int position link id
    # returns: int position id
    #
    def get_position_id(self,position_link_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT fac_position_id FROM faculty_to_positions_link WHERE id = %s", (position_link_id,))

        if cursor.rowcount > 0:
            return cursor.fetchone().get('fac_position_id')
        else:
            return -1

    #=============================================
    # accepts: nothing
    # returns: list of positions, each entry is a
    #          dictionary with keys: "id" and "description"
    #
    def get_position_list(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT fac_position_id AS id, position_desc AS description FROM faculty_positions ORDER BY position_desc")

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: str position description
    # returns: 1 if the position was created, 0 if not
    #
    def add_new_position(self,position_description):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO faculty_positions (position_desc) VALUES (%s)", (position_description,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int link id
    #          int position_id
    # returns: 1 if the position was updated successfully, 0 if not
    #
    def update_position_link(self,link_id, position_id):
        cursor = self.get_cursor()

        cursor.execute("UPDATE faculty_to_positions_link SET fac_position_id = %s WHERE id = %s", (position_id, link_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: int link_id
    # returns: 1 if the position link was deleted successfully, 0 if not
    #
    def delete_position_link(self,link_id):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM faculty_to_positions_link WHERE id = %s", (link_id,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str employee username
    #          int position id
    # returns: 1 if the position was added successfully, 0 if not
    #
    def add_position_link(self,employee_username, position_id):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO faculty_to_positions_link (employee_username, fac_position_id) VALUES (%s, %s)", (employee_username, position_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str employee username
    # returns: str employee id
    #
    def get_employee_id(self,employee_username):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM employee_ids WHERE employee_username = %s", (employee_username,))

        if cursor.rowcount == 1:
            return cursor.fetchone().get('employee_id')
        else:
            return ""

    #=============================================
    # accepts: str employee username
    # returns: 1 if the employee id was deleted, 0 if not
    #
    def delete_employee_id(self,employee_username):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM employee_ids WHERE employee_username = %s", (employee_username,))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str employee username
    #           str employee id
    # returns: 1 if the employee id was added, 0 if not
    #
    def add_employee_id(self,employee_username, employee_id):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO employee_ids (employee_username, employee_id) VALUES (%s, %s)", (employee_username, employee_id))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: str employee username
    #           str employee id
    # returns: 1 if the employee id was updated, 0 if not
    #
    def update_employee_id(self,employee_username, employee_id):
        cursor = self.get_cursor()

        cursor.execute("UPDATE employee_ids SET employee_id = %s WHERE employee_username = %s", (employee_id, employee_username))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: nothing
    # returns: dict of schools
    #
    def get_school_list(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM schools ORDER BY name")

        return cursor.fetchall()

    #=============================================
    # accepts: str name
    #          str city
    #          str state
    #          str country
    # returns: 1 if the school was added successfully, 0 if not
    #
    def add_school(self,name, city, state, country):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO schools(name, city, state, country) VALUES (%s, %s, UPPER(%s), %s)", (name, city, state, country))

        return 1 # we should be checking if this succeeded

    #=============================================
    # accepts: none
    # returns: list of benchmark types
    #
    def get_benchmark_types(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM benchmark_types ORDER BY benchmark_description")

        return cursor.fetchall()

    #=============================================
    # accepts: int the selected benchmark_type_id
    # returns: the html for a drop down list of benchmark types
    #
    def get_benchmark_type_dropdown(self,selected):
        selected = int(selected)
        benchmark_types = self.get_benchmark_types()

        output = """<select name="benchmark_type_id">"""

        select_str = ""

        if selected == 0:
            # the first test will be selected by default
            select_str = " selected"

        for record in benchmark_types:
            if record.get('benchmark_type_id') == selected:
                select_str = " selected"
            output += f"""<option value="{record.get('benchmark_type_id')}"{select_str}>{record.get('benchmark_description')}</option>"""
            select_str = ""

        output += """
</select>
"""

        return output


    #=============================================
    # accepts: int the current list
    # returns: the html for a drop down list of people not in the list
    #
    def get_available_for_list(self,list_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM person AS p WHERE %s NOT IN (SELECT list_id FROM people_to_lists_link AS pll WHERE pll.person_id = p.person_id) ORDER BY last_name, first_name""", (list_id,))

        results = cursor.fetchall()

        output = """<select name="person_id">"""

        for record in results:
            output += f"""<option value="{record.get('person_id')}">{record.get('last_name')}, {record.get('first_name')}</option>"""

        output += """
</select>
"""

        return output


    #=============================================
    # accepts: str name
    #          str username
    # returns: list of people matching the search parameters
    #
    def get_search_results(self,first_name, last_name, username):
        cursor = self.get_cursor()

        if len(first_name) == 0:
            first_name = "%"

        if len(last_name) == 0:
            last_name = "%"

        if len(username) == 0:
            username = "%"

        cursor.execute("""
SELECT
 IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name,
 IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,
 p.person_id,
 IFNULL(old_employee_username, "") AS employee_username,
 IFNULL(old_student_username, "") AS student_username,
 IFNULL(username, "") AS username
FROM person AS p
WHERE
 (first_name LIKE %s OR
 pref_name LIKE %s OR
 display_name LIKE %s)
AND
 (last_name LIKE %s OR
 maiden_name LIKE %s)
AND
 (IFNULL(old_employee_username, "") LIKE %s OR
 IFNULL(old_student_username, "") LIKE %s OR
 IFNULL(username, "") LIKE %s)
ORDER BY last_name, first_name""", (first_name, first_name, first_name, last_name, last_name, username, username, username))

        result = cursor.fetchall()

        return result

    #TODO: make this compatible with the new username system
    #=============================================
    # accepts: nothing
    # returns: list of first year students and pertinent info
    #
    def get_first_year_report(self):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT
 p.person_id,
 first_name,
 last_name,
 IFNULL(s.student_username, "") AS student_username,
 IFNULL(cuids.CUID, "") AS CUID,
 IFNULL(e.employee_username, "") AS employee_username,
 IFNULL(emp_ids.employee_id, "") AS employee_id,
 program,
 cur_degree,
 area,
 start_semester,
 start_year,
 end_semester,
 end_year,
 IFNULL((SELECT GROUP_CONCAT(description ORDER BY description) FROM people_to_offices_link AS pol LEFT JOIN offices AS o ON o.office_id = pol.office_id WHERE pol.person_id = p.person_id GROUP BY p.person_id), "") AS offices,
 IFNULL((SELECT GROUP_CONCAT(CONCAT_WS(" ", first_name, last_name) ORDER BY last_name, first_name)
FROM advisors AS a_adv
LEFT JOIN students AS s_adv
ON s_adv.student_username = a_adv.student_username
LEFT JOIN employees AS emp_adv
ON emp_adv.employee_username = a_adv.employee_username
LEFT JOIN person AS p_adv
ON p_adv.person_id = emp_adv.person_id
WHERE
 a_adv.student_username = s.student_username
AND
 a_adv.advisor_type = "first"), "") AS advisors
FROM degree_program AS d
LEFT JOIN students AS s
ON s.student_username = d.student_username
LEFT JOIN person AS p
ON p.person_id = s.person_id
LEFT JOIN CUIDs AS cuids
ON cuids.student_username = s.student_username
LEFT JOIN employees AS e
ON e.person_id = p.person_id
LEFT JOIN employee_ids AS emp_ids
ON emp_ids.employee_username = e.employee_username
WHERE start_year = 2012
ORDER BY last_name, first_name""")

        return cursor.fetchall()

    #TODO: make this compatible with the new username system
    #=============================================
    # accepts: int month
    #          int year
    # returns: list of prelims taken during the given month and year
    #
    def get_prelim_report(month, year):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT first_name, last_name, b.student_username, p.person_id, benchmark_description, attempt_date, passed
FROM benchmarks AS b
LEFT JOIN benchmark_types AS bt
ON bt.benchmark_type_id = b.benchmark_type_id
LEFT JOIN students AS s
ON s.student_username = b.student_username
LEFT JOIN person AS p
ON p.person_id = s.person_id
WHERE
 EXTRACT(MONTH FROM attempt_date) = %s AND
 EXTRACT(YEAR FROM attempt_date) = %s
ORDER BY last_name, first_name
""", (month, year))

        return cursor.fetchall()

    def update_data_review_timestamp(self,person_id):
        cursor = self.get_cursor()

        cursor.execute("""INSERT INTO reviewed_info (person_id, last_reviewed) VALUES (%s, NOW()) ON DUPLICATE KEY UPDATE last_reviewed = NOW()""", (person_id,))

        return 1 # we should be checking to see if the UPDATE succeeded


    #=============================================
    # accepts: int the selected month number
    # returns: the html for a drop down list of months
    #
    def get_month_num_dropdown(self,selected):
        values = range(1,13)
        display_text = [month[:3] for month in calendar.month_name[1:]]
        selected_index = selected
        name = "month"
        output = self.get_dropdown_html(values,display_text,selected_index,name)

#         output = """<select name="month">"""

#         for i in range(1,len(calendar.month_name)):
#             month = calendar.month_name[i][:3]
#             select_str = ""
#             if i == selected:
#                 select_str = " selected"
#             output += f"""
#     <option value="{i}"{select_str}>{month}</option>"""

#         output += """
# </select>
# """

        return output

    #=============================================
    # accepts: nothing
    # returns: the html for the navigation menu
    #
    def get_menu(self):
        return """
<hr>
<div class="menu">
<ul>
    <li><a href="dept_info_main">Main</a></li>
    <li><a href="search">Search</a></li>
    <li><a href="reportsy">Reports</a></li>
</ul>
</div>
<hr style="clear: left;">
<div class="menu">
<ul>
    <li><a href="manage_people">Add People</a></li>
    <li><a href="manage_lists">Lists</a></li>
    <li><a href="manage_schools">Schools</a></li>
    <li><a href="manage_positions">Positions</a></li>
    <li><a href="office_main">Offices</a></li>
</ul>
</div>
<hr style="clear: left;">
"""


    #=============================================
    # accepts: int person_id
    #          int candidate_id
    #          int form_id
    #          dict form
    #          class cand_lib
    #
    # returns: str content
    #
    def evaluate_candidate_form_submit(self,person_id,candidate_id,form_id,form,cand_lib):
        content=""
        if ((dfl.is_cur_student(person_id) and form_id == 2) or \
            (dfl.is_cur_faculty(person_id) and form_id == 1)) and \
            cand_lib.valid_evaluator(person_id, candidate_id, form_id):
            # create a response
            response_id = cand_lib.create_response(candidate_id, form_id)

            question_list = cand_lib.get_form_data(form_id)

            for question in question_list:
                question_id = question.get('question_id')
                if question.get('question_type') == "multiple":
                    response = form.get(f'question_{question_id}')
                    # they may not have made a choice for this question
                    if len(response) > 0:
                        option_id = response.split("_")[1]
                        cand_lib.store_multiple_free_response(response_id, option_id, "")
                elif question.get('question_type') == "multiple_free":
                    response = form.get(f'question_{question_id}')
                    # they may not have made a choice for this question
                    if len(response) > 0:
                        option_id = response.split("_")[1]
                        option_comment = form.get(f'option_{option_id}_text')
                        cand_lib.store_multiple_free_response(response_id, option_id, option_comment)
                elif question["question_type"] == "free":
                    option_comment = form.get(f'question_{question_id}')
                    cand_lib.store_free_response(response_id, question_id, option_comment)

            # mark this person as having evaluated this candidate
            cand_lib.mark_candidate_as_evaluated(person_id, form_id, candidate_id)

            content = """<html>
<head>
        <meta HTTP-EQUIV="REFRESH" content="0; url=evaluation_list">
</head>
<body>
<p style="text-align: center;">Please wait while you are redirected back to the evaluation list page, or click <a href="evaluation_list">here</a> to go there now.</p>
</body>
</html>"""
        else:
                # tell them that they cannot evaluate this candidate
                content = """<html>
<head>
        <title>Form Error</title>
</head>
<body>
<p style="text-align: center;">Your evaluation could not be saved, click <a href="evaluation_list">here</a> to return to the evaluation list page.</p>
</body>
</html>"""

        return content

    #=============================================
    # accepts: int person_id
    #          int candidate_id
    #          class cand_lib
    #
    # returns: str name
    #          str school
    #          str visit_dates
    #          int form_id
    #          str form_html
    #
    def get_form_html(self,person_id,candidate_id,cand_lib):
        name=""
        school=""
        visit_dates=""

        form_id = 0
        if self.is_cur_faculty(person_id):
            form_id = 1
        elif self.is_cur_student(person_id):
            form_id = 2

        if person_id > 0 and form_id > 0 and cand_lib.valid_evaluator(person_id, candidate_id, form_id):
            info = cand_lib.get_candidate_info(candidate_id)

            if len(info) > 0:
                name = f"""{info.get('first_name')} {info.get('last_name')}"""
                school = info.get('school')
                visit_dates = info.get('visit_dates')

            # build the form
            form_data = cand_lib.get_form_data(form_id)

            form_html = ""

            for question in form_data:
                option_html = ""
                for option in question.get('options'):
                    if option.get('option_type') == "multiple":
                        option_html += f"""        <li><input id="option_{option.get('option_id')}" type="radio" name="question_{question.get('question_id')}"  value="option_{option.get('option_id')}" onClick="javascript:show_textbox(this);"> <label for="option_{option.get('option_id')}">{option.get('option_statement')}</label></li>\n"""
                    elif option.get('option_type') == "multiple_free":
                        # show textbox when this is selected
                        option_html += f"""        <li>
                <input id="option_{option.get('option_id')}" type="radio" name="question_{question.get('question_id')}"  value="option_{option.get('option_id')}" onClick="javascript:show_textbox(this);"> <label for="option_{option.get('option_id')}">{option.get('option_statement')}</label>
                <div><textarea id="option_{option.get('option_id')}_text" name="option_{option.get('option_id')}_text" class="multiple_free"></textarea></div>
        </li>\n"""
                    elif option.get('option_type') == "free":
                        option_html += f"""        <li><textarea name="question_{question.get('question_id')}" style="width: 700px; height: 50px;"></textarea></li>\n"""

                option_html = f"""
<ul class="option_list">
{option_html}
</ul>
"""

                form_html += f"""
<div class="question">
        <div class="question_statement">{question.get('question_statement')}</div>
        <div>
{option_html}
        </div>
</div>
"""
        else:
            # TODO: inform the person that they can't evaluate this candidate
            form_html = "You cannot evaluate this candidate. Either you have already done so or you are not authorized to do so."
        return name, school, visit_dates, form_id, form_html


    #=============================================
    # accepts: int person_id
    #          class cand_lib
    #
    # returns: str eval_list_html
    #
    def get_eval_list_html(self,person_id,cand_lib):
        if self.is_cur_student(person_id) \
           or self.is_cur_faculty(person_id) \
           or self.is_cur_staff(person_id):
            eval_list = cand_lib.get_evaluations_for_evaluator(person_id)

            if len(eval_list) > 0:
                cand_list_html=f"\n\t".join([f"""
<tr>
    <td class="candidate_cell">
        <form action=evaluate_candidate method="POST">
            <input type="submit" value="{item.get('last_name')}, {item.get('first_name')}">
            <input type="hidden" name="candidate_id" value="{item.get('candidate_id')}">
        </form>
    </td>
    <td class="candidate_cell">{item.get('school')}</td>
    <td class="candidate_cell" style="text-align: center;">{item.get('visit_dates')}</td>
    <td class="candidate_cell" style="text-align: center;">{item.get('close_date').strftime("%m/%d/%y").lstrip("0")}</td>
</tr>""" for item in eval_list])

                eval_list_html = f"""
        <table cellspacing="0">
                <tr>
                    <td class="list_title">Name</td>
                    <td class="list_title">School</td>
                    <td class="list_title">Visit Date(s)</td>
                    <td class="list_title">Evaluation Closes</td>
                </tr>
                {cand_list_html}
        </table>
        """
            else:
                eval_list_html = "You don't have any evaluations at this time."
        else:
            eval_list_html = "Only current faculty and graduate students can evaluate candidates."
        return eval_list_html


    #=============================================
    # accepts: str subject
    #          str email content
    #          str send to address
    #          bool copy
    #          str sent from address
    # returns: 1 if the email was sent
    #
    def send_email(self,subject, email_content, send_to, copy_email, sent_from):
        msg = MIMEText.MIMEText(email_content)

        msg["Subject"] = subject
        msg["From"] = sent_from
        msg["To"] = send_to

        s = smtplib.SMTP("localhost")
        s.sendmail(sent_from, send_to, msg.as_string())
        s.quit()

        if copy_email:
            msg["To"] = sent_from
            s = smtplib.SMTP("localhost")
            s.sendmail(sent_from, sent_from, msg.as_string())
            s.quit()

        return 1 # we should be checking if the email was actually sent
