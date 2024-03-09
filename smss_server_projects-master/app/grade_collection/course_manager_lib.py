#!/var/www/mthsc/common/venv/bin/python3

import datetime
import os
import smtplib
import sys
import types

import cx_Oracle
from .grade_collection_lib import grade_collection_lib
import MySQLdb

class course_manager_lib(grade_collection_lib):
    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary
    #
    def get_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)


    #=============================================
    # accepts: nothing
    # returns: string the current semester
    #
    def get_current_semester(self):
        cursor = self.get_cursor()

        sql = """SELECT value FROM course_settings WHERE name = "semester" """

        cursor.execute(sql)

        record = cursor.fetchone()

        return record.get('value','spring')


    #=============================================
    # accepts: nothing
    # returns: string the current year
    #
    def get_current_year(self):
        cursor = self.get_cursor()

        sql = """SELECT value FROM course_settings WHERE name = "year" """

        cursor.execute(sql)

        record = cursor.fetchone()

        now = datetime.datetime.now()
        # now = datetime.now()
        return record.get('value',str(now.year))


    #=============================================
    # accepts: string year
    # returns: 1 if the year was updated
    #
    def set_current_year(self, year):
        cursor = self.get_cursor()

        sql = """UPDATE course_settings SET value = %s WHERE name = "year" """

        cursor.execute(sql,(year,))

        sql = """UPDATE ug_pages.ug_page_settings SET value = %s WHERE name = "year" """

        cursor.execute(sql,(year,))

        return 1 # we should really be checking if the UPDATE succeeded


    #=============================================
    # accepts: string semester
    # returns: 1 if the semester was updated
    #
    def set_current_semester(self, semester):
        cursor = self.get_cursor()

        sql = """UPDATE course_settings SET value = %s WHERE name = "semester" """

        cursor.execute(sql,(semester,))

        sql = """UPDATE ug_pages.ug_page_settings SET value = %s WHERE name = "semester" """

        cursor.execute(sql,(semester,))

        return 1 # we should really be checking if the UPDATE succeeded


    #=============================================
    # accepts: string semester
    #          string year
    # returns: list of courses for the given semester and year
    #
    def get_semester_list(self, semester, year):
        cursor = self.get_cursor()

        sql = """
SELECT * FROM course_offerings AS co
LEFT JOIN course_list AS cl
ON co.course_id = cl.course_id
LEFT JOIN people_to_offers_link AS pol
ON co.offer_id = pol.offer_id
LEFT JOIN dept_info.person AS p
ON pol.employee_username = p.username
WHERE
 co.semester = %s AND
 co.year = %s
ORDER BY
 prefix, cl.course_num, co.section_num
"""

        cursor.execute(sql, (semester, year))

        result = cursor.fetchall()

        output = []
        last_id = 0

        for item in result:
            if item.get('offer_id') != last_id:
                # add this course offer to the list
                last_id = item.get('offer_id')
                course = {"offer_id": item.get('offer_id'), "prefix": item.get('prefix'), "course_num": item.get('course_num'), "section_num": item.get('section_num'), "instructors": []}
                output.append(course)

            # if type(item.get('employee_username')) is not types.NoneType:
            if type(item.get('employee_username')) is not type(None):
                # add this instructor to this offer
                output[-1].get('instructors').append({"employee_username": item.get('employee_username'), "first_name": item.get('first_name'), "last_name": item.get('last_name')})
            else:
                print(item)

        return output


    #=============================================
    # accepts: string info
    #
    # returns: html code for info box
    def info_box(self, info):
        return f"""
<div style="text-align: center; margin-top: 6px; margin-bottom: 3px; min-height: 30px;">
    <span id="info_box" style="display: inline-block; padding: 5px; background: #FFFF99; border: solid 2px #000000;">{info}</span>
</div>
"""


    #=============================================
    # accepts: string semester
    #          int year
    # returns: 1 (True) if the semester is in the past
    #          0 (False) if it is the present semester or in the future
    def is_past_semester(self, semester, year):
        semesters = ["spring","summer i","summer ii","fall"]

        # old code
        # if int(year) < int(self.get_current_year()):
        #     return 1 # this was last year
        # elif (int(year) == int(self.get_current_year())) and (semesters.index(semester.lower()) < semesters.index(self.get_current_semester())):
        #     return 1 # this was earlier this year
        # else:
        #     return 0 # this is the present semester or later

        pastYear=int(year) <  int(self.get_current_year())
        pastSem=(int(year) == int(self.get_current_year())) and (semesters.index(semester.lower()) < semesters.index(self.get_current_semester()))
        return pastYear or pastSem # True if (last year) or (same year and previous semester)


    #=============================================
    # accepts: string semester
    #          int year
    # returns: 1 (True) if the semester is the present semester
    #          0 (False) if it is not
    def is_current_semester(self, semester, year):
        return int(year) == int(self.get_current_year()) and semester.lower() == self.get_current_semester().lower()


    #=============================================
    # accepts: int offer_id
    # returns: 1 if the offer was deleted
    #
    def delete_offer(self,offer_id):
        cursor = self.get_cursor()

        # check that this offer is in the present or the future (we shouldn't delete offers from past semesters as this will break links to the info)
        cursor.execute("""SELECT semester, year FROM course_offerings WHERE offer_id = {}""".format(offer_id))

        if cursor.rowcount == 1:
            record = cursor.fetchone()

            if int(record.get('year')) < int(self.get_current_year()):
                return 0 # this is an old offer and we shouldn't delete it

            semesters = ["spring","summer i","summer ii","fall"]
            current_semester = self.get_current_semester()
            if semesters.index(record.get('semester')) < semesters.index(current_semester.lower()):
                return 0 # this is an old offer and we shouldn't delete it


        # we should be executing this as a stored procedure using a transaction

        cursor.execute("DELETE FROM course_offerings WHERE offer_id = {}".format(offer_id))

        cursor.execute("DELETE FROM people_to_offers_link WHERE offer_id = {}".format(offer_id))

        return 1 # we should be checking to see if both DELETE statements succeeded


    #=============================================
    # accepts: nothing
    # returns: 1 if all offers were deleted for the current year
    #
    def delete_all_offers(self):
        cursor = self.get_cursor()

        # we should be executing this as a stored procedure using a transaction

        cursor.execute("""
DELETE FROM pol
USING people_to_offers_link AS pol
INNER JOIN course_offerings AS co ON
 co.offer_id = pol.offer_id
WHERE
 semester = (SELECT value FROM course_settings WHERE name = "semester") AND
 year = (SELECT value FROM course_settings WHERE name = "year")""")

        cursor.execute("""
DELETE FROM course_offerings WHERE
 semester = (SELECT value FROM course_settings WHERE name = "semester") AND
 year = (SELECT value FROM course_settings WHERE name = "year")
""")

        return 1 # we should be checking to see if both DELETE statements succeeded


    #=============================================
    # accepts: str username
    #          str course prefix
    #          str course number
    #          str section number
    #          str crn
    #          str semester
    #          str year
    # returns: 1 if the offer was added
    #
    def add_course_offer(self,username, course_prefix, course_num, section_num, crn, semester, year):
        cursor = self.get_cursor()

        try:
            cursor.execute("SELECT offer_id FROM course_offerings WHERE course_id = (SELECT course_id FROM course_list WHERE prefix = %s AND course_num = %s) AND section_num = %s AND crn = %s AND semester = %s AND year = %s", (course_prefix, course_num, section_num, crn, semester, year))

            if cursor.rowcount > 0:
                return (False, f"""This offer ({course_prefix} {course_num}-{section_num}, CRN={crn}) already exists.""")

            cursor.execute("INSERT INTO course_offerings (course_id, section_num, crn, semester, year) VALUES ((SELECT course_id FROM course_list WHERE prefix = %s AND course_num = %s), %s, %s, %s, %s)", (course_prefix, course_num, section_num, crn, semester, year))

            new_offer_id = cursor.lastrowid

            self.add_people_to_offer_link(new_offer_id, username)

            return (True, new_offer_id)
        except MySQLdb.Error as err:
            return (False, f"""The course "{course_prefix} {course_num}" was not found in the system, please add it. Actual error was {str(err)}""")


    #=============================================
    # accepts: int offer id
    #          string section number
    #          string semester
    #          string year
    # returns: 1 if the offer was updated, 0 if not
    #
    def update_offer(self,offer_id, section_num, semester, year):
        cursor = self.get_cursor()

        cursor.execute("UPDATE course_offerings SET section_num = %s, semester = %s, year = %s WHERE offer_id = %s", (section_num, semester, year, offer_id))

        return 1 # we should be checking if the UPDATE succeeded


    #=============================================
    # accepts: str semester
    #          str year
    # returns: True if the course offers were downloaded, False otherwise
    #
    def download_Banner_course_offers(self, semester, year):
        # cursor = con.cursor()
        cursor = self.get_banner_cursor()

        # assume Fall semester by default
        semester_num = "08"

        if semester.lower() == "spring":
            semester_num = "01"
        elif semester.lower() == "summer i":
            semester_num = "05"
        elif semester.lower() == "summer ii":
            semester_num = "06"

        term = year + semester_num

        #cursor.execute("""SELECT CRN, SUBJECT_CODE, COURSE_NUMBER, SECTION_NUMBER FROM SIS_MTHSC_COURSE_SECT_ENROLL WHERE TERM_CODE = '%s' AND SUBJECT_CODE IN ('MTHS', 'EXST')""" % term)

        cursor.execute("""SELECT crn, subject_code, course_number, section_number, instructor_username FROM sis_mthsc_course_sect_prof WHERE term_code = '{}' AND subject_code IN ('MATH', 'STAT','DSA') ORDER BY subject_code, course_number, section_number, crn, instructor_username""".format(term))

        last_crn = ""
        last_offer_id = 0

        offers = cursor.fetchall()

        for offer in offers:
            if offer[0] == last_crn:
                self.add_people_to_offer_link(last_offer_id, offer[4])
            else:
                last_crn = offer[0]

                result = self.add_course_offer(offer[4], offer[1], offer[2], offer[3], offer[0], semester, year)

                if not result[0]:
                    return result

                last_offer_id = result[1]

        return (True, "")


    #=============================================
    # accepts: nothing
    # returns: list of courses
    #
    def get_course_list(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM course_list ORDER BY prefix, course_num")

        return cursor.fetchall()


    #=============================================
    # accepts: string course prefix
    #                    string course number
    #          string course description
    # returns: string outcome of creating the course
    #
    def create_course(self, course_prefix, course_num, course_desc):
        cursor = self.get_cursor()

        cursor.execute("SELECT COUNT(course_num) AS already_exists FROM course_list WHERE prefix = %s AND course_num = %s",(course_prefix, course_num))

        if cursor.fetchone().get('already_exists') != 0:
            return f"{course_prefix} {course_num} already exists"
        else:
            cursor.execute("INSERT INTO course_list (prefix, course_num, description) VALUES (%s, %s, %s)", (course_prefix, course_num, course_desc))

            return "new course created" # we should really be checking if the INSERT statement succeeded


    #=============================================
    # accepts: int offer_id
    #          string employee username
    # returns: 1 if the link was deleted, 0 if not
    #
    def delete_people_to_offer_link(self, offer_id, username):
        cursor = self.get_cursor()

        cursor.execute("DELETE FROM people_to_offers_link WHERE offer_id = %s AND employee_username = %s", (offer_id, username))

        return 1 # we should be checking if the DELETE succeeded


    #=============================================
    # accepts: int offer_id
    #          string employee username
    # returns: 1 if the link was added, 0 if not
    #
    def add_people_to_offer_link(self, offer_id, username):
        cursor = self.get_cursor()

        if username is not None and username != "":
            cursor.execute("INSERT INTO people_to_offers_link (offer_id, employee_username) VALUES (%s, %s)", (offer_id, username))
            return 1
        else:
            return 0 # we should be checking if the INSERT succeeded


    #=============================================
    # accepts: int offer_id
    # returns: info about the offer
    #
    def get_offer_info(self, offer_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT * FROM course_offerings AS co
LEFT JOIN people_to_offers_link AS pol
ON co.offer_id = pol.offer_id
LEFT JOIN course_list AS cl
ON cl.course_id = co.course_id
WHERE co.offer_id = {}
""".format(offer_id))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return {}


    #=============================================
    # accepts: nothing
    # returns: html for a dropdown list of current_faculty and grad students
    #
    def get_current_teachers_dropdown(self, selected, extras=''):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM dept_info.people_to_lists_link AS pll LEFT JOIN dept_info.lists AS l ON l.list_id = pll.list_id LEFT JOIN dept_info.person AS p ON p.person_id = pll.person_id WHERE l.list_name = "current faculty" OR l.list_name = "current students" ORDER BY last_name, first_name""")

        output = f"""<select id="employee_username" name="employee_username" {extras}>"""

        results = cursor.fetchall()

        selected_str = ""

        if selected == "":
            #select first item in list
            selected_str = " selected"

        for record in results:
            if selected == record.get('username'):
                selected_str = " selected"

            output += f"""
    <option value="{record.get('username')}"{selected_str}>{record.get('last_name')}, {record.get('first_name')} ({record.get('username')})</option>"""
            selected_str = ""

        return output + "</select>"


    #=============================================
    # accepts: string course prefix
    #          string course number
    #          string username
    #          string semester
    #          string year
    # returns: 1 if the coordinator was added
    #
    def add_course_coordinator(self, prefix, course_num, username, semester, year):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO course_coordinators (course_id, employee_username, semester, year) VALUES ((SELECT course_id FROM course_list WHERE prefix = %s AND course_num = %s), %s, %s, %s)", (prefix, course_num, username, semester, year))

        return 1 # we should be checking that the INSERT statement succeeded

    # accepts: string semester to copy to
    #          int year to copy to
    #          string semester to copy from
    #          int year to copy from
    # returns: string success message
    #
    def import_course_coordinators(self, semester, year, semester_import, year_import):
        courses = self.get_semester_coordinators(semester_import, year_import)
        if len(courses) > 0:
            for course in courses:
                prefix    =course.get('prefix')
                course_num=course.get('course_num')
                username  =course.get('username')
                self.add_course_coordinator(prefix,course_num,username,semester,year)
                info="Course coordinators successfully copied"
        else:
            info="No coordinators to import"
        return info

    #=============================================
    # accepts: string semester
    #          string year
    # returns: list of courses for the given semester and year
    #
    def get_semester_coordinators(self, semester, year):
        cursor = self.get_cursor()

        sql = """
SELECT * FROM course_coordinators AS cc
LEFT JOIN course_list AS cl
ON cc.course_id = cl.course_id
LEFT JOIN dept_info.person as per
ON per.username = cc.employee_username
WHERE
 cc.semester = %s AND
 cc.year = %s
ORDER BY
 prefix, cl.course_num
"""

        cursor.execute(sql, (semester, year))

        result = cursor.fetchall()

        return result


    #=============================================
    # accepts: int coordinator_id
    # returns: 1 if the offer was deleted
    #
    def delete_coordinator(self, coordinator_id):
        cursor = self.get_cursor()

        # check that this offer is in the present or the future (we shouldn't delete offers from past semesters as this will break links to the info)
        cursor.execute("SELECT semester, year FROM course_coordinators WHERE coordinator_id = %s", (coordinator_id,))

        if cursor.rowcount == 1:
            record = cursor.fetchone()

            if int(record.get('year')) < int(self.get_current_year()):
                return 0 # this is an old coordinator and we shouldn't delete it

            semesters = ["spring","summer i","summer ii","fall"]
            current_semester = self.get_current_semester().lower()
            if semesters.index(record.get('semester').lower()) < semesters.index(current_semester.lower()):
                return 0 # this is an old coordinator and we shouldn't delete it

        cursor.execute("DELETE FROM course_coordinators WHERE coordinator_id = %s",(coordinator_id,))

        return 1 # we should be checking to see if both DELETE statements succeeded


    #=============================================
    # accepts: nothing
    # returns: 1 if all the coordinators were deleted for the current semester and year
    #
    def delete_all_coordinators(self):
        cursor = self.get_cursor()

        cursor.execute("""
DELETE FROM course_coordinators WHERE
 semester = (SELECT value FROM course_settings WHERE name = "semester") AND
 year = (SELECT value FROM course_settings WHERE name = "year")
""")

        return 1 # we should be checking to see if both DELETE statements succeeded

    #=============================================
    # accepts: int coord_id
    # returns: message indicating which course coordinators have been deleted
    #
    def delete_course_coords(self,coord_id):
        if coord_id == -1:
            # delete all offers for this semester
            result = self.delete_all_coordinators()
            info = "The course coordinators were successfully deleted"

        if coord_id > 0:
            result = self.delete_coordinator(coord_id)
            if result == 1:
                info = "Course coordinator successfully deleted"
            else:
                info = "The course coordinator was not deleted. You cannot delete course coordinators from past semesters"
        return info

    #=============================================
    # accepts: string semester
    #          int year
    #          string info
    # returns: html for course manager view courses page
    #
    def get_html_for_view_offers(self,semester,year,info=''):
        # PW 2022-01-14: Title from old "course manager" system
        # html=f"""
# <div style="width: 800px; margin: 0 auto; text-align: left; margin-top: 10px;">
# <div class="course_manager_title">View Course Offers for {semester[0].capitalize()+semester[1:]} {year}</div>
# """

        html = self.info_box(info)

        courses = self.get_semester_list(semester, year)
        current_semester=self.is_current_semester(semester, year)

        # only give the user the delete or edit option for current semester offers
        delete_all_offers_html=f"""
        <td colspan="2">
        <form action="javascript:course_manager_AJAX('delete_offers_all');" name="delete_offers_all" method="POST">
              <input type="hidden" name="form_name" value="view_offers">
              <input type="hidden" name="action" value="delete_offers">
              <input type="hidden" name="offer_id" value="-1">
              <input type="hidden" name="semester" value="{semester}">
              <input type="hidden" name="year" value="{year}">
              <input type="image" src="static/images/del.png" style="border: none;" title="Delete All" alt="Submit"> Delete All
          </form>
        </td>"""

        html+= f"""<table class="course_manager_outline" cellspacing="0">
        <tr style="font-weight: bold;">
            {delete_all_offers_html if current_semester else ""}
            <td>Course</td>
            <td>Section</td>
            <td>Faculty</td>
            <td>Name</td>
        </tr>"""

        for course in courses:
            instructors_length = len(course.get('instructors'))
            if instructors_length > 0:
                instructor_span = f"""rowspan="{instructors_length}" """
            else:
                instructor_span = ""

            offer_id=course.get('offer_id')
            delete_edit_html=f"""
            <td {instructor_span} style="text-align: center;">
              <form action="javascript:course_manager_AJAX('delete_offers_{offer_id}');" name="delete_offers_{offer_id}" method="POST">
              <input type="hidden" name="form_name" value="view_offers">
              <input type="hidden" name="action" value="delete_offers">
              <input type="hidden" name="offer_id" value="{offer_id}">
              <input type="hidden" name="semester" value="{semester}">
              <input type="hidden" name="year" value="{year}">
              <input type="image" src="static/images/del.png" style="border: none;" title="Delete" alt="Submit">
              </form>
            </td>
            <td {instructor_span} style="text-align: center;">
              <form action="javascript:course_manager_AJAX('edit_offer_{offer_id}');" name="edit_offer_{offer_id}" method="POST">
              <input type="hidden" name="form_name" value="view_offers">
              <input type="hidden" name="action" value="edit_offers">
              <input type="hidden" name="offer_id" value="{offer_id}">
              <input type="hidden" name="semester" value="{semester}">
              <input type="hidden" name="year" value="{year}">
              <input type="image" src="static/images/edit.png" style="border: none;" title="Edit" alt="Submit">
              </form>
            </td>

"""

            html += f"""
       <tr>
                {delete_edit_html if current_semester else ""}
              <td {instructor_span}>{course.get('prefix')} {course.get('course_num')}</td>
              <td {instructor_span}>{course.get('section_num')}</td>
"""

            if instructors_length > 0:
                for i in range(0, instructors_length):
                    person = course.get('instructors')[i]
                    name_str = ""
                    # if type(person['last_name']) is not types.NoneType and type(person['first_name']) is not types.NoneType:
                    if type(person.get('last_name')) is not type(None) and type(person.get('first_name')) is not type(None):
                        name_str = person.get('first_name') + " " + person.get('last_name')
                    if i != 0:
                        html += """
        <tr>
"""

                    html += f"""
                <td>{person.get('employee_username')}</td>
                <td>{name_str}</td>
        </tr>
"""
            else:
                html += """
                <td></td>
                <td></td>
        </tr>
"""

        html += "</table>"
        return html


    #=============================================
    # accepts: int offer_id
    #          string semester
    #          int year
    #          string info
    #
    # returns: html for course manager edit course page
    #
    def get_html_for_edit_offers(self, offer_id, semester, year, info=''):
        offer_info = self.get_offer_info(offer_id)

        if len(offer_info) == 0:
            content = "The requested course offer was not found"
        else:
            if len(offer_info) > 1:
                teacher_list = []
                for i in range(0,len(offer_info)):
                    teacher_list.append(offer_info[i].get('employee_username'))
                offer_info = offer_info[0]
                offer_info["employee_username"] = teacher_list
            else:
                offer_info = offer_info[0]

                if type(offer_info.get('employee_username')) is type(None):
                        offer_info["employee_username"] = []
                else:
                        offer_info["employee_username"] = [offer_info.get('employee_username')]

            employee_html = f"""
<div id="teacher_list container" style="min-height: 25px;">""" + "".join([f"""<div><img src="static/images/del.png" style="margin-right: 5px; cursor: pointer;" onClick="javascript:delete_teacher('{username}', this)">{username}</div>""" for username in offer_info.get('employee_username')])
            employee_html+= f"""
</div>
<hr><div><img src="static/images/add.png" style="margin-right: 5px; cursor: pointer;" onClick="javascript:add_teacher()">{self.get_current_teachers_dropdown("")}</div>"""

            semester_dropdown=self.get_semester_dropdown(offer_info.get('semester'), menu_name='new_semester')
            year_dropdown=self.get_year_dropdown(offer_info.get('year'), first_year=2008, menu_name='new_year')
            content = f"""
<table class="course_manager_outline" cellspacing="0">
        <tr>
                <td>Course</td>
                <td>Section</td>
                <td>Teacher(s)</td>
                <td>Semester</td>
                <td>Year</td>
        </tr>
        <tr>
                <td style="vertical-align: top;">{offer_info.get('prefix')} {offer_info.get('course_num')}</td>
                <td style="vertical-align: top;"><input id="section_num" name="section_num" value="{offer_info.get('section_num')}" size="5"></td>
                <td style="vertical-align: top;">{employee_html}</td>
                <td style="vertical-align: top;">{semester_dropdown}</td>
                <td style="vertical-align: top;">{year_dropdown}</td>
        </tr>
        <tr>
                <td colspan="6" style="text-align: center;">
                        <input type="submit" value="Save Changes">
                </td>
        </tr>
</table>
"""

        html=f"""
{self.info_box(info)}
<form action="javascript:course_manager_AJAX('view_offers_data')" name="view_offers_data" method="POST">
<div style="margin-bottom: 15px;">
</div>
{content}
        <input type="hidden" id="action" name="action" value= "edit_offer_submit">
        <input type="hidden" id="add_teachers" name="add_teachers" value="">
        <input type="hidden" id="delete_teachers" name="delete_teachers" value="">
        <input type="hidden" id="form_name" name="form_name" value= "view_offers">
        <input type="hidden" id="offer_id" name="offer_id" value="{offer_id}">
        <input type="hidden" id="semester" name="semester" value="{semester}">
        <input type="hidden" id="year" name="year" value="{year}">
</form>

"""
        return html


    #=============================================
    # accepts: int offer_id
    #          string semester
    #          int year
    #
    # returns: html for course manager add course offer page
    #
    def get_html_for_add_offers(self, semester, year, info=''):
        content = f"""
<div style="margin: 10px; text-align: left">
{self.info_box(info)}
You can pull the course offers currently listed in Banner by clicking the button below. This will not &quot;update&quot; the courses. Instead, it will add each course listed in Banner as a new course in the MthSc database. If you want to update the course offers you will need to to delete the current courses and then re-add them all.
<!--Note: this will break any links with the syllabus manager and course offers so once the syllabus manager is opened this <span class="emphasis">should not be done</span>.</div>-->
<form action="javascript:course_manager_AJAX('get_banner_offers')" name="get_banner_offers" method="POST">
<p>
<input type="submit" value="Get Banner Offers">
<input type="hidden" name="form_name" value="add_offers">
<input type="hidden" name="action" value="get_banner_offers">
<input type="hidden" name="semester" value={semester}>
<input type="hidden" name="year" value={year}></p>
</div>
</form>
<hr>
<form action="javascript:course_manager_AJAX('add_offer')" name="add_offer" method="POST">
    <table style="margin: 0px auto;">
        <tr>
            <td>Semester</td>
            <td>Year</td>
            <td>Prefix</td>
            <td>Course<br>Number</td>
            <td>Section<br>Number</td>
            <td>CRN</td>
            <td>Instructor Usernames<br>(use commas to separate usernames)</td>
        </tr>
        <tr>
            <td><input type="text" id="semester" name="semester" value="{semester.title().replace("Ii","II")}" size="5" readonly></td>
            <td><input type="text" id="year" name="year" value="{year}" size="5" readonly></td>
            <td style="vertical-align: top; text-align: center;">
                <select id="course_prefix" name="course_prefix">
	            <option value="MATH">MATH</option>
	            <option value="STAT">STAT</option>
	            <option value="DSA">DSA</option>
	        </select>
	    </td>
            <td><input type="text" id="course_num" name="course_num" value="" size="5"></td>
            <td><input type="text" id="section_num" name="section_num" value="" size="5"></td>
            <td><input type="text" id="crn" name="crn" value="" size="5"></td>
            <td><input type="text" id="usernames" name="usernames" value="" size="35"></td>
        </tr>
    </table>
    <div style="text-align: center;">
        <input type="submit" value="Add Offer">
        <input type="hidden" name="form_name" value="add_offers">
        <input type="hidden" name="action" value="add_offer_upload">
    </div>
</form>
"""
        return content


    #=============================================
    # accepts: int offer_id
    #          int section_num
    #          string semester
    #          string year
    #
    # returns: edits course offer
    #          and displays success/failure message
    def edit_course_offer(self, offer_id, section_num, semester, year, del_teachers, add_teacher_str):
        self.update_offer(offer_id, section_num, semester, year)

        # delete the teachers that were deleted
        for teacher in del_teachers:
            self.delete_people_to_offer_link(offer_id, teacher)

        # add the teachers that were added
        if len(add_teacher_str) > 0:
            add_teachers = add_teacher_str.split(",")
            for teacher in add_teachers:
                self.add_people_to_offer_link(offer_id, teacher)

        info = "Course info updated"
        return info


    #=============================================
    # accepts: int offer_id
    #
    # returns: deletes appropriate course offers
    #          and displays success/failure message
    def delete_course_offers(self, offer_id):
        if int(offer_id)==-1:     # delete all offers for this semester
            result = self.delete_all_offers()
            info = "The course offers were successfully deleted"
        elif int(offer_id) > 0:   # delete this specific offer
            result = self.delete_offer(offer_id)
            if result == 1:
                info = "Course offer successfully deleted"
            else:
                info = "The course offer was not deleted, you cannot delete course offers from past semesters"
        return info


    #=============================================
    # accepts: string usernames
    #          string prefix
    #          int course_num
    #          int section_num
    #          int crn
    #          string year
    #          int year
    #
    # returns: adds new course offer to database
    def add_offer_upload(self, usernames,prefix,course_num,section_num,crn,semester,year):
        if len(usernames) > 0:
            usernames = usernames.split(",")
            usernames = [name.strip() for name in usernames]
        else:
            usernames = []

        result = self.add_course_offer("", prefix, course_num, section_num, crn, semester, year)

        if result[0]:
            # link usernames
            offer_id = result[1]
            for username in usernames:
                self.add_people_to_offer_link(offer_id, username)

            # content = """<div style="font-size: 16pt; color: red;">Offer successfully added.</div>"""
            content = """Offer successfully added."""
        else:
            # content = """<div style="font-size: 16pt; color: red;">""" + result[1] + "</div>"
            content = result[1]

        return content


    #=============================================
    # accepts: int offer_id
    #          string semester
    #          int year
    #
    # returns: html for course manager view course coordinators page
    #
    def get_html_for_view_coords(self, semester, year, info='', coord_id=''):
        html=self.info_box(info)

        courses = self.get_semester_coordinators(semester, year)
        current_semester=self.is_current_semester(semester, year)

        if len(courses)>0:
            delete_all_coords_html=f"""
<td colspan="2">
<form action="javascript:course_manager_AJAX('delete_coords_all');" name="delete_coords_all" method="POST">
    <input type="hidden" name="form_name" value="view_coords">
    <input type="hidden" name="action" value="delete_coords">
    <input type="hidden" name="coord_id" value="-1">
    <input type="hidden" name="semester" value="{semester}">
    <input type="hidden" name="year" value="{year}">
    <input type="image" src="static/images/del.png" style="border: none;" title="Delete All" alt="Submit"> Delete All
</form>
</td>
"""

            html += f"""
    <table class="course_manager_outline" cellspacing="0">
        <tr style="font-weight: bold;">
            {delete_all_coords_html if current_semester else ""}
            <td>Course</td>
            <td>Faculty</td>
            <td width="320px">Name</td>
        </tr>"""

            for course in courses:
                this_coord_id = course.get('coordinator_id')
                this_coord_username = course.get('employee_username')
                this_course_id = course.get('course_id')

                submit_button=f"""
<!--input form="edit_coord_form" type="submit" value="Save Changes" title="Submit" alt="Submit"-->
<input form="edit_coord_form" type="image" src="static/images/green_checkmark.svg" style="vertical-align: text-top" title="Submit" alt="Submit">
"""
                edit_button=f"""
<form action="javascript:course_manager_AJAX('edit_coord_{this_coord_id}');" name="edit_coord_{this_coord_id}" method="POST">
    <input type="hidden" name="form_name" value="view_coords">
    <input type="hidden" name="action" value="edit_coords">
    <input type="hidden" name="coord_id" value="{this_coord_id}">
    <input type="hidden" name="semester" value="{semester}">
    <input type="hidden" name="year" value="{year}">
    <input type="image" src="static/images/edit.png" style="border: none;" title="Edit" alt="Submit">
</form>
"""

                delete_edit_html=f"""
<td style="text-align: center;">
<form action="javascript:course_manager_AJAX('delete_coords_{this_coord_id}');" name="delete_coords_{this_coord_id}" method="POST">
    <input type="hidden" name="form_name" value="view_coords">
    <input type="hidden" name="action" value="delete_coords">
    <input type="hidden" name="coord_id" value="{this_coord_id}">
    <input type="hidden" name="semester" value="{semester}">
    <input type="hidden" name="year" value="{year}">
    <input type="image" src="static/images/del.png" style="border: none;" title="Delete" alt="Submit">
</form>
</td>
<td style="text-align: center;">
{submit_button if str(this_coord_id) == str(coord_id) else edit_button}
</td>
"""

                name_col_html=f"""
{f'''<form action="javascript:course_manager_AJAX('edit_coord_form')" id='edit_coord_form' name='edit_coord_form' method="POST">
<input type="hidden" name="action" value="upload_edit_coords">
<input type="hidden" name="coord_id"  value="{this_coord_id}">
<input type="hidden" name="course_id" value="{this_course_id}">
<input type="hidden" name="course_num" value="{course.get('course_num')}">
<input type="hidden" name="course_prefix" value="{course.get('prefix')}">
<input type="hidden" name="form_name" value="view_coords">
<input type="hidden" name="semester"  value="{semester}">
<input type="hidden" name="year"  value="{year}">
{self.get_current_teachers_dropdown(this_coord_username)} </form>'''
if str(this_coord_id) == str(coord_id) else f'''{course.get('first_name','')} {course.get('last_name','')}'''}
"""

                html += f"""
        <tr>
            {delete_edit_html if current_semester else ""}
            <td> {course.get('prefix')} {course.get('course_num')} </td>
            <td> {this_coord_username} </td>
            <td> {name_col_html} </td>
        </tr>"""

            html +=f"""
    </table>"""

        else:
            html+=f"""
<form action="javascript:course_manager_AJAX('import_coord_form')" name='import_coord_form' method="POST">
  <p> Use the menu below to copy course coordinators from previous semesters</p>
    <table>
        <tr>
            <td> {self.get_semester_dropdown("", menu_name="import_semester")} </td>
            <td> {self.get_year_dropdown(int(year)-1, menu_name="import_year")} </td>
            <td> <input type="submit" value="Copy coordinators"> </td>
            <input type="hidden" name="form_name" value="view_coords">
            <input type="hidden" name="action" value="import_coords">
            <input type="hidden" name="semester" value={semester}>
            <input type="hidden" name="year" value={year}>
        </tr>
    </table>
</form>
"""

        return html

    #=============================================
    # accepts: int offer_id
    #          string semester
    #          int year
    #
    # returns: html for course manager add course coordinator page
    #
    def get_html_for_add_coords(self, semester, year, info='', coord_id=''):
        html=self.info_box(info)

        html+=f"""
<div style="margin-bottom: 10px; text-align: left;">This page adds course coordinators to a given semester. If changes to the course coordinator are needed, then click the edit or delete link for the course offer from the View Course Cordinators page.</div>
<div class="course_manager_format" style="text-align: left;">
    <span>Format:</span> MATH,1080,BLACK5
    <br><span>Filename:</span> course_coords.txt</div>

<form action="javascript:course_manager_AJAX('add_coords_form')" name="add_coords_form" method="POST" enctype="multipart/form-data">
<div style="margin-bottom: 15px; text-align: left;">
</span>
Choose the data file from your computer (in &lt;comma&gt; delimited format) <br>
<input type="file" id="file_add_coords" name="file_add_coords"><br>
Or copy and paste the data into the text area below. (in &lt;comma&gt; delimited format)
<div style="text-align: center;">
    <textarea id="coords_data" name="coords_data" rows="15" cols="80" wrap="off"></textarea><br>
    <input type="submit" value="Upload">
    <input type="hidden" name="form_name" value="add_coord">
    <input type="hidden" name="action" value="upload_new_coord">
</div>
</div>
</form>
"""
        return html


    #=============================================
    # accepts: string semester
    #          int year
    #          <type> data file
    #          string data
    #
    # returns: html for course manager add course coordinator page
    #
    def upload_add_coords(self, semester, year, coords_data):
        info=''
        delimiter = "\t"

        data = coords_data.rstrip().split("\n")
        try:
            for entry in data:
                # replace commas with the delimiter
                temp =entry.replace(",", delimiter).replace(" ","").split(delimiter) # splits string into prefix, course number, coordinator username

                prefix = temp[0]
                course_num = temp[1]
                username = temp[2].upper()

                # now we insert the data into the database
                self.add_course_coordinator(prefix, course_num, username, semester, year)
            info="Data successfully uploaded"
        except:
            info="Error in data uploaded. Please check format."

        return info


    #=============================================
    # accepts: string semester
    #          int year
    #          string info
    #
    # returns: html for create course page
    #
    def get_html_for_create_course(self, semester, year, info=''):
        html=self.info_box(info)

        content = f"""
<table class="course_manager_outline" cellspacing="0">
    <tr>
        <td style="font-weight: bold;">Course Prefix</td>
        <td style="font-weight: bold;">Course Number</td>
        <td style="font-weight: bold;">Description</td>
    </tr>
"""
        course_list = self.get_course_list()

        for course in course_list:
            content += f"""
        <tr>
            <td>{course.get('prefix')}</td>
            <td>{course.get('course_num')}</td>
            <td>{course.get('description')}</td>
        </tr>
"""

        content += "</table>"

        html+=f"""
<div class="course_manager_content_panel" style="text-align: left;">
    <p>To create a new course, enter the 4-digit course number and the catalog description below. Then click the Create Course button at the bottom of the form. The courses that exist already are listed below as well.</p>
    <p><span style="font-weight: bold;">Note:</span> This form creates a course that does not exist currently. If you want to add a course offer for a semester, use the Add Offers page by clicking on the Add Offers button in the menu above.</p>
<form action="javascript:course_manager_AJAX('create_course_form')" name="create_course_form" method="POST">
    <table>
        <tr>
          <td style="font-weight: bold; padding: 5px;">Course Prefix</td>
          <td style="font-weight: bold; padding: 5px;">Course Number</td>
          <td style="font-weight: bold; padding: 5px;">Catalog Description</td>
        </tr>
        <tr>
          <td style="vertical-align: top; text-align: center;">
            <select id="course_prefix" name="course_prefix">
            <option value="MATH">MATH</option>
            <option value="STAT">STAT</option>
            <option value="DSA">DSA</option>
            </select>
          </td>
          <td style="vertical-align: top; text-align: center;">
            <input type="text" size="4" id="course_num" name="course_num">
          </td>
          <td>
            <textarea type="text" style="width: 400px; height: 50px;" id="course_desc" name="course_desc"></textarea>
          </td>
        </tr>
        <tr>
          <td colspan="3" style="text-align: center;">
            <input type="submit" value="Create Course">
            <input type="hidden" name="form_name" value="create_course">
            <input type="hidden" name="action" value="upload_new_course">
            <input type="hidden" name="semester" value={semester}>
            <input type="hidden" name="year" value={year}>
          </td>
        </tr>
    </table>
</form>
{content}

</div>
</div>
"""
        return html



    #=============================================
    # accepts: string semester
    #          int year
    #          string info
    #
    # returns: html for settings page
    #
    def get_html_for_settings_page(self, semester, year, info=''):

        html=self.info_box(info)

        html+=f"""
<div class="course_manager_content_panel" style="margin-bottom: 10px; margin-top: 10px; text-align: left;">Update the current semester or year by choosing from the drop down lists above. Click the 'Update Settings' button at the bottom of the page to save your changes. This provides a safeguard against deleting old class offers. You can only delete class offers that are present or future based on these settings.</div>
<div style="margin-bottom: 10px; text-align: left;">
<form action="javascript:course_manager_AJAX('settings_form')" name="settings_form" method="POST">
        <table style="margin-bottom: 15px;">
        <tr>
        <td>Semester</td>
        <td>Year</td>
        </tr>
        <tr>
        <td>
        <input type="text" id="semester" name="semester" value="{semester.title().replace("Ii","II")}" size="5" readonly>
        </td>
        <td>
        <input type="text" id="year" name="year" value="{year}" size="5" readonly>
        </td>
        <td>
        <input type="submit" value="Update Settings">
        <input type="hidden" name="form_name" value="settings">
        <input type="hidden" name="action" value="save_settings">
        </td>
        </tr>
        </table>
        </form>
"""

        return html
