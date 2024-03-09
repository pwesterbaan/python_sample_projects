#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import os
import sys

import MySQLdb
import cx_Oracle
import io
import smtplib
import xmlrpc.client
from dateutil.relativedelta import relativedelta
from email.mime import text as MIMEText

sys.path.append("/var/www/mthsc/common") #delete/comment out when running on apache server
from common_lib import commonFunctions

class cmpt_lib(commonFunctions):

    #PW 2022-06-03: mthscID is generated using MD5 hash on
    #   f'{username.lower()+self.hash_append}' when adding students
    #   to eligibility list
    #================
    # this was created by CCIT when they set up the Shibboleth token with ALEKS
    hash_append = "hash_append"
    #=================


    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary
    #
    def get_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)


    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary
    #
    def get_prereq_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)

    #=============================================
    # accepts: str ALEKS_class_code as cohort
    # returns: list of scores
    def get_best_scores(self,cohort):
        cursor = self.get_cursor()
        # we now have people in multiple cohorts
        if cohort == "all":
            cursor.execute("SELECT * FROM cmpt_scores AS cs WHERE cs.test_number = (SELECT test_number FROM cmpt_scores WHERE mthscID = cs.mthscID ORDER BY score DESC, test_number DESC LIMIT 1) AND cs.ALEKS_class_code = (SELECT ALEKS_class_code FROM cmpt_scores WHERE mthscID = cs.mthscID AND approval = 'accepted' ORDER BY score DESC, test_number DESC LIMIT 1) ORDER BY date_ended DESC, time_ended DESC")
        else:
            cursor.execute("SELECT * FROM cmpt_scores AS cs WHERE cs.test_number = (SELECT test_number FROM cmpt_scores WHERE mthscID = cs.mthscID AND approval = 'accepted' ORDER BY score DESC, test_number DESC LIMIT 1) AND cs.ALEKS_class_code = '{}' ORDER BY date_ended DESC, time_ended DESC".format(cohort))
        return cursor.fetchall()


    #=============================================
    # accepts: str ALEKS_class_code as cohort
    # returns: list of scores
    def get_best_engr_scores(self,cohort):
        cursor = self.get_cursor()
        # we now have people in multiple cohorts
        if cohort == "all":
            cursor.execute("SELECT * FROM cmpt_scores AS cs WHERE cs.test_number = (SELECT test_number FROM cmpt_scores WHERE mthscID = cs.mthscID ORDER BY score DESC, test_number DESC LIMIT 1) AND cs.ALEKS_class_code = (SELECT ALEKS_class_code FROM cmpt_scores WHERE mthscID = cs.mthscID AND approval = 'accepted' ORDER BY score DESC, test_number DESC LIMIT 1) AND ALEKS_class_code IN (SELECT ALEKS_class_code FROM cohorts WHERE student_type = 'ENGR') ORDER BY date_ended DESC, time_ended DESC")
        else:
            cursor.execute("SELECT * FROM cmpt_scores AS cs WHERE cs.test_number = (SELECT test_number FROM cmpt_scores WHERE mthscID = cs.mthscID AND approval = 'accepted' ORDER BY score DESC, test_number DESC LIMIT 1) AND cs.ALEKS_class_code = '{}' ORDER BY date_ended DESC, time_ended DESC".format(cohort))
        return cursor.fetchall()


    #=============================================
    # accepts: str ALEKS_class_code as cohort
    # returns: list of scores
    def get_all_scores(self,cohort):
        cursor = self.get_cursor()
        if cohort == "all":
            cursor.execute("SELECT * FROM cmpt_scores cs JOIN eligibility_list el using (mthscID) ORDER BY approval, date_ended DESC, time_ended DESC")
        else:
            cursor.execute("SELECT * FROM cmpt_scores cs JOIN eligibility_list el using (mthscID) WHERE ALEKS_class_code = '{}' ORDER BY approval, date_ended DESC, time_ended DESC".format(cohort))
        return cursor.fetchall()


    #=============================================
    # accepts: str ALEKS_class_code as cohort
    # returns: list of scores
    # added by KDH 4-23-20
    def get_pending_scores(self):
        cursor = self.get_cursor()
        cursor.execute("SELECT * FROM cmpt_scores cs JOIN eligibility_list el using (mthscID) WHERE approval = '' ORDER BY date_ended DESC, time_ended DESC")
        return cursor.fetchall()


    #=============================================
    # accepts: str ALEKS_class_code as cohort
    # returns: list of scores
    def get_all_engr_scores(self,cohort):
        cursor = self.get_cursor()
        if cohort == "all":
            cursor.execute("SELECT * FROM cmpt_scores WHERE ALEKS_class_code IN (SELECT ALEKS_class_code FROM cohorts WHERE student_type = 'ENGR') ORDER BY date_ended DESC, time_ended DESC")
        else:
            cursor.execute("SELECT * FROM cmpt_scores WHERE ALEKS_class_code = '{}' ORDER BY date_ended DESC, time_ended DESC".format(cohort))
        return cursor.fetchall()


    #=============================================
    # accepts: nothing
    # returns: list of XIDs and mthscIDs for all students who have a score
    def get_mthscIDs(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT DISTINCT xid, mthscID, username FROM cmpt_scores")
        return cursor.fetchall()


    #=============================================
    # accepts: nothing
    # returns: list of scores for orientation
    def get_orientation_scores(self):
        cursor = self.get_cursor()
        # we don't have the scores expire now
        #cursor.execute("SELECT xid, MAX(score) AS score, date_ended FROM cmpt_scores WHERE xid IS NOT NULL AND date_ended >= DATE_SUB(NOW(), INTERVAL 1 YEAR) GROUP BY xid")
        #cursor.execute("SELECT xid, MAX(score) AS score, date_ended FROM cmpt_scores WHERE xid IS NOT NULL GROUP BY xid")

        # NB: (6-23-2014) we only pull scores from the past year
        # KH: (4-6-16) changed to past 4 years
        cursor.execute("SELECT temp.xid, temp.score, date_ended FROM (SELECT xid, MAX(score) AS score, date_ended FROM cmpt_scores WHERE xid IS NOT NULL AND approval = 'accepted' GROUP BY xid) AS temp WHERE temp.date_ended >= DATE_SUB(NOW(), INTERVAL 4 YEAR)")
        return cursor.fetchall()


    #=============================================
    # accepts: nothing
    # returns: list of substitute scores from precal
    def get_substitute_scores(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT xid, score FROM substitute_scores")
        return cursor.fetchall()


    #=============================================
    # accepts: str mthscID
    # returns: list of cmpt scores
    def get_scores(self,mthscID):
        cursor = self.get_cursor()
        cursor.execute("SELECT * FROM cmpt_scores WHERE mthscID = '{}' ORDER BY ALEKS_class_code, test_number ASC".format(mthscID))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: str mthscID
    # returns: (int cmpt score, str ALEKS_class_code, int test number, date test date) otherwise (None, None, None, None)
    def get_score(self,mthscID):
        cursor = self.get_cursor()
        cursor.execute("SELECT * FROM cmpt_scores WHERE mthscID = '{}' ORDER BY score DESC, test_number DESC LIMIT 1".format(mthscID))

        if cursor.rowcount == 1:
            data = cursor.fetchone()
            return (data.get('score'), data.get('ALEKS_class_code'), data.get('test_number'), data.get('test_date'))
        else:
            return (None, None, None, None)

    #=============================================
    # accepts: str mthscID
    # returns: (int cmpt score, str ALEKS_class_code, int test number, date test date) otherwise (None, None, None, None)
    def get_best_accepted_score(self,mthscID):
        cursor = self.get_cursor()
        cursor.execute("SELECT * FROM cmpt_scores WHERE mthscID = '{}' AND approval = 'accepted' ORDER BY score DESC, test_number DESC LIMIT 1".format(mthscID))

        if cursor.rowcount == 1:
            data = cursor.fetchone()
            return (data.get('score'), data.get('ALEKS_class_code'), data.get('test_number'), data.get('test_date'))
        else:
            return (None, None, None, None)


    #=============================================
    # accepts: nothing
    # returns: list of student types
    def get_student_types(self):
        cursor = self.get_cursor()
        cursor.execute("""SELECT * FROM cohorts ORDER BY year, semester, student_type""")
        return cursor.fetchall()


    #=============================================
    # accepts: nothing
    # returns: list of current student types
    def get_current_student_types(self):
        cursor = self.get_cursor()
        cursor.execute("""SELECT * FROM student_types ORDER BY student_type""")
        return cursor.fetchall()


    #=============================================
    # accepts: str username
    # returns: student info for this username
    def get_student_info_username(self,username):
        cursor = self.get_cursor()
        cursor.execute("SELECT cur_student_type, cur_semester, cur_year FROM eligibility_list WHERE username = '{}'".format(username))

        if cursor.rowcount > 0:
            return cursor.fetchone()
        else:
            return {}


    #=============================================
    # accepts: str xid
    # returns: mthscID for this xid
    def get_mthscID_from_xid(self,xid):
        cursor = self.get_cursor()
        cursor.execute("SELECT mthscID FROM eligibility_list WHERE xid = '{}'".format(xid))

        if cursor.rowcount > 0:
            return cursor.fetchone().get('mthscID')
        else:
            return ""


    #=============================================
    # accepts: str xid
    # returns: mthscID for this username
    def get_mthscID_from_username(self,username):
        cursor = self.get_cursor()
        cursor.execute("SELECT mthscID FROM eligibility_list WHERE username = '{}'".format(username))

        if cursor.rowcount > 0:
            return cursor.fetchone().get('mthscID')
        else:
            return ""


    #=============================================
    # accepts: str student type
    #          str semester
    #          int year
    # returns: ALEKS class code for this student type, semester, and year
    def get_ALEKS_class_code(self,student_type, semester, year):
        cursor = self.get_cursor()
        cursor.execute("SELECT ALEKS_class_code FROM cohorts WHERE student_type = '{}' AND semester = '{}' AND year = '{}'".format(student_type, semester, year))

        if cursor.rowcount > 0:
            return cursor.fetchone().get('ALEKS_class_code')
        else:
            return ""


    #=============================================
    # accepts: str student_type
    #          str semester
    #          int year
    #          str ALEKS_class_code
    # returns: True is the cohort was created, False otherwise
    def add_cohort(self,student_type, semester, year, ALEKS_class_code):
        cursor = self.get_cursor()
        cursor.execute("INSERT INTO cohorts (student_type, semester, year, ALEKS_class_code) VALUES ('{}', '{}', '{}', '{}') ON DUPLICATE KEY UPDATE year = year".format(student_type, semester, year, ALEKS_class_code))

        return True # we should be checking to see if anything went wrong


    #=============================================
    # accepts: nothing
    # returns: list of cohorts
    def get_all_cohorts(self):
        cursor = self.get_cursor()
        cursor.execute("SELECT * FROM cohorts ORDER BY year DESC, semester, student_type")

        return cursor.fetchall()

    #=============================================
    # accepts: str ALEKS_class_code
    # returns: list of cohorts info
    def get_cohort_info(self,ALEKS_class_code):
        cursor = self.get_cursor()
        cursor.execute("SELECT * FROM cohorts WHERE ALEKS_class_code='{}' ORDER BY year DESC, semester, student_type".format(ALEKS_class_code))

        if cursor.rowcount > 0:
            return cursor.fetchone()
        else:
            return {'ALEKS_class_code': ALEKS_class_code, 'year': '', 'semester': '', 'student_type': ''}


    #=============================================
    # accepts: nothing
    # returns: list of cohorts
    def get_all_class_codes(self):
        cursor = self.get_cursor()
        cursor.execute("SELECT DISTINCT ALEKS_class_code,semester,year FROM cohorts ORDER BY year DESC, semester ASC")

        return cursor.fetchall()


    #=============================================
    # accepts: nothing
    # returns: list of cohorts
    def get_all_engr_class_codes(self):
        cursor = self.get_cursor()
        cursor.execute("SELECT DISTINCT ALEKS_class_code,semester,year FROM cohorts WHERE student_type='ENGR' ORDER BY year DESC, semester ASC")

        return cursor.fetchall()


    #=============================================
    # accepts: str xid
    # returns: list of cohorts
    def get_all_cohorts_for_student(self,xid):
        cursor = self.get_cursor()
        cursor.execute("SELECT xid, c.student_type, c.semester, c.year, ALEKS_class_code FROM student_to_student_types_link AS sstl LEFT JOIN cohorts AS c ON c.student_type = sstl.student_type AND c.semester = sstl.semester AND c.year = sstl.year WHERE xid = '{}' ORDER BY year, semester, student_type, ALEKS_class_code".format(xid))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: str semester
    #          int year
    # returns: ALEKS class codes for the last 3 semesters (including this one)
    def get_recent_ALEKS_class_codes(self,semester, year):
        terms = [(semester, year)]
        year = int(year)

        if semester == "fall":
            terms.append(("spring", year))
            terms.append(("fall", year - 1))
        else:
            terms.append(("fall", year - 1))
            terms.append(("spring", year - 1))

        cursor = self.get_cursor()
        cursor.execute("SELECT DISTINCT ALEKS_class_code FROM cohorts WHERE (semester = '{}' AND year = '{}') OR (semester = '{}' AND year = '{}') OR (semester = '{}' AND year = '{}')".format(terms[0][0], terms[0][1], terms[1][0], terms[1][1], terms[2][0], terms[2][1]))

        if cursor.rowcount > 0:
            return [code.get('ALEKS_class_code') for code in cursor.fetchall()]
        else:
            return []


    #=============================================
    # accepts: nothing
    # returns: True if the scores were downloaded, False otherwise
    #
    def download_ALEKS_scores_test(self):
        start_date = "2013-01-01"
        # get todays date
        end_date = datetime.date.today().strftime("%Y-%b-%d")

        class_code = "CEJNA-UGDNE"

        self.download_ALEKS_scores("best", class_code, start_date, end_date)

        return True # we should be checking to see if anything went wrong


    #=============================================
    # accepts: str score type
    #          str ALEKS class code
    #          str start date
    #          str end date
    # returns: True if the scores were downloaded, False otherwise
    #
    def download_ALEKS_scores(self,score_type, class_code, start_date, end_date, cwd=""):
        # first pull the data down from ALEKS
        # proxy = xmlrpclib.ServerProxy("https://api.aleks.com/xmlrpc")
        proxy = xmlrpc.client.ServerProxy("https://api.aleks.com/xmlrpc")

        params = {"username": "user", "password": "password", "class_code": class_code, "type": score_type, "from_completion_date": start_date, "to_completion_date": end_date, "style": "slice", "page_num": 1}

        valid = True
        records = []

        while valid:
            data_str = proxy.getPlacementReport(params)

            data_str = data_str.lstrip()
            data_str = data_str.rstrip()

            if data_str == "No records found":
                valid = False
            else:
                csv_file = io.StringIO(data_str)
                syspath=os.path.dirname(__file__)
                # files in reports/ have permission 660
                # apache in group web_mthsc
                filename=os.path.join(syspath,"app/static/reports/aleks.csv")
                f = open(filename,"w")
                f.write(data_str)
                f.close()
                # this will use the first line as the headers
                data = csv.DictReader(csv_file)
                records += data
                params["page_num"] += 1


        # store the scores in the database
        cursor = self.get_cursor()
        for record in records:
            test_date = datetime.datetime.strptime(record.get('End Date'), "%m/%d/%Y").date()
            date_started = datetime.datetime.strptime(record.get('Start Date'), "%m/%d/%Y").date()
            time_started = datetime.datetime.strptime(record.get('Start Time'), "%I:%M %p")

            date_ended = datetime.datetime.strptime(record.get('End Date'), "%m/%d/%Y").date()
            time_ended = datetime.datetime.strptime(record.get('End Time'), "%I:%M %p")

            score = record.get('Placement Results %').rstrip("%")

            last,first = record.get('Name').split(',')

            # store the scores
            cursor.execute("INSERT INTO cmpt_scores (mthscID, last, score, ALEKS_class_code, test_number, test_date, date_started, time_started, date_ended, time_ended, time_in_test, approval) VALUES ('{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '') ON DUPLICATE KEY UPDATE last = '{}', score = '{}', ALEKS_class_code = '{}', test_number = '{}', test_date = '{}', date_started = '{}', time_started = '{}', date_ended = '{}', time_ended = '{}', time_in_test = '{}'".format(record.get('Student Id'), last, score, class_code, record.get('Placement Assessment Number'), test_date, date_started, time_started, date_ended, time_ended, record.get('Time in Placement (in hours)'), last, score, class_code, record.get('Placement Assessment Number'), test_date, date_started, time_started, date_ended, time_ended, record.get('Time in Placement (in hours)')))

            # update the usernames if we had new scores
            cursor.execute("UPDATE cmpt_scores AS cs INNER JOIN eligibility_list AS el ON el.mthscID = cs.mthscID SET cs.username = el.username, cs.xid = el.xid")

            return True # we should be checking to see if anything went wrong


    #=============================================
    # accepts: nothing
    #
    # returns: success message after performing manual download of ALEKS scores
    #
    def admin_download_ALEKS_scores(self):
        cur_semester = self.get_current_semester()
        cur_year = self.get_current_year()

        class_codes = self.get_recent_ALEKS_class_codes(cur_semester, cur_year)

        for class_code in class_codes:
            self.download_all_ALEKS_scores(class_code)

        msg = "scores downloaded"
        return msg


    #=============================================
    # accepts: str ALEKS class code
    # returns: True if the scores were downloaded, False otherwise
    #
    def download_recent_ALEKS_scores(self,class_code):
        # get start date
        start_date = datetime.date.today() + relativedelta(days=-2)
        start_date = start_date.strftime("%Y-%m-%d")

        # get todays date
        end_date = datetime.date.today().strftime("%Y-%m-%d")

        self.download_ALEKS_scores("all", class_code, start_date, end_date)

        return True # we should be checking to see if anything went wrong


    #=============================================
    # accepts: str ALEKS class code
    # returns: True if the scores were downloaded, False otherwise
    #
    def download_all_ALEKS_scores(self,class_code):
        # get start date
        start_date = datetime.date.today() + relativedelta(years=-1)
        start_date = start_date.strftime("%Y-%m-%d")

        # get todays date
        end_date = datetime.date.today().strftime("%Y-%m-%d")

        self.download_ALEKS_scores("all", class_code, start_date, end_date)

        return True # we should be checking to see if anything went wrong


    #=============================================
    # accepts: str term (format: 201308)
    # returns: True if the students were downloaded, False otherwise
    #
    def download_admissions_list_from_daps(self,term):
        banner_cursor=self.get_banner_cursor()

        extra_term = ""

        if term[4:] == "08":
            extra_term = f"OR TERM_CODE_ENTRY = '{term[0:4]}05'"

        banner_cursor.execute("""SELECT XID, USERNAME_STUDENT FROM SIS_MTHSC_ACCEPTED_STUDENTS WHERE TERM_CODE_ENTRY = {} {}""".format(term, extra_term))

        students = banner_cursor.fetchall()

        year = int(term[0:4])
        month = int(term[4:])
        eligible_until = f"""{int(year+1)}-{str(month+1).zfill(2)}-01 """

        semester = "fall"

        if month == 1:
            semester = "spring"

        for student in students:
            xid = student[0].strip()
            username = student[1].strip().lower()

            if len(username) > 0:
                # now we insert the data into the database
                self.auto_add_to_eligibility_list(username, xid, "new", semester, year, eligible_until)

        return True # we should be checking to see if anything went wrong


    #=============================================
    # accepts: str mthscID
    # returns: True if the student is eligible, False otherwise
    #
    def is_eligible(self,mthscID):
        cursor = self.get_cursor()
        cursor.execute("SELECT COUNT(username) AS valid FROM eligibility_list WHERE mthscID = '{}' AND NOW() <= eligible_until".format(mthscID))

        valid = cursor.fetchone().get('valid')
        if valid == 1:
            return True
        else:
            return False


    #=============================================
    # accepts: str mthscID
    # returns: dict info about the student
    #
    def get_student_info(self,mthscID):
        cursor = self.get_cursor()
        cursor.execute("SELECT * FROM eligibility_list LEFT JOIN Banner_info.student_info using (xid) WHERE mthscID = '{}'".format(mthscID))

        if cursor.rowcount > 0:
            info = cursor.fetchone()

            info["ALEKS_class_code"] = self.get_ALEKS_class_code(info.get('cur_student_type'), info.get('cur_semester'), info.get('cur_year'))

            return info
        else:
            return {}


    #=============================================
    # accepts: str student_type
    #          str semester
    #          int year
    # returns: list of students
    #
    def get_eligibility_list(self,student_type, semester, year):
        cursor = self.get_cursor()
        if student_type == "all":
            cursor.execute("SELECT * FROM eligibility_list LEFT JOIN Banner_info.student_info using (xid) WHERE cur_semester = '{}' AND cur_year = '{}' ORDER BY datetime_added".format(semester, year))
        else:
            cursor.execute("SELECT * FROM eligibility_list LEFT JOIN Banner_info.student_info using (xid) WHERE cur_student_type = '{}' AND cur_semester = '{}' AND cur_year = '{}' ORDER BY datetime_added".format(student_type, semester, year))
        result = cursor.fetchall()

        if cursor.rowcount > 0:
            return result
        else:
            return []


    #=============================================
    # accepts: str mthscID
    #          str semester
    #          int year
    # returns: True if the student was removed, False otherwise
    #
    def remove_from_eligibility_list(self,mthscID, student_type, semester, year):
        cursor = self.get_cursor()

        if mthscID == "*":
            if student_type == "all":
                cursor.execute("DELETE FROM eligibility_list WHERE cur_semester = '{}' AND cur_year = '{}'".format(semester, year))
            else:
                cursor.execute("DELETE FROM eligibility_list WHERE cur_student_type = '{}' AND cur_semester = '{}' AND cur_year = '{}'".format(student_type, semester, year))
        else:
            # a student should only be listed as eligible for one type/semester/year combination
            cursor.execute("DELETE FROM eligibility_list WHERE mthscID = '{}'".format(mthscID))

        return True # we really should be checking to see if this succeeded


    #=============================================
    # This function is called when a student is manually added to a cohort.
    # accepts:  str username
    #           str xid
    #           str student_type
    #           str semester
    #           int year
    #           date eligible until
    # returns: True if the student was added, False otherwise
    #
    def add_to_eligibility_list(self,username, xid, student_type, semester, year, eligible_until):
        cursor = self.get_cursor()
        # Because this is a manual add, we DO want to update type/semester/year
        cursor.execute("INSERT INTO eligibility_list (username, mthscID, xid, cur_student_type, cur_semester, cur_year, eligible_until, datetime_added) VALUES ('{}', MD5('{}'), '{}', '{}', '{}', '{}', '{}', NOW()) ON DUPLICATE KEY UPDATE xid = '{}', cur_student_type = '{}', cur_semester = '{}', cur_year = '{}', eligible_until = '{}'".format(username.lower(), username.lower() + self.hash_append, xid, student_type, semester, year, eligible_until, xid, student_type, semester, year, eligible_until))

        cursor.execute("INSERT INTO student_to_student_types_link (xid, student_type, semester, year) VALUES ('{}', '{}', '{}', '{}') ON DUPLICATE KEY UPDATE xid = xid".format(xid, student_type, semester, year))

        return True # we really should be checking to see if this succeeded


    #=============================================
    # This function gets called when the eligibility list is updated from admissions data every morning. We do not want student type overwritten
    # accepts:  str username
    #           str xid
    #           str student_type
    #           str semester
    #           int year
    #           date eligible until
    # returns: True if the student was added, False otherwise
    #
    def auto_add_to_eligibility_list(self,username, xid, student_type, semester, year, eligible_until):
        cursor = self.get_cursor()
        # if a student is on the list from a past semester we don't change their type/semester/year combination
        cursor.execute("INSERT INTO eligibility_list (username, mthscID, xid, cur_student_type, cur_semester, cur_year, eligible_until, datetime_added) VALUES ('{}', MD5('{}'), '{}', '{}', '{}', '{}', '{}', NOW()) ON DUPLICATE KEY UPDATE xid = '{}'".format(username.lower(), username.lower() + self.hash_append, xid, student_type, semester, year, eligible_until, xid))

        cursor.execute("INSERT INTO student_to_student_types_link (xid, student_type, semester, year) VALUES ('{}', '{}', '{}', '{}') ON DUPLICATE KEY UPDATE xid = xid".format(xid, student_type, semester, year))

        return True # we really should be checking to see if this succeeded


    #=============================================
    # accepts:  nothing
    # returns: the html for the menu
    #
    def get_admin_menu(self):
        #"admin_view_students" is no longer used
        html = """
<hr>
<div class="menu">
    <ul>
        <li><a href="admin_settings">Settings</a></li>
        <li><a href="admin_manage_cohorts">Cohorts</a></li>
        <li><a href="admin_admissions_list">Admissions List</a></li>
        <li><a href="admin_download_scores">ALEKS Scores</a></li>
    </ul>
</div>
<hr style="clear: left;">
<div class="menu">
    <ul>
        <!--<li><a href="admin_view_students">View Students</a></li>-->
        <li><a href="admin_add_students">Add Students</a></li>
        <li><a href="admin_lookup_scores">Lookup Score</a></li>
        <li><a href="admin_view_best_scores">Best Scores</a></li>
        <li><a href="admin_view_all_scores">All Scores</a></li>
    </ul>
</div>
<hr style="clear: left;">
"""

        return html


    #=============================================
    # accepts:  str title
    # returns: the html for the header
    #
    def get_header(self,title):
        html = f"""<div class="header"><h1>Clemson Mathematics Placement Test (CMPT)</h1></div>
<div class="page_title">{title}</div>"""

        return html


    #=============================================
    # accepts:  nothing
    # returns: the html for the menu
    #
    # replaced line <li><a href="course_credit.py">Course Credit</a></li> with  <li><a href="course_credit.py">AP / Transfer Credit</a></li>
    #
    def get_menu(self):
        html = """<ul class="menu">
    <li><a href="information">Info</a></li>
    <li><a href="course_credit">AP/Transfer Credit</a></li>
    <li><a href="access_test">Go To ALEKS</a></li>
    <li><a href="view_cmpt_score">View/Interpret Score</a></li>
    <li><a href="faq">FAQ</a></li>
    <li><a href="tech_support">Tech Support</a></li>
</ul>"""

        return html


    #=============================================
    # accepts:  nothing
    # returns: the html for the footer
    #
    def get_footer(self):
        #html = """<div class="footer">
        #<span style="float: left;">Questions: contact Jennifer Van Dyken (<a href="mailto:jdyken@clemson.edu">jdyken@clemson.edu</a>)</span>
        #<span style="float: right;"><a href="/dept_forms/website_error" target="_blank">report a problem</a></span>
        #</div>"""


        html = """<div class="footer">
<span style="float: left;"></span>
</div>
"""

        return html


    #=============================================
    # accepts: str mthscID
    # returns: the html for score info table
    #
    def get_html_for_score_info_table(self,mthscID):
        (best_score, best_cohort, best_test, test_date) = self.get_best_accepted_score(mthscID)
        score_list = self.get_scores(mthscID)

        if len(score_list) == 0:
            score_table = """<div style="color: #F66733; font-size: 24px; text-align: center;">No score found.</div>"""
        else:
            score_table = """
<table class="cmpt_scores">
    <tr>
        <td>CMPT attempt number</td>
        <td>CMPT score</td>
        <td>Date</td>
        <td>Result</td>
        <!--<td>Expiration</td>-->
    </tr>
"""
            for score in score_list:
                cmpt_attempt = score.get('test_number')
                cmpt_score = score.get('score')
                approval = score.get('approval')

                style_class = ""

                if cmpt_attempt == best_test:
                    style_class = """ class="best_test" """

                #format date as "Month day, year"
                #day has no padding zeros: %-d
                expiration_date = score.get('date_ended') + relativedelta(years=10)
                cmpt_expiration_date = expiration_date.strftime("%B %-d, %Y")

                test_date = score.get('date_ended')
                cmpt_test_date = test_date.strftime("%B %-d, %Y")

                score_table += f"""
    <tr{style_class}>
        <td>{cmpt_attempt}</td>
        <td>{cmpt_score}</td>
        <td>{test_date}</td>
        <td>{approval}</td>
        <!--<td>{cmpt_expiration_date}</td>-->
    </tr>
"""

            score_table += """
</table>"""

        score_info = f"""
<h2>Your CMPT Score</h2>
<div>The following table lists your scores on the CMPT. The highest score you have gotten on the placement test so far is highlighted in <span style="font-weight: bold; color: #F66733;">orange</span>. The scores are updated periodically, but if you took the test recently you may need to click the Check for New Score button to manually retrieve your score.</div>
{score_table}
<div style="text-align: center;">
    <form action="view_cmpt_score" method="POST">
        <input type="submit" value="Check for New Score">
        <input type="hidden" name="new_score_check" value="True">
    </form>
</div>
"""

        return score_info


    #=============================================
    # accepts: list student_data
    #          str delim
    # returns: html used after student data uploaded
    #
    def get_html_for_add_student_upload(self,student_data):
        content = ""
        data = student_data.replace("\r", "").split("\n")

        cur_semester = self.get_current_semester()
        cur_year = self.get_current_year()

        errorMsg=""
        for i in range(0,len(data)):
            cur_student = data[i].replace('\t',',').split(',')

            if len(cur_student)==4:
                # cuid
                username = cur_student[0].strip()
                XID = cur_student[1].strip()
                student_type = cur_student[2].strip()
                pieces = cur_student[3].strip().replace("/","-").split("-")
                eligible_until = f"{pieces[2]}-{pieces[0]}-{pieces[1]}"

                # now we insert the data into the database
                self.add_to_eligibility_list(username, XID, student_type, cur_semester, cur_year, eligible_until)
            elif len(data[i])>0:
                #this handles lines of incorrect length
                errorMsg+=f"<tr><td>{data[i]}</td></tr>"
        if len(errorMsg)>0:
            content = f"""
<div style=\"text-align: center;\">The following entries did not upload successfully:
<table>{errorMsg}</table></div>"""
        else:
            content = "<div style=\"text-align: center;\">Data has been successfully uploaded.</div>"
        return content

    #=============================================
    # accepts: nothing
    # returns: html used after student data uploaded
    #
    def get_html_for_blank_add_student_form(self):
        content = ""
        student_types = self.get_current_student_types()

        student_types_display = [temp["student_type"] for temp in student_types]
        student_types = [temp["student_type"] for temp in student_types]

        student_type_dropdown = self.get_dropdown_html(student_types, student_types_display, "", "student_type")

        content = f"""
<div class="add_header" onclick="show_section('single_add')">Add a single student</div>
<div class="add_section" id="single_add">
    <form action="admin_add_students" method="POST" enctype="multipart/form-data">
        <table style="margin: 0px auto;">
            <tr>
                <td>username</td>
                <td>XID</td>
                <td>student type</td>
                <td style="text-align: center;">eligible until<br>(M-D-YYYY)</td>
            </tr>
            <tr>
                <td><input type="text" name="username"></td>
                <td><input type="text" name="xid"></td>
                <td>{student_type_dropdown}</td>
                <td><input type="text" name="month" size="2"> - <input type="text" name="day" size="2"> - <input type="text" name="year" size="4"></td>
            </tr>
        </table>
        <div class="upload_btn_section">
            <input type="submit" value="Upload">
            <input type="hidden" name="form_name" value="single_add">
        </div>
    </form>
</div>

<div class="add_header" onclick="show_section('upload_add')">Upload students from file</div>
<div class="add_section" id="upload_add">
    <form action="admin_add_students" method="POST" enctype="multipart/form-data">
        <div>Choose the data file from your computer <input type="file" name="student_data_file"></div>
        <div class="upload_btn_section">
            <input type="submit" value="Upload">
            <input type="hidden" name="form_name" value="upload_add">
        </div>
    </form>
</div>

<div class="add_header" onclick="show_section('copy_add')">Copy students from file</div>
<div class="add_section" id="copy_add">
    <form action="admin_add_students" method="POST" enctype="multipart/form-data">
        <div>Copy and paste the data into the textarea below.</div>
        <!--<div>Format: <input type="radio" name="format" id="tab" value="tab"><label for="tab"> Tab</label> <input type="radio" name="format" id="csv" value="csv" checked><label for="csv"> CSV</label></div>-->
        <textarea name="student_data" style="width: 900px; height: 400px; margin: 0px auto;" wrap="off"></textarea>
        <div class="upload_btn_section">
            <input type="submit" value="Upload">
            <input type="hidden" name="form_name" value="copy_add">
        </div>
    </form>
</div>"""
        return content

    #=============================================
    # accepts: nothing
    # returns: html used in 'blank' admin_settings.py
    #
    def get_html_for_admin_settings(self):
        content=f"""
<div>
<p>The current semester and year are used for when students are automatically pulled from Banner or added manually. Students can always view their CMPT scores regardless of the current semester.</p>
<form action="admin_settings" method="POST">
<table>
    <tr>
        <td>Current semester:</td>
        <td>{self.get_dropdown_html(["fall", "spring"],["Fall", "Spring"],self.get_current_semester(), "current_semester")}</td>
    </tr>
    <tr>
        <td>Current year:</td>
        <td><input type="text" name="current_year" value="{self.get_current_year()}" style="width: 50px;"></td>
    </tr>
</table>
<div>
<input type="submit" value="Save">
<input type="hidden" name="form_name" value="save_settings">
</div>
</form>
</div>"""

        return content

    #=============================================
    # accepts: nothing
    # returns: html used in 'blank' admin_manage_cohorts.py
    #
    def get_html_for_admin_manage_cohorts(self,info):
        student_types = self.get_current_student_types()

        student_types_display = [temp.get('student_type') for temp in student_types]
        student_types = [temp.get('student_type') for temp in student_types]

        student_type_dropdown = self.get_dropdown_html(student_types, student_types_display, "", "student_type")

        # display the current cohorts
        cohorts = self.get_all_cohorts()

        cohorts_html = ""

        if len(cohorts) > 0:
            cohorts_html = """
    <table class="table_view" style="margin: 0px auto;">
        <tr>
            <td>Term</td>
            <td>Student Type</td>
            <td>ALEKS Class Code</td>
        </tr>
"""
            for cohort in cohorts:
                cohorts_html += f"""
        <tr>
            <td>{cohort.get('semester').capitalize()} {cohort.get('year')}</td>
            <td>{cohort.get('student_type')}</td>
            <td>{cohort.get('ALEKS_class_code')}</td>
        </tr>
"""

            cohorts_html += "</table>"

        content = f"""
<div style="font-weight: bold; font-size: 24px;">Add a new cohort</div>
<div>Fill out the information in the boxes below and click the Add Cohort button to add a new cohort.</div>
<div>
    <form action="admin_manage_cohorts" method="POST">
        <table style="margin: 0px auto;">
            <tr>
                <td>Student type</td>
                <td>Semester</td>
                <td>Year</td>
                <td>ALEKS class code</td>
            </tr>
            <tr>
                <td>{student_type_dropdown}</td>
                <td>{self.get_dropdown_html(["fall", "spring"], ["Fall", "Spring"], self.get_current_semester(), "semester")}</td>
                <td><input type="text" name="year" style="width: 50px;"></td>
                <td><input type="text" name="ALEKS_class_code"></td>
            </tr>
        </table>
        <div style="text-align: center; margin-bottom: 20px;">
            <input type="submit" value="Add Cohort">
            <input type="hidden" name="form_name" value="add_cohort">
{info}
        </div>
    </form>
</div>
<hr>
<div style="font-weight: bold; font-size: 24px;">Existing Cohorts</div>
{cohorts_html}"""

        return content

    #=============================================
    # accepts: nothing
    # returns: html for admin_view_students
    #
    def get_html_for_admin_view_students(self,student_type,cur_semester,cur_year):
        eligibility_list = self.get_eligibility_list(student_type, cur_semester, cur_year)

        content = f"""
<table class="table_view" cellspacing="0" align="center">
    <tr>
        <th scope="col" class="display"  style="text-align: center; font-weight: bold;"><!--<button type="button" onclick="javascript:warning_all('{student_type}');">Delete All</button>--></th>
        <th scope="col">Last Name</th>
        <th scope="col">First Name</th>
        <th scope="col">Username</th>
        <th scope="col">XID</th>
        <th scope="col">mthscID</th>
        <th scope="col">Student Type</th>
        <th scope="col">Eligible Until</th>
        <th scope="col">Added</th>
    </tr>
"""

        for student in eligibility_list:
            eligible_until = student.get('eligible_until')
            if eligible_until is None:
                eligible_until = ""
            else:
                eligible_until = eligible_until.strftime("%m-%d-%Y")
            last_name = student.get('last_name')
            if last_name == None:
                last_name = ""
            first_name = student.get('first_name',"")
            if first_name == None:
                first_name = ""

            content += f"""
    <tr>
        <td><button type="button" onclick="javascript:warning('{student.get('mthscID')}', '{student_type}');" >delete</button></td>
        <td>{last_name}</td>
        <td>{first_name}</td>
        <td>{student.get('username')}</td>
        <td>{student.get('xid')}</td>
        <td>{student.get('mthscID')}</td>
        <td>{student.get('cur_student_type')}</td>
        <td>{eligible_until}</td>
        <td>{student.get('datetime_added').strftime("%m-%d-%Y")}</td>
    </tr>
"""

        content += "</table>"

        return content

    #=============================================
    # accepts: str xid
    #          str username
    # returns: html for admin_lookup_scores
    #
    def get_html_for_admin_lookup_scores(self,xid,username):
        # page accepts one of xid and username
        # if neither specified, defaults to error message (should not happen)
        if len(xid)>0:
            errorMsgIdentifier=xid
            mthscID = self.get_mthscID_from_xid(xid)
        elif len(username)>0:
            errorMsgIdentifier=username
            mthscID = self.get_mthscID_from_username(username)
        else:
            #This should not happen
            return "No xid or username submitted"

        info = self.get_student_info(mthscID)
        if len(info) > 0:
            #grab both username and xid since only one specified
            username         = info.get('username')
            xid              = info.get('xid')
            all_cohorts_html = ""

            cohort_list = self.get_all_cohorts_for_student(xid)

            if len(cohort_list) > 0:
                all_cohorts_html = """
                <table class="table_view" cellspacing="0">
                    <tr>
                        <td>Student Type</td>
                        <td>Semester</td>
                        <td>Year</td>
                        <td>ALEKS class code</td>
                    </tr>"""

                for cohort in cohort_list:
                    all_cohorts_html += f"""
                    <tr>
                        <td>{cohort.get('student_type')}</td>
                        <td>{cohort.get('semester')}</td>
                        <td>{cohort.get('year')}</td>
                        <td>{cohort.get('ALEKS_class_code')}</td>
                    </tr>
"""

                all_cohorts_html += "</table>"

            scores_html = f"""
<div style="margin-bottom: 20px;">
    <div style="font-weight: bold; font-size: 20px;">Student Info for {info.get('last_name')}, {info.get('first_name')}: {xid}</div>
    <table style="margin-left: 20px;">
        <tr>
            <td>mthscID:</td>
            <td>{mthscID}</td>
        </tr>
        <tr>
            <td>Eligible Until:</td>
            <td>{info.get('eligible_until').strftime("%m-%d-%Y")}</td>
        </tr>
        <tr>
            <td>Current Student Type:</td>
            <td>{info.get('cur_student_type')}</td>
        </tr>
        <tr>
            <td>CMPT Term:</td>
            <td>{info.get('cur_semester').capitalize()} {info.get('cur_year')}</td>
        </tr>
        <tr>
            <td>Current Cohort:</td>
            <td>{info.get('ALEKS_class_code')}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">All Cohorts:</td>
            <td>{all_cohorts_html}</td>
        </tr>
    </table>
</div>
<div style="font-weight: bold; font-size: 20px;">CMPT Scores</div>
<table class="table_view" cellspacing="0" align="center">
    <tr>
        <td>XID</td>
        <td>Username</td>
        <td>MthscID</td>
        <td>Score</td>
        <td>Cohort</td>
        <td>Attempt</td>
        <td>Started</td>
        <td>Ended</td>
        <td style="text-align: center;">Time in Test<br>(in hours)</td>
        <td>Approval</td>
    </tr>
"""

            (best_score, best_cohort, best_test, test_date) = self.get_best_accepted_score(mthscID)
            scores = self.get_scores(mthscID)

            for score in scores:
                start_time = self.format_time(score.get('time_started'))
                end_time = self.format_time(score.get('time_ended'))

                style_class = ""

                if score.get('test_number') == best_test and score.get('ALEKS_class_code') == best_cohort:
                    style_class = """ class="best_test" """

                scores_html += f"""
    <tr{style_class}>
        <td>{score.get('xid')}</td>
        <td>{score.get('username')}</td>
        <td>{score.get('mthscID')}</td>
        <td>{score.get('score')}</td>
        <td>{score.get('ALEKS_class_code')}</td>
        <td>{score.get('test_number')}</td>
        <td>{score.get('date_started').strftime("%m-%d-%Y")} {start_time}</td>
        <td>{score.get('date_ended').strftime("%m-%d-%Y")} {end_time}</td>
        <td style="text-align: center;">{score.get('time_in_test')}</td>
        <td>{score.get('approval')}</td>
    </tr>
"""

            scores_html += """
</table>"""

            student_types = self.get_current_student_types()

            student_types_display = [temp.get('student_type') for temp in student_types]
            student_types = [temp.get('student_type') for temp in student_types]
            student_type_dropdown = self.get_dropdown_html(student_types, student_types_display, "", "student_type")

            scores_html += f"""<div id="add_button"><a href="javascript:toggleAddSection()">Click here to add this student to the eligibility list</a></div>
<div class="add_section" id="single_add" style="display:none;">
    <form action="admin_add_students" method="POST" enctype="multipart/form-data">
        <table style="margin: 0px auto;">
            <tr>
                <td>username</td>
                <td>XID</td>
                <td>student type</td>
                <td style="text-align: center;">eligible until<br>(M-D-YYYY)</td>
            </tr>
            <tr>
                <td><input type="text" name="username" value="{username}"></td>
                <td><input type="text" name="xid" value="{xid}"></td>
                <td>{student_type_dropdown}</td>
                <td><input type="text" name="month" size="2"> - <input type="text" name="day" size="2"> - <input type="text" name="year" size="4"></td>
            </tr>
        </table>
        <div class="upload_btn_section" style="text-align:center;">
            <input type="hidden" name="form_name" value="single_add">
            <input type="submit" value="Add Student to Eligibility List">
        </div>
    </form>
</div>"""

        else:
            scores_html = f"Student ({errorMsgIdentifier}) not found in the eligibility list."
        return scores_html, xid, username

    #=============================================
    # accepts: str access_type
    #          str score_type
    #          str cohort_selection
    # returns: cohort html for admin_view_scores
    #
    def get_html_for_cohort_html(self,access_type,score_type,cohort_selection):
        cohorts_html = ""

        #access type is limited to 'admin' or 'engr'
        if access_type=='admin':
            all_cohorts = self.get_all_class_codes()
        else:
            all_cohorts = self.get_all_engr_class_codes()

        if len(all_cohorts) > 0:
            cohorts_html = """
    <center><h2>Select a cohort:</h2><form action="" method="POST">
    <select name="cohort">
"""
            for cohort in all_cohorts:
                cohort_ALEKS_class_code=cohort.get('ALEKS_class_code')
                cohorts_html += f"""
        <option value={cohort_ALEKS_class_code} """

                if cohort_ALEKS_class_code == cohort_selection:
                    cohorts_html += """selected="selected" """

                cohorts_html += f""">{cohort.get('semester').capitalize()} {cohort.get('year')} {cohort_ALEKS_class_code}</option>
"""

            cohorts_html += """
    <option value="all">All</option>
    </select>
"""

            if score_type == "best":
                cohorts_html += """
        <input type="hidden" name="type" value="best">
    """

            cohorts_html += """
    <input type="submit" value="View">
    </form>
    <br></center>"""

            if cohort_selection != "none":
                #access type is limited to 'admin' or 'engr'
                # cohorts_html += f"""<center><a href="admin_excel_download?type={score_type}&cohort={cohort_selection}">Download Excel File</a></center>
                cohorts_html += f"""
        <center>
          <form action="{access_type}_excel_download" method="post" id="excel_download">
            <input type="hidden" name="score_type" value="{score_type}">
            <input type="hidden" name="cohort" value="{cohort_selection}">
          </form>
        <button type="submit" form="excel_download" value="Submit">Download Excel File</button>
        </center>"""

        elif "engr" == access_type:
            # Print this message to the engr page only if no cohorts available
            cohorts_html = """<p style="text-align:center;">No ENGR cohorts available</p>"""
        return cohorts_html

    #=============================================
    # accepts: str score_type
    #          str cohort_selection
    # returns: scores html for admin_view_scores
    #
    def get_html_for_scores_and_count_html(self,access_type,score_type,cohort_selection):
        scores_html = ""
        count_html=""

        group_colName = 'Cohort'
        #access type is limited to 'admin' or 'engr'
        if "admin" == access_type:
            if score_type == "best":
                scores = self.get_best_scores(cohort_selection)
                group = 'ALEKS_class_code'
            else:
                group_colName = 'Current<br>Student Type'
                group = 'cur_student_type'
                if cohort_selection == "none":
                    scores = self.get_pending_scores()
                    scores_html = """<p style="text-align:center;font-weight:bold;">Pending Scores</center><br>"""
                else:
                    scores = self.get_all_scores(cohort_selection)
        else:
            if score_type == "best":
                scores = self.get_best_engr_scores(cohort_selection)
            else:
                scores = self.get_all_engr_scores(cohort_selection)

        #variable column names in order they appear
        if "admin" == access_type:
            MthscID_colName= "MthscID"
            lastName ="""
        <th scope="col">Last</th>"""
            if "all" == score_type:
                ALEKS_class_code_header="""
        <th scope="col">Cohort</th>"""
            else:
                ALEKS_class_code_header=""
            last_cols=f"""
        <th scope="col">Status</th>
        <th scope="col">Set Approval</th>"""
        else: #engr
            lastName= ""
            MthscID_colName= "Name"
            ALEKS_class_code_header=""
            last_cols= ""

        scores_html += f"""
<table class="table_view" width="1800px" cellspacing="0" align="center">
    <tr>
        <th scope="col">XID</th>
        <th scope="col">Username</th>
        <th scope="col">{MthscID_colName}</th>
{lastName}
        <th scope="col">Score</th>
{ALEKS_class_code_header}
        <th scope="col">{group_colName}</th>
        <th scope="col">Attempt</th>
        <th scope="col">Started</th>
        <th scope="col">Ended</th>
        <th scope="col" style="text-align: center;">Time in Test<br>(in hours)</th>
{last_cols}
    </tr>
"""

        for score in scores:
            start_time = self.format_time(score.get('time_started'))
            end_time = self.format_time(score.get('time_ended'))
            if "admin" == access_type:
                studentIdentifier=f"""<small>{score.get('mthscID')}</small>"""
                lastName=f"""
        <td>{score.get('last')}</td>"""
                if "all" == score_type:
                    ALEKS_class_code_entry=f"""
        <td>{score.get('cur_semester','').capitalize()} {score.get('cur_year')} {score.get('ALEKS_class_code')}</td>"""
                else:
                    ALEKS_class_code_entry=""
                groupInfo=f"""{score.get(group)}"""
                ## tmp vars to build last_cols ##
                score_id = f"""{score.get('username')}-{score.get('ALEKS_class_code')}-{score.get('test_number')}"""
                #anon function to clean up "approval_links" line
                js_link = lambda db_status, link_name: f"""
<a href="javascript:set_approval('{db_status}','{score.get('username')}','{score.get('ALEKS_class_code')}','{score.get('test_number')}')">{link_name}</a>"""
                approval_links = f"""{js_link('accepted','Accept')} | {js_link('denied','Deny')} | {js_link('','Clear')}"""
                last_cols=f"""
        <td id="{score_id}">{score.get('approval')}</td>
        <td>{approval_links}</td>"""
            else:
                studentIdentifier=f"""{self.get_name_from_xid(score.get('xid'))}"""
                lastName=""
                ALEKS_class_code_entry=""
                groupInfo=f"""{score.get('ALEKS_class_code')}"""
                last_cols=""
            scores_html += f"""
    <tr>
        <td>{score.get('xid')}</td>
        <td>{score.get('username')}</td>
        <td>{studentIdentifier}</td>
{lastName}
        <td>{score.get('score')}</td>
{ALEKS_class_code_entry}
        <td>{groupInfo}</td>
        <td>{score.get('test_number')}</td>
        <td>{score.get('date_started').strftime("%m-%d-%Y")} {start_time}</td>
        <td>{score.get('date_ended').strftime("%m-%d-%Y")} {end_time}</td>
        <td style="text-align: center;">{score.get('time_in_test')}</td>
{last_cols}
    </tr>"""

        scores_html += """
</table>"""

        count_html = f"""<center><p>Total: {len(scores)}</p></center>"""

        return scores_html, count_html


    #=============================================
    # accepts: datetimedelta (only handles ones less than a day)
    # returns: str representation of the time
    #
    def format_time(self,time):
        seconds = time.seconds
        (minutes, seconds) = divmod(seconds, 60)
        (hours, minutes) = divmod(minutes, 60)

        am_pm = "AM"
        hour = hours
        if hours == 0:
            hour = "12"
        elif hours > 11:
            am_pm = "PM"
            if hours > 12:
                hour = f"{hours-12}"

        return f"{hour}:{int(minutes):02d} {am_pm}"



    #=============================================
    # accepts: date date test ended
    # returns: str daps expiration term code (format: 1308)
    #
    def get_cmpt_expiration_term(self,date_ended):
        year = (date_ended.year + 1) - 2000

        if date_ended.month < 9:
            month = "08"
        else:
            month = "01"

        return f"{int(year)}{month}"


    #=============================================
    # accepts: str xid
    # returns: strfull name
    #
    def get_name_from_xid(self,xid):
        banner_cursor=self.get_banner_cursor()

        banner_cursor.execute("""select student_name from SIS_MTHSC_STUDENT_INFORMATION where xid = '{}'""".format(xid))
        result=banner_cursor.fetchall()

        if len(result) > 0:
            #result is a list of tuples
            return result[0][0]
        else:
            return ""


    #=============================================
    # accepts: str username
    #          str xid
    #          str full_name
    #          str course_num
    #          str credit_type
    #          str comments
    # returns: nothing
    #
    def save_credit_claim(self, username, xid, full_name, course_num, credit_type, comments):
        cursor = self.get_prereq_cursor()

        # currently only MATH courses can have claimed credit
        sql = """INSERT INTO claimed_credit (username, xid, full_name, course_id, credit_type, comments, time_submitted) VALUES(UPPER(%s), UPPER(%s), %s, (SELECT course_id FROM course.course_list AS cl WHERE cl.prefix = "MATH" AND cl.course_num = %s), %s, %s, NOW())"""

        cursor.execute(sql, (username, xid, full_name, course_num, credit_type, comments))
        # we will just assume that this always works and never fails


##PW 2022-09-22: Replaced with send_email defined in common_lib
#     #=============================================
#     # accepts: str username
#     #          str xid
#     #          str full_name
#     #          str course_num
#     #          str credit_type
#     #          str comments
#     # returns: nothing
#     #
#     def send_email(self,username, xid, full_name, course_num, credit_type, comments):
#         msg_txt = f"""You have successfully submitted an unofficial course credit claim. This information will be used in determining which math classes you are allowed to register for until your course credit claim is processed by the university. It is your responsibility to supply the required course credit information to the university. The details of your unofficial course credit claim are listed below.

#         XID: {xid}
#         course number: MATH {course_num}
#         credit type: {credit_type}
#         details: {comments}
# """

#         msg = MIMEText.MIMEText(msg_txt)

#         msg["Subject"] = "Unofficial Course Credit Claim"
#         msg["From"] = "Jennifer E. Van Dyken <jdyken@clemson.edu>"
#         msg["To"] = username + "@clemson.edu"

#         s = smtplib.SMTP("localhost")
#         s.sendmail("jdyken@clemson.edu", [username + "@clemson.edu"], msg.as_string())
#         s.quit()
