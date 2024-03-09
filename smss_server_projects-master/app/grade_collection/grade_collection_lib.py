#!/var/www/mthsc/common/venv/bin/python3

#TODO: create error message function for when user not authorized to view page

import csv
import datetime
import numpy as np
from operator import itemgetter
import os
import re
import sys

import time #TODO: remove

import MySQLdb
import cx_Oracle

sys.path.append("/var/www/mthsc/common") #TODO delete/comment out when running on apache server
from common_lib import commonFunctions

class grade_collection_lib(commonFunctions):
    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary
    #
    def get_cursor(self):
        # TODO -- update when live
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)


    #=============================================
    # accepts: str semester
    # returns: True/False if the setting was saved
    #
    def set_current_semester(self,semester):
        cursor = self.get_cursor()

        cursor.execute("UPDATE rolls.settings SET value = '{}' WHERE name = 'current_semester'".format(semester))

        cursor.execute("UPDATE Banner_info.settings SET value = '{}' WHERE name = 'current_semester'".format(semester))

        return self.set_setting("current_semester", semester)


    #=============================================
    # accepts: str year
    # returns: True/False if the setting was saved
    #
    def set_current_year(self,year):
        cursor = self.get_cursor()

        cursor.execute("UPDATE rolls.settings SET value = '{}' WHERE name = 'current_year'".format(year))

        cursor.execute("UPDATE Banner_info.settings SET value = '{}' WHERE name = 'current_year'".format(year))

        return self.set_setting("current_year", year)


    #=============================================
    # accepts: list list,
    #          ? value
    # returns: appends the value to the list if it doesn't contain it already
    #
    def add_if_not_in(self,list, value):
        if value not in list:
            list.append(value)

        return list

    #=============================================
    # accepts: string prefix,
    #          int course_num
    #          int default (optional)
    # returns: returns course_id or default value if this fails
    #
    def get_course_id(self,prefix, course_num, default=0):
        cursor = self.get_cursor()
        if "MTHSC" == prefix.upper() or "MTHS" == prefix.upper():
            prefix="MATH"
        elif "EXST" == prefix.upper() or "EX ST" == prefix.upper():
            prefix="STAT"

        if 1000>int(course_num):
            course_num*=10;

        cursor.execute("""SELECT course_id FROM course.course_list WHERE prefix='{}' AND course_num={}""".format(prefix,course_num));
        result=cursor.fetchall()
        if len(result)>0:
            return result[0].get('course_id',default)
        else:
            return default


    #=============================================
    # accepts: str username,
    #          str current roll
    # returns: str html for role box
    #
    def get_role_box(self,username, current_role):
        role_menu = ""

        user_roles = []

        if self.is_admin(username):
            self.add_if_not_in(user_roles, "admin")
            self.add_if_not_in(user_roles, "staff")
            self.add_if_not_in(user_roles, "coordinator")
            self.add_if_not_in(user_roles, "instructor")

        if self.is_staff(username):
            self.add_if_not_in(user_roles, "staff")

        if self.is_coordinator(username, self.get_current_semester(), self.get_current_year()):
            self.add_if_not_in(user_roles, "coordinator")
            self.add_if_not_in(user_roles, "instructor")

        if self.is_instructor(username, self.get_current_semester(), self.get_current_year()):
            self.add_if_not_in(user_roles, "instructor")

        user_roles.remove(current_role)

        if "admin" in user_roles:
            role_menu += """<li><a href="manage_exams">Admin</a></li>"""

        if "staff" in user_roles:
            role_menu += """<li><a href="manage_scantrons">Staff</a></li>"""

        if "coordinator" in user_roles:
            role_menu += """<li><a href="manage_versions">Coordinator</a></li>"""

        if "instructor" in user_roles:
            role_menu += """<li><a href="view_rolls">Instructor</a></li>"""

        if len(role_menu) > 0:
            role_html = f"""
<span class="role_box role_box_{current_role}">
    <div class="active_roll"><span class="down_arrow"></span> {current_role.capitalize()}</div>
    <div class="role_menu">
        <ul>
{role_menu}
        </ul>
    </div>
</span>
"""
        else:
            role_html = ""

        return role_html


    #=============================================
    # accepts: none
    # returns: html for menu
    #
    def get_menu(self):
        return """
<ul class="menu">
    <li><a href="main" title="Home" class="a_img_btn"><img src="static/images/home.png" alt="home"></a></li>
    <li><a href="view_rolls">View Rolls</a></li>
    <li><a href="manage_grades">Manage Grades</a></li>
    <li><a href="term_end">End of Term</a></li>
    <li><a href="reports">Reports</a></li>
    <li><a href="help">Help</a></li>
</ul>
"""


    #=============================================
    # accepts: none
    # returns: html for admin menu
    #
    def get_admin_menu(self):
        return """
<ul class="menu admin_menu">
    <li><a href="main" title="Home" class="a_img_btn"><img src="static/images/home.png" alt="home"></a></li>
    <li><a href="manage_exams">Manage Exams</a></li>
    <li><a href="course_manager">Manage Courses</a></li>
    <li><a href="settings">Settings</a></li>
    <li><a href="actions">Actions</a></li>
    <li><a href="admin_reports">Reports</a></li>
    <li><a href="view_grades">View Grades</a></li>
    <li><a href="admin_term_end">End of Term</a></li>
    <li><a href="admin_help">Help</a></li>
</ul>
"""


    #=============================================
    # accepts: none
    # returns: html for coord menu
    #
    def get_coord_menu(self):
        return """
<ul class="menu coord_menu">
    <li><a href="main" title="Home" class="a_img_btn"><img src="static/images/home.png" alt="home"></a></li>
    <li><a href="manage_versions">Manage Versions</a></li>
    <li><a href="coord_rolls">View Rolls</a></li>
    <li><a href="coord_term_end">End of Term</a></li>
    <li><a href="coord_reports">Reports</a></li>
    <li><a href="coord_help">Help</a></li>
</ul>
"""


    #=============================================
    # accepts: none
    # returns: html for staff menu
    #
    def get_staff_menu(self):
        return """
<ul class="menu staff_menu">
    <li><a href="main" title="Home" class="a_img_btn"><img src="static/images/home.png" alt="home"></a></li>
    <li><a href="manage_scantrons">Manage Scantrons</a></li>
</ul>
"""

    #=============================================
    # accepts: course_info dict
    # returns: html for download link
    #
    def get_download_rolls_link(self, course_info, filename):
        html = f"""
<div style="margin-top: 10px; padding-left: 2px; margin-bottom: 15px;">Click the button below to download a copy of the current rolls for <span style="font-weight: bold; font-size: 20px; color: #F66733;">{course_info.get('prefix')} {course_info.get('course_num')}</span>.</div>

<div class="container">
  <input type="submit" onclick="get_course_rolls('{filename}')" value="Download rolls">
</div>
"""
        return html


    #=============================================
    # accepts: form dict
    # returns: html for section enrollment summary table
    #
    def get_coord_section_summary(self, form):
        year     =form.get('year', self.get_current_year())
        semester =form.get('semester', self.get_current_semester())
        course_id=form.get('course_id')

        course_info = self.get_course_info(course_id)
        sections = self.get_course_sections(course_id, semester, year)

        html = f"""<div class="title">Section Enrollment Summary</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
"""

        summary_html = ""

        for section in sections:
            section_num = section.get("section_num")

            instructors_str = ",".join(self.get_instructors(section.get("offer_id")))
            num_students = len(self.get_roll(section.get("offer_id")))
            summary_html += f"""
    <tr>
        <td>{section_num}</td>
        <td>{instructors_str}</td>
        <td>{num_students:d}</td>
    </tr>
"""

        if len(summary_html) > 0:
            html += f"""
<table class="coord_enrollment_summary_tbl outline">
    <tr>
        <td>Section</td>
        <td>Instructor(s)</td>
        <td>Number of<br>Students</td>
    </tr>
{summary_html}
</table>
"""
        else:
            html += "Section enrollment summary not available."
        return html

    #=============================================
    # accepts: form dict
    # returns: html for course report table
    #
    def get_coord_fr_course_report_table(self, form):
        course_id=form.get("course_id")
        exam_id = form.get("exam_id")

        report = self.get_fr_course_report(exam_id)
        course_info = self.get_course_info(course_id)
        exam_info = self.get_exam(exam_id)

        html = f"""<div class="title">{exam_info.get('title')} FR</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
<div style="margin-top: 20px;">The normalized scores are green (above) or red (below) if the difference in average from other sections is significant at the 95% level. The normalized scores have not been adjusted for student skill level and often exhibit considerable variation over different exams by section.</div>
"""

        report_html = ""

        for section in report:
            section_num = f"{course_info.get('prefix')} {course_info.get('course_num')}-{section.get('section_num')}"

            color_flag = ""

            if section.get('normalized_score') > float(self.get_setting("normalized_score_max")):
                color_flag = """ style="color: #00AA00; font-weight: bold;" """
            elif section.get('normalized_score') < float(self.get_setting("normalized_score_min")):
                color_flag = """ style="color: #FF0000; font-weight: bold;" """

            report_html += f"""
    <tr>
        <td>{section_num}</td>
        <td>{section.get('instructors')}</td>
        <td>{section.get('num_students'):d}</td>
        <td>{section.get('avg_fr_percent'):0.2f}%</td>
        <td>{section.get('fr_score_avg'):0.2f}</td>
        <td>{section.get('fr_score_std'):0.2f}</td>
        <td{color_flag}>{section.get('normalized_score'):0.2f}</td>
    </tr>
"""

        if len(report) > 0:
            html += f"""
<table class="course_report_table outline">
    <tr>
        <td>Section</td>
        <td>Instructor(s)</td>
        <td>Number of<br>Test Takers</td>
        <td>Average<br>Percent</td>
        <td>Average<br>Score</td>
        <td>Standard<br>Deviation</td>
        <td>Normalized<br>Score</td>
    </tr>
{report_html}
</table>
"""

        else:
            html += "There is no report data yet. It will be available immediately once FR data is uploaded."
        return html


    #=============================================
    # accepts: form dict
    # returns: html for course report table
    #
    def get_coord_course_report(self, form):
        course_id=form.get("course_id")
        exam_id = form.get("exam_id")

        stats       = self.get_overall_course_stats(exam_id)
        report      = self.get_overall_course_report(exam_id)
        course_info = self.get_course_info(course_id)
        exam_info   = self.get_exam(exam_id)

        freq_table_html = ""

        if stats["num_test_takers"] > 0:
            for i in range(0, len(stats.get('freq_table'))):
                percentage = float(stats.get('freq_table')[i]) / stats.get('num_test_takers')
                freq_table_html += f"""
        <tr>
            <td style="text-align: right;">{10*i}%</td>
            <td style="vertical-align: middle; height: 30px;"><div style="background: #522D80; border: solid 1px #000000; height: 20px; width: {int(percentage*1000)}px;"></div></td>
            <td style="text-align: center;">{percentage:0.1%}</td>
        </tr>
"""

        html = f"""<div class="title">{exam_info.get('title')} Overall</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
<div style="margin-top: 20px;">The normalized scores are green (above) or red (below) if the difference in average from other sections is significant at the 95% level. The normalized scores have not been adjusted for student skill level and often exhibit considerable variation over different exams by section.</div>
"""

        report_html = ""

        for section in report:
            section_num = "{course_info.get('prefix')} {course_info.get('course_num')}-{section.get('section_num')}"

            color_flag = ""

            if section.get("normalized_score") > float(self.get_setting("normalized_score_max")):
                color_flag = """ style="color: #00AA00; font-weight: bold;" """
            elif section.get("normalized_score") < float(self.get_setting("normalized_score_min")):
                color_flag = """ style="color: #FF0000; font-weight: bold;" """

            report_html += f"""
    <tr>
        <td>section_num</td>
        <td>section.get('instructors')</td>
        <td>section.get('num_students')</td>
        <td>{section.get('score_avg'):0.2f}</td>
        <td>{section.get('score_std'):0.2f}</td>
        <td{coloar_flag}>{section.get('normalized_score'):0.2f}</td>
    </tr>
"""

        if len(report) > 0:
            num_test_takers=stats.get('num_test_takers')
            num_absent=stats.get('num_absent')
            num_missing=stats.get('num_missing')
            total_points=stats.get('total_points')
            avg_score=stats.get('avg_score')
            avg_score_perc=avg_score/total_points
            std_scores=stats.get('std_scores')
            std_scores_perc=std_scores/total_points
            coef_of_var=stats.get('coef_of_variation')
            median_score=stats.get('median_score')
            median_score_perc=median_score/total_points

            html += f"""
<table class="course_report_table outline">
    <tr>
        <td>Section</td>
        <td>Instructor</td>
        <td>Number of<br>Test Takers</td>
        <td>Average</td>
        <td>Standard<br>Deviation</td>
        <td>Normalized<br>Score</td>
    </tr>
{report_html}
</table>
    <div style="font-weight: bold; font-size: 24px; margin-top: 20px;">Statistics</div>
    <table>
        <tr>
            <td>Number of test takers:</td>
            <td>{num_test_takers}</td>
        </tr>
        <tr>
            <td>Number absent:</td>
            <td>{num_absent}</td>
        </tr>
        <tr>
            <td>Number missing:</td>
            <td>{num_missing}</td>
        </tr>
        <tr>
            <td>Total points:</td>
            <td>{total_points}</td>
        </tr>
        <tr>
            <td>Average Score:</td>
            <td>{avg_score:.1f} ({avg_score_perc:.1%})</td>
        </tr>
        <tr>
            <td>Standard Deviation:</td>
            <td>{std_scores:.1f} ({std_scores_perc:.1%})</td>
        </tr>
        <tr>
            <td>Coefficient of Variation:</td>
            <td>{coef_of_var:.1%}</td>
        </tr>
        <tr>
            <td>Median:</td>
            <td>{median_score:.1f} ({median_score_perc:.1%})</td>
        </tr>
    </table>
    <div style="margin-top: 20px; font-weight: bold; font-size: 24px;">Frequency Table</div>
    <table>
{freq_table_html}
    </table>
"""
        else:
            html += "There is no report data yet. It will be available immediately once MC or FR data is uploaded."


    #=============================================
    # accepts: dict exam info,
    #          dict course info,
    #          int exam id,
    #          tuple of dicts sections info
    # returns: html for overall course grades table
    #
    def get_coord_overall_course_grades_table(self, exam_info, course_info, exam_id, sections):
        html = f"""
<div class="title">{exam_info.get('title')}</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
<div style="margin-top: 20px;">
    <a class="btn_link" href="coord_reports?form_name=reports&action=overall_course_grades_download&exam_id={exam_id}">Download Data as CSV</a>
</div>
<table class="coord_grades_table">"""

        for section in sections:
            section_scores_html = f"""
            <table class="faint_outline overall_table">
                <tr>
                    <td>XID</td>
                    <td>Last Name</td>
                    <td>First Name</td>
                    <td>MC version</td>
                    <td>MC points</td>
                    <td>FR version</td>
                    <td>FR points</td>
                    <td>Total points</td>
                </tr>
            """

            scores = self.get_overall_scores(section.get('offer_id'), exam_id)

            for score in scores:
                score = self.nones_to_blanks(score)

                mc_points=self.pretty_print_number(score.get('mc_points'))
                fr_points=self.pretty_print_number(score.get('fr_points'))
                total_points=self.pretty_print_number(score.get('total_points'))

                section_scores_html += f"""
                <tr>
                    <td>{score.get('xid')}</td>
                    <td>{score.get('last_name')}</td>
                    <td>{score.get('first_name')}</td>
                    <td>{score.get('mc_version')}</td>
                    <td>{mc_points}</td>
                    <td>{score.get('fr_version')}</td>
                    <td>{fr_points}</td>
                    <td>{total_points}</td>
                </tr>
"""

            section_scores_html += "</table>"

            html += f"""
            <tr>
                <td>
                    <div class="section">{section.get('prefix')} {section.get('course_num')}-{section.get('section_num')}</div>
                    <div>{"</div><div>".join(self.get_instructors(section["offer_id"]))}</div>
                </td>
                <td>{section_scores_html}</td>
            </tr>"""
        html += "</table>"
        return html

    #=============================================
    # accepts: dict exam info,
    #          dict course info,
    #          int exam id,
    #          tuple of dicts section info
    # returns: file contents for overall course grades download
    #
    def get_coord_overall_course_grades_download(self, exam_info, course_info, exam_id, sections):
        fileContents=[["""Section Number,XID,Last Name,First Name,MC Version,MC Points,FR Version,FR Points,Total Points"""]]
        for section in sections:
            scores = self.get_overall_scores(section.get('offer_id'), exam_id)

            for score in scores:
                score = self.nones_to_blanks(score)
                mc_points=self.pretty_print_number(score.get('mc_points'))
                fr_points=self.pretty_print_number(score.get('fr_points'))
                total_points=self.pretty_print_number(score.get('total_points'))
                fileContents.append([section.get('section_num'),\
                                     score.get('xid'),\
                                     score.get('last_name'),\
                                     score.get('first_name'),\
                                     score.get('mc_version'),\
                                     mc_points,\
                                     score.get('fr_version'),\
                                     fr_points, total_points])

        #TODO file name is set in ajax call
        #filename="%s_%s_%s_%s_%s_grades.csv";\r""" % (exam_info.get('semester'), exam_info.get('year'), course_info.get('prefix'), course_info.get('course_num'), exam_info.get('title').replace(" ", "_")))
        #e.g. spring_2021_MATH_1040_Exam_1_grades.csv
        return fileContents

    #=============================================
    # accepts: exam info dict,
    #          course info dict,
    #          exam id int,
    #          section tuple of dicts
    # returns: html for overall course raw data table
    #
    def get_coord_overall_course_raw_data_table(self, exam_info, course_info, exam_id, sections):
        html = f"""
<div class="title">{exam_info.get('title')}</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
<div style="margin-top: 20px;">
    <a class="btn_link" href="coord_reports?form_name=reports&action=overall_course_raw_data_download&exam_id={exam_id}">Download Data as CSV</a>
</div>
<table class="coord_grades_table">"""

        key_version = self.get_exam_key_version(exam_id)
        fr_questions = self.get_fr_questions(exam_id, key_version)
        num_of_fr_questions = len(fr_questions)

        fr_question_num_html = "</td>\n\t<td>".join([question["question_num"] for question in fr_questions])

        if len(fr_question_num_html) > 0:
            fr_question_num_html = f"<td>{fr_question_num_html}</td>"

        for section in sections:
            section_raw_data_html = f"""
            <table class="faint_outline overall_table">
                <tr>
                    <td>XID</td>
                    <td>Last Name</td>
                    <td>First Name</td>
                    <td>MC version</td>
                    <td>MC responses</td>
                    <td>Number<br>Right</td>
                    <td>Number<br>Wrong</td>
                    <td>Number<br>Blank</td>
                    <td>Number<br>Mismarked</td>
                    <td>Graded</td>
                    <td>MC points</td>
                    <td>FR version</td>
{fr_question_num_html}
                    <td>FR points</td>
                    <!--<td>Total points</td>-->
                </tr>
            """

            # we assume that there should be the same set of students in the same order for each of these functions
            mc_data = self.get_mc_responses(section["offer_id"], exam_id)
            fr_data = self.get_fr_scores(section["offer_id"], exam_id)

            for i in range(0, len(mc_data)):
                cur_mc = self.nones_to_blanks(mc_data[i])
                cur_fr = self.nones_to_blanks(fr_data[i])

                fr_scores_html = "</td>\n\t<td>".join([self.pretty_print_number(temp) for temp in cur_fr["scores"]])

                if len(fr_scores_html) > 0:
                    fr_scores_html = f"\t<td>{fr_scores_html}</td>"
                else:
                    fr_scores_html = "\t<td></td>\n" * num_of_fr_questions

                mc_points_earned=self.pretty_print_number(cur_mc.get('points_earned'))
                fr_points_earned=self.pretty_print_number(cur_fr.get('points_earned'))
                section_raw_data_html += f"""
                        <tr>
                            <td>{cur_mc.get('xid')}</td>
                            <td>{cur_mc.get('last_name')}</td>
                            <td>{cur_mc.get('first_name')}</td>
                            <td>{cur_mc.get('version')}</td>
                            <td>{cur_mc.get('responses')}</td>
                            <td>{cur_mc.get('num_right')}</td>
                            <td>{cur_mc.get('num_wrong')}</td>
                            <td>{cur_mc.get('num_blank')}</td>
                            <td>{cur_mc.get('num_mismarked')}</td>
                            <td>{cur_mc.get('graded')}</td>
                            <td>{mc_points_earned}</td>
                            <td>{cur_fr.get('version')}</td>
{fr_scores_html}
                            <td>{fr_points_earned}</td>
                        </tr>
                    """
            section_raw_data_html += "</table>"

            html += f"""
            <tr>
                <td>
                    <div class="section">{section.get('prefix')} {section.get('course_num')}-{section.get('section_num')}</div>
                    <div>{"</div><div>".join(self.get_instructors(section["offer_id"]))}</div>
                </td>
                <td>{section_raw_data_html}</td>
            </tr>"""
        html += "</table>"
        return html

    #=============================================
    # accepts: dict exam info,
    #          dict course info,
    #          int exam id,
    #          tuple of dicts of section info
    # returns: file contents for overall course raw data download
    #
    def get_coord_overall_course_raw_data_download(self, exam_info, course_info, exam_id, sections):
        key_version = self.get_exam_key_version(exam_id)
        fr_questions = self.get_fr_questions(exam_id, key_version)
        num_of_fr_questions = len(fr_questions)

        fr_question_num_csv = ",".join([question["question_num"] for question in fr_questions])

        if len(fr_question_num_csv) > 0:
            fr_question_num_csv += ","

        fileContents = [["""Section Number,XID,Last Name,First Name,MC Version,MC responses,Number Right,Number Wrong,Number Blank,Number Mismarked,Graded,MC points,FR version,{fr_question_num_csv}FR points"""]]

        for section in sections:
            # we assume that there should be the same set of students in the same order for each of these functions
            mc_data = self.get_mc_responses(section.get('offer_id'), exam_id)
            fr_data = self.get_fr_scores(section.get('offer_id'), exam_id)

            for i in range(0, len(mc_data)):
                cur_mc = self.nones_to_blanks(mc_data[i])
                cur_fr = self.nones_to_blanks(fr_data[i])

                fr_scores_csv = ",".join([self.pretty_print_number(temp) for temp in cur_fr["scores"]])

                if len(fr_scores_csv) > 0:
                    fr_scores_csv += ","
                else:
                    fr_scores_csv = "," * num_of_fr_questions

                mc_points_earned=self.pretty_print_number(cur_mc.get('points_earned'))
                fr_points_earned=self.pretty_print_number(cur_fr.get('points_earned'))
                fileContents.append([section.get('section_num'),\
                                     cur_mc.get('xid'),\
                                     cur_mc.get('last_name'),\
                                     cur_mc.get('first_name'),\
                                     cur_mc.get('version'),\
                                     cur_mc.get('responses'),\
                                     cur_mc.get('num_right'),\
                                     cur_mc.get('num_wrong'),\
                                     cur_mc.get('num_blank'),\
                                     cur_mc.get('num_mismarked'),\
                                     cur_mc.get('graded'),\
                                     mc_points_earned,\
                                     cur_fr.get('version'),\
                                     fr_scores_csv,\
                                     fr_points_earned])


        #TODO file name is set in ajax call
        #filename="%s_%s_%s_%s_%s_grades.csv";\r""" % (exam_info.get('semester'), exam_info.get('year'), course_info.get('prefix'), course_info.get('course_num'), exam_info.get('title').replace(" ", "_")))
        #e.g. spring_2021_MATH_1040_Exam_1_raw_data.csv
        return fileContents

    #=============================================
    # accepts: dict form
    # returns: html for view class-section role table
    #
    def get_view_rolls_table(self, form):
        offer_id=form.get('offer_id')

        offer_info = self.get_offer_info(offer_id)
        students   = self.get_roll(offer_id)
        html       = str(len(students))

        if len(students) > 0:
            section_description = f"{offer_info.get('prefix')} {offer_info.get('course_num')}-{offer_info.get('section_num')}"

            student_count_str = ""

            if len(students) != 1:
                student_count_str = f"({len(students)} students)"
            else:
                student_count_str = "(1 student)"

            html = f"""
<div class="title">{section_description}</div>
<div class="subtitle">{student_count_str}</div>
<table class="outline rolls_tbl">
    <tr>
        <td>Last Name</td>
        <td>First Name</td>
        <td>Username</td>
        <td>XID</td>
    </tr>
"""

            for student in students:
                html += f"""
    <tr>
        <td>{student.get('last_name')}</td>
        <td>{student.get('first_name')}</td>
        <td>{student.get('username')}</td>
        <td>{student.get('xid')}</td>
    </tr>
"""

            html += """
</table>
"""
        return html


    #=============================================
    # accepts: dict current_course_responses
    # returns: html for view scantrons table
    #
    def get_view_scantrons_table(self, responses):
        html = """
<table class="scantron_table">
"""

        for section in responses:
            response_table = """
            <table class="faint_outline scantron_responses">
                <tr>
                    <td>XID</td>
                    <td>Scantron<br>Version</td>
                    <td>Scantron Responses</td>
                </tr>"""

            for response in section.get("responses"):
                response = self.nones_to_blanks(response)

                response_table += f"""
                <tr>
                    <td>{response.get('xid')}</td>
                    <td>{response.get('orig_scantron_version')}</td>
                    <td>{response.get('orig_scantron_responses')}</td>
                </tr>
"""

            response_table += """       </table>
"""

            html += f"""
    <tr>
        <td>{section.get('prefix')} {section.get('course_num')}-{section.get('section_num')}</td>
        <td>
{response_table}
        </td>
    </tr>
"""

        html += """
</table>
"""
        return html

    #=============================================
    # accepts: dict course_info,
    #          dict exam_info
    # returns: html for upload scantrons form
    #
    def get_upload_scantron_form(self, course_info, exam_info):
        html = f"""
<div style="margin-top: 10px; padding-left: 2px; margin-bottom: 15px;">Choose the scantron file to upload responses to the grade collection system.</div>
<div class="example_link" id="example_btn" onclick="toggle_examples()">Show Examples</div>
<div id="examples" class="examples">
    <div>We should give some directions on processing the scantrons here.</div>
</div>

<div class="msg" style="margin-top: 15px;"><span id="msg"><span></div>
<table class="data_table" style="margin-top: 15px;">
    <tr>
        <td>Course and Exam:</td>
        <td>{course_info.get('prefix')} {course_info.get('course_num')} {exam_info.get('title')}<br>(if this is not correct then choose the correct course in the dropdown list above)</td>
    </tr>
    <tr>
        <td>Column Format:</td>
        <td>XID, version, test number, responses</td>
    </tr>
    <tr>
        <td>Data:</td>
        <td>
            <!--<textarea id="data" style="height: 600px; width: 400px;"></textarea>-->
            <iframe name="hidden_form" id="hidden_form" src="scantrons_ajax" style="height: 0px; width: 0px; display: none;" onload="handle_iframe()"></iframe>
            <form method="POST" id="scantron_upload_form" target="hidden_form" action="manage_scantrons" enctype="multipart/form-data">
                <input type="file" name="data" id="data" accept="text/csv" required>
                <input type="hidden" name="form_name" value="submit_scantrons">
                <input type="hidden" name="course_id" value="{course_info.get('course_id')}">
                <input type="hidden" name="exam_id" value="{exam_info.get('exam_id')}">
            </form>
        </td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: center;">
            <button type="button" onclick="submit_scantrons()">Submit Data</button>
        </td>
    </tr>
</table>
        """
        return html

    #=============================================
    # accepts: form dict
    # returns: processed scantrons in table form
    #
    def process_and_get_scantron_table(self, form, form_data):
        exam_id=form.get("exam_id")

        ## get versions for exam_id
        cursor = self.get_cursor()
        # note: the version comparison is case sensitive
        cursor.execute("SELECT version FROM versions WHERE exam_id = '{}'".format(exam_id))
        version_list=[item.get('version') for item in cursor.fetchall()]
        # dictionary of answer choices for each version of exam
        choices_dict={}
        for version in version_list:
            choices_dict[version]=self.get_mc_choices(exam_id, version)

        # list of tuples used to store all valid mc responses from a class to save with a single db call
        valid_mc_responses_list=[]

        try:
            if form_data!=None and form_data.filename:
                # read file and clean up data
                data = form_data.read().decode('utf-8').replace(" ", "").splitlines()
            else:
                data = ""

            if len(data) > 0:
                html = """<table class="outline scantron_data_table">
    <tr>
        <td>XID</td>
        <td>Version</td>
        <td>Responses</td>
        <td>Saved/Error</td>
    </tr>
"""
                for record in data:
                    # record: XID, version test number, responses
                    record = record.replace(" ","").split(",")
                    record = [x.strip() for x in record]

                    error = False
                    msg = ""

                    # first we get rid of the test number
                    del record[2]

                    # convert the blanks to "-" and multiple responses to "?"
                    for i in range(2, len(record)):
                        if len(record[i]) == 0:
                            record[i] = "-"
                        elif len(record[i]) != 1:
                            record[i] = "?"

                    # change the first "CUID" character to a "C"
                    # store all responses in the 3rd slot
                    record = ["C" + record[0][1:], record[1], "".join(record[2:])]

                    if not self.is_valid_xid(record[0]):
                        error = True
                        msg = "The XID is not valid."
                    elif False: #not self.is_student_in_section(record[0], offer_id):
                        error = True
                        msg = "This student is not in the course."
                    elif not record[1] in version_list: #self.is_version_valid(exam_id, record[1]):
                        # PW 2021-07-06: version_list created to avoid repeatedly accessing db
                        error = True
                        msg = "The version is not valid."
                    else:
                        #TODO: result[1] is empty if the list of responses is empty (a.k.a. empty exam)
                        result = self.force_mc_responses_to_be_valid(exam_id, record[1], record[2],choices_dict)
                        if not result[0]:
                            error = True
                            msg = result[2]

                        # the responses may have changed
                        record[2] = result[1]
                        msg = result[2]
                    if error:
                        # print(record)
                        html += f"""
<tr>
    <td colspan="3">{  ','.join(record)  }</td>
    <td><span class="error">{  msg  }</span></td>
</tr>
"""

                    else:
                        # save this valid record to a variable
                        # self.store_scantron_mc_responses(record[0], exam_id, record[1], record[2])
                        [xid, version, responses]=record
                        valid_mc_responses_list.append((xid,\
                                                        exam_id,\
                                                        version,\
                                                        version,\
                                                        responses,\
                                                        responses))

                        if len(msg) == 0:
                            msg = "saved"

                        html += f"""
<tr>
    <td>{  record[0]  }</td>
    <td>{  record[1]  }</td>
    <td>{  record[2]  }</td>
    <td><span style="color: #00AA00;">{  msg  }</td>
</tr>
"""
                    #end for loop

                html += "</table>"
                store_all_valid_scantron_mc_responses(exam_id, valid_mc_responses_list)
            else:
                html = "There doesn't seem to be any data uploaded."

        except:
            html = f"""An error has occured: {str(sys.exc_info())}"""
            html = html.replace("<", "&lt;").replace(">", "&gt;")
            html = f"""<span class="error">{html}</span>"""
        return html


    #=============================================
    # accepts: int course_id,
    #          int exam_id
    # returns: html to display table of exam versions and exam keys
    #
    def get_view_version_and_keys_table(self, course_id, exam_id):
        # generate out the version section
        key_version = self.get_exam_key_version(exam_id)
        versions = self.get_exam_versions(exam_id)

        if len(versions) > 0:
            exam_info = self.get_exam(exam_id)
            course_info = self.get_course_info(course_id)
            course_description = f"{course_info.get('prefix')} {course_info.get('course_num')}"

            html = f"""
<div class="title">{exam_info.get('title')} Versions</div>
<div class="subtitle">{course_description}</div>
<table class="outline version_table">
    <tr>
        <td colspan="2"></td>
        <td>Version</td>
        <td></td>
    </tr>
"""

            for version in versions:
                choices = self.get_mc_choices(exam_id, version)

                question_num_cells = ""
                key_version_question_num_cells = ""
                choices_cells = ""

                for choice in choices:
                    question_num_cells += f"""
        <td>{choice.get('question_num')}</td>"""
                    key_version_question_num_cells += f"""
        <td>{choice.get("key_version_question_num")}</td>"""
                    choices_cells += f"""
        <td>{choice.get("choices")}</td>"""

                if len(choices) > 0:
                    choice_table = f"""
<table class="faint_outline">
    <tr>
        <td rowspan="3" style="width: 25px;">MC</td>
        <td style="text-align: left;">Question Number</td>
{question_num_cells}
    </tr>
    <tr>
        <td style="text-align: left; min-width: 275px;">Key Version Question Number</td>
{key_version_question_num_cells}
    </tr>
    <tr>
        <td style="text-align: left;">Choices Permutation</td>
{choices_cells}
    </tr>
</table>
"""
                else:
                    choice_table = ""

                fr_questions = self.get_fr_questions(exam_id, version)

                question_num_cells = ""
                key_version_question_num_cells = ""
                points_cells = ""

                for question in fr_questions:
                    question_num_cells += f"""
        <td>{question.get("question_num")}</td>"""
                    key_version_question_num_cells += f"""
        <td>{question.get("key_version_question_num")}</td>"""
                    points_cells += f"""
        <td>{self.pretty_print_number(question.get("points"))}</td>"""

                if len(fr_questions) > 0:
                    points_table = f"""
<table class="faint_outline" style="margin-top: 15px;">
    <tr>
        <td rowspan="3" style="width: 25px;">FR</td>
        <td style="text-align: left;">Question Number</td>
{question_num_cells}
    </tr>
    <tr>
        <td style="text-align: left; min-width: 275px;">Key Version Question Number</td>
{key_version_question_num_cells}
    </tr>
    <tr>
        <td style="text-align: left;">Points</td>
{points_cells}
    </tr>
</table>
"""
                else:
                    points_table = ""



                html += f"""
    <tr>
        <td><img class="img_btn" onclick="delete_version({course_id}, {exam_id}, '{version}')" src="static/images/del.png"></td>
        <td><img class="img_btn" onclick="edit_version({course_id}, {exam_id}, '{version}')" src="static/images/edit.png"></td>
        <td>{version}</td>
        <td>
{choice_table}
{points_table}
        </td>
    </tr>
"""

            html += "</table>"

            # generate out the key section
            exam_info = self.get_exam(exam_id)
            course_info = self.get_course_info(course_id)
            course_description = f"{course_info.get('prefix')} {course_info.get('course_num')}"

            choices = self.get_mc_choices(exam_id, exam_info.get("key_version"))

            question_num_cells = ""

            for choice in choices:
                question_num_cells += f"""
        <td>{choice.get('question_num')}</td>"""

            question_num_row = f"""
    <tr>
        <td>Question Number</td>
{question_num_cells}
    </tr>
"""

            html += f"""
<p style="margin-top: 30px;">The highlighted version in the tables below is the key in the grade collection system. The keys for the other versions are calculated from this baseline key using the question choice pairing shown above.</p>
<div class="title">{exam_info.get('title')} Keys</div>
<div class="subtitle">{course_description}</div>
"""
            # print a separate table for each key
            for version in versions:
                html += f"""
<table class="outline version_table">
{question_num_row}
"""

                highlight = ""
                if version == key_version:
                    highlight = " class=\"row_highlight\""
                key = self.get_mc_key(exam_id, version)

                point_cells = ""
                key_cells = ""

                for answer in key:
                    point_cells += f"""
        <td>{self.pretty_print_number(answer.get("points"))}</td>"""

                    key_cells += f"""
        <td>{answer.get('correct_answers')}</td>"""

                html += f"""
    <tr>
        <td>Points</td>
{point_cells}
    </tr>
    <tr {highlight}>
        <td>Version {version}</td>
{key_cells}
    </tr>
</table>
"""

            html += "</table>"
        else:
            html = "No versions exist for this exam."
        return html


    #=============================================
    # accepts: int course_id,
    #          int exam_id
    # returns: html to submit key for exam
    #
    def get_html_to_submit_key(self, course_id, exam_id):
        course_info = self.get_course_info(course_id)
        exam_info = self.get_exam(exam_id)

        key_version = self.get_exam_key_version(exam_id)
        key = self.get_raw_key(exam_id)
        key_data = "\n".join(["{}\t{}".format(self.pretty_print_number(question.get('points')), question.get('correct_answers')) for question in key])

        fr_data = self.get_raw_fr_questions(exam_id)
        fr_data = "\n".join(["{}\t{}".format(question.get('question_num'), self.pretty_print_number(question.get('points'))) for question in fr_data])

        if len(key) > 0:
            instructions = "Fill in the data below to update the key for this exam."
            btn_text = "Update key"
        else:
            instructions = "Fill in the data below to add a key for this exam."
            btn_text = "Add key"

        html = f"""
<div style="margin-top: 10px; padding-left: 2px; margin-bottom: 15px;">{  instructions  } There is only one key for each exam. You specify which version it corresponds to and the other keys are derived from it using the question choice pairings stored for each version.</div>
<div class="example_link" id="example_btn" onclick="toggle_examples()">Show Examples</div>
<div id="examples" class="examples">
    <div>Entering the following for the MC data means:
        <ul>
            <li>Question 1 is worth 0 points and either answer A or C is correct</li>
            <li>Question 2 is worth 3 points and only answer A is correct</li>
            <li>Question 3 is worth 3 points and only answer B is correct</li>
            <li>Question 4 is worth 4 points and only answer D is correct</li>
        </ul>
    </div>
    <textarea style="width: 200px; height: 80px; margin-top: 15px;" disabled>
0,AC
3,A
3,B
4,D
</textarea>
    <div style="margin-top: 10px;">Entering the following for the FR data means:
        <ul>
            <li>Question 1 is worth 0 points</li>
            <li>Question 2a is worth 2.5 points</li>
            <li>Question 2b is worth 4 points</li>
            <li>The scantron is worth 1 point</li>
        </ul>
    </div>
    <textarea style="width: 200px; height: 80px; margin-top: 15px;" disabled>
1,0
2a,2.5
2b,4
scantron,1
</textarea>
</div>

<div class="msg" style="margin-top: 15px;"><span id="msg"><span></div>
<table class="data_table" style="margin-top: 15px;">
    <tr>
        <td style="min-width: 170px;">Course and Exam:</td>
        <td><span style="font-weight: bold; color: #F66733; font-size: 24px;">{  course_info.get('prefix')  } {  course_info.get('course_num')  } {  exam_info.get('title')  }</span><br>(if this is not correct then choose the correct course in the dropdown list above)</td>
    </tr>
    <tr>
        <td>Version:</td>
        <td><input type="text" id="key_version" value="{  key_version  }" size="3"></td>
    </tr>
    <tr>
        <td>MC Column Format:</td>
        <td>
            <div>points for the question (a number in the range (0,999) with at most two optional decimal digits), all answers that are correct for the question (with no space in between and using capital letters)</div>
            <div>[the columns can be separated by a comma or a tab or a mix of both]</div>
        </td>
    </tr>
    <tr>
        <td>MC Data:</td>
        <td><textarea id="mc_data" style="height: 450px; width: 400px;">{  key_data  }</textarea></td>
    </tr>
    <tr>
        <td>FR Column Format:</td>
        <td>question number (a string of at most 10 characters without any commas), points for the question (a number in the range (0,999) with at most two optional decimal digits)</td>
    </tr>
    <tr>
        <td>FR Data:</td>
        <td><textarea id="fr_data" style="height: 150px; width: 400px;">{  fr_data  }</textarea></td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: center;">
            <button type="button" id="submit_key_btn" onclick="submit_key()">{  btn_text  }</button>
            <input type="hidden" id="data_course_id" value="{  course_id  }">
            <input type="hidden" id="data_exam_id" value="{  exam_id }">
        </td>
    </tr>
</table>
"""
        return html


    #=============================================
    # accepts: int course_id,
    #          int exam_id,
    #          str old_version
    # returns: html to submit key for exam
    #
    def get_html_to_modify_version(self, course_id, exam_id, old_version):
        course_info = self.get_course_info(course_id)
        exam_info   = self.get_exam(exam_id)

        choices = self.get_mc_choices(exam_id, old_version)

        choice_data = "\n".join(["{}\t{}".format(choice.get('key_version_question_num'), "\t".join(list(choice.get('choices')))) for choice in choices])

        fr_questions = self.get_fr_questions(exam_id, old_version)

        fr_data = "\n".join(["{}\t{}".format(question.get('question_num'), question.get('key_version_question_num')) for question in fr_questions])

        if old_version != "":
            instructions = "Fill in the data below to update this version of this exam."
            btn_text = "Update version"
        else:
            instructions = "Fill in the data below to add a version for this exam."
            btn_text = "Add version"

        html = f"""
<div style="margin-top: 10px; margin-bottom: 10px; padding-left: 2px;">{instructions}
    <ul>
        <li>Use one row for each question in the version (they should be in order from question 1 to the last question)</li>
        <li>For MC
            <ul>
                <li>In each row enter the corresponding question number in the key version and then the permutation of the choices from the key version (separated by tabs or commas or a mix of both)</li>
                <li>When entering the version the key is based off of (usually version A) you will probably have ABCD for each question since they are the original choices</li>
            </ul>
        </li>
        <li>For FR
            <ul>
                <li>In each row enter the question number for the version and the corresponding question number in the key version. (the columns can be separated by a tab or a space or a mix of both for different rows)</li>
                <li>When entering the version the key is based off of (usually version A) you should have the same question number repeated twice on each line.</li>
                <li>If you do not change the order of questions for FR, each version will look identical to the key version.</li>
            </ul>
        </li>
    </ul>
</div>
<div class="example_link" id="example_btn" onclick="toggle_examples()">Show Examples</div>
<div id="examples" class="examples">
    <div>Assuming version A is the key version, entering the following for MC data means:
        <ul>
            <li>Version B question 1 is the same as Version A question 4, but has A and D switched</li>
            <li>Version B question 2 is the same as Version A question 3, but has C and D switched</li>
            <li>Version B question 3 is the same as Version A question 2, but has has the choices reversed</li>
            <li>Version B question 4 is the same as Version A question 1, but has the choices shifted back by one</li>
        </ul>
    </div>
    <table style="border-collapse: collapse; border-spacing: 0px;">
        <tr>
            <td style="padding-bottom: 0px;">Version A</td>
            <td style="width: 40px;"></td>
            <td style="padding-bottom: 0px;">Version B</td>
        </tr>
        <tr>
            <td>
                <textarea style="width: 200px; height: 80px;" disabled>
1,A,B,C,D
2,A,B,C,D
3,A,B,C,D
4,A,B,C,D
</textarea>
            </td>
            <td></td>
            <td>
                <textarea style="width: 200px; height: 80px;" disabled>
4,D,B,C,A
3,A,B,D,C
2,D,C,B,A
1,B,C,D,A
</textarea>
            </td>
        </tr>
    </table>
    <div style="margin-top: 15px;">Assuming version A is the key version, entering the following for FR data means:
        <ul>
            <li>Version B has questions 1a and 1b switched from version A.</li>
            <li>Version B has questions 2 and 3 switched from version A.</li>
        </ul>
    </div>
    <table style="border-collapse: collapse; border-spacing: 0px;">
        <tr>
            <td style="padding-bottom: 0px;">Version A</td>
            <td style="width: 40px;"></td>
            <td style="padding-bottom: 0px;">Version B</td>
        </tr>
        <tr>
            <td>
                <textarea style="width: 200px; height: 80px;" disabled>
1a,1a
1b,1b
2,2
3,3
</textarea>
            </td>
            <td></td>
            <td>
                <textarea style="width: 200px; height: 80px;" disabled>
1a,1b
1b,1a
2,3
3,2
</textarea>
            </td>
        </tr>
    </table>
</div>
<div class="msg"><span id="msg"><span></div>
<table class="data_table" style="margin-top: 15px;">
    <tr>
        <td style="min-width: 170px;">Course and Exam:</td>
        <td><span style="font-weight: bold; color: #F66733; font-size: 24px;">{course_info.get('prefix')} {course_info.get('course_num')} {exam_info.get('title')}</span><br>(if this is not correct then choose the correct course and exam in the dropdown lists above)</td>
    </tr>
    <tr>
        <td>Version:</td>
        <td><input type="text" id="new_version" value="{old_version}" size="3"></td>
    </tr>
    <tr>
        <td>MC Column Format:</td>
        <td>Corresponding question number in key version, permutation of the key choices (separated by tabs or commas or a mix of both)</td>
    </tr>
    <tr>
        <td>MC Data:</td>
        <td><textarea id="mc_data" style="width: 500px; height: 400px;">{choice_data}</textarea></td>
    </tr>
    <tr>
        <td>FR Column Format:</td>
        <td>Question number for this version, Corresponding question number in key version</td>
    </tr>
    <tr>
        <td>FR Data:</td>
        <td><textarea id="fr_data" style="width: 500px; height: 400px;">{fr_data}</textarea></td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: center;">
            <button type="button" id="submit_version_btn" onclick="submit_version()">{btn_text}</button>
            <input type="hidden" id="data_course_id" value="{course_id}">
            <input type="hidden" id="data_exam_id" value="{exam_id}">
            <input type="hidden" id="old_version" value="{old_version}">
        </td>
    </tr>
</table>
"""
        return html

    #=============================================
    # accepts: str semester,
    #          str year,
    #          str course_id
    # returns: html to view/generate coord end of term summary w/o dropped students
    #
    def get_html_to_print_term_summary_old(self, semester, year, course_id):
        course_info = self.get_course_info(course_id)

        sections = self.get_course_sections(course_id, semester, year)

        html = f"""
<div style="margin-top: 30px;">The following is a summary of all the grades submitted during the term. Items highlighted in orange are missing, while items highlighted in blue mean the student was marked as absent for that item. Items colored light grey indicate the student was withdrawn.</div>
<div class="title">{semester.capitalize()} {year} Term Summary</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
<div style="margin-top: 20px;">
    <a class="btn_link" href="coord_term_end?form_name=term_end&action=term_summary_download&course_id={course_id}&semester={semester}&year={year}">Download Data as CSV</a>
</div>
<table class="coord_term_table">
"""

        csv_header_1 = ""
        csv_header_2 = ""
        csv_data = ""

        for section in sections:
            offer_id = section.get('offer_id')
            data = self.get_term_summary_full(offer_id)
            # PW 2021-08-08: trying to reduce number of db queries


            if len(data) > 0:
                # so we only get the header once
                csv_header_1 = "Section,Last name,First name,XID,Status"
                csv_header_2 = ",,,,"

                offer_info = self.get_offer_info(offer_id)

                exams  = data.get('exams')
                scores = data.get('scores')

                exam_header_1 = ""
                exam_header_2 = ""

                for exam in exams:
                    # rowspan=0 should work, but Chrome doesn't like it
                    exam_header_1 += f"""
        <td class="term_summary_spacer" rowspan="{len(scores)+2:d}"></td>
        <td colspan="3">{exam.get('title')}</td>
"""
                    exam_header_2 += """
        <td>MC</td>
        <td>FR</td>
        <td>Overall</td>
"""

                    csv_header_1 += f",{exam.get('title')},,"
                    csv_header_2 += ",MC,FR,Overall"

                section_html = f"""
<table class="outline view_term_summary_tbl">
    <tr>
        <td rowspan="2">Last Name</td>
        <td rowspan="2">First Name</td>
        <td rowspan="2">XID</td>
{exam_header_1}
    </tr>
    <tr>
{exam_header_2}
    </tr>
"""

                for entry in scores:
                    student = self.nones_to_blanks(entry.get('student'))
                    cur_exam_scores = entry.get('exam_scores')
                    reg_code = entry.get('student').get('reg_stat_code')

                    exam_html = ""
                    cur_scores_csv_data = ""

                    for exam in exams:
                        score_data = cur_exam_scores.get(exam.get('exam_id'))

                        mc_class = ""
                        fr_class = ""
                        total_class = ""

                        if score_data.get('absent'):
                            absent_class = " class=\"absent\""
                            mc_class = absent_class
                            fr_class = absent_class
                            total_class = absent_class
                        else:
                            missing_data_class = " class=\"missing_data\""
                            withdraw_class = " class=\"withdrawn\""
                            if exam.get('num_mc_questions') > 0 and score_data.get('mc_points') is None:
                                mc_class = missing_data_class

                            if exam.get('num_fr_questions') > 0 and score_data.get('fr_points') is None:
                                fr_class = missing_data_class

                            if len(mc_class) > 0 or len(fr_class) > 0:
                                total_class = missing_data_class

                            if score_data.get('mc_points') is None and score_data.get('fr_points') is None and reg_code in ['AU', 'AW', 'DD', 'DE', 'DN', 'DW', 'WD', 'WL', 'WW']:
                                mc_class = withdraw_class
                                fr_class = withdraw_class
                                total_class = withdraw_class

                        # wait to do this here so we can assign classes
                        score_data = self.nones_to_blanks(score_data)

                        exam_html += f"""
        <td{mc_class}>{score_data.get('mc_points')}</td>
        <td{fr_class}>{score_data.get('fr_points')}</td>
        <td{total_class}>{score_data.get('total_points')}</td>
        """

                        cur_scores_csv_data += f"{score_data.get('mc_points')},{score_data.get('fr_points')},{score_data.get('total_points')},"

                    section_html += f"""
    <tr>
        <td>{student.get('last_name')}</td>
        <td>{student.get('first_name')}</td>
        <td>{student.get('xid')}</td>
{exam_html}
    </tr>
"""

                    if reg_code in ['AU', 'AW', 'DD', 'DE', 'DN', 'DW', 'WD', 'WL', 'WW']:
                        status = "Withdrawn"
                    else:
                        status = ""

                    csv_data += f"'{section.get('section_num')}',{student.get('last_name')},{student.get('first_name')},{student.get('xid')},{status},{cur_scores_csv_data}\n"

                section_html += "</table>"

                html += f"""
<tr>
    <td>
        <div class="section">{section.get('prefix')} {section.get('course_num')}-{section.get('section_num')}</div>
        <div>{"</div><div>".join(self.get_instructors(section.get('offer_id')))}</div>
    </td>
    <td>
{section_html}
    </td
</tr>
"""

            else:
                html = "No data"
        return html


    #=============================================
    # accepts: str semester,
    #          str year,
    #          str course_id
    # returns: html to view/generate coord end of term summary w/ dropped students
    #          generates csv term summary
    def get_html_to_print_term_summary(self, semester, year, course_id, local_filename):
        course_info = self.get_course_info(course_id)

        prefix=course_info.get('prefix')
        course_num=course_info.get('course_num')
        download_filename=f"{semester.capitalize()}_{year}_{prefix}_{course_num}_term_summary.csv"

        sections = self.get_course_sections(course_id, semester, year)
        list_of_section_nums=[section.get('section_num') for section in sections]
        students_by_section_by_xid=dict()
        for section_num in list_of_section_nums:
            students_by_section_by_xid[section_num]=dict()

        html = f"""
<div style="margin-top: 30px;">The following is a summary of all the grades submitted during the term. Items highlighted in orange are missing, while items highlighted in blue mean the student was marked as absent for that item. Items colored light grey indicate the student was withdrawn.</div>
<div class="title">{semester.capitalize()} {year} Term Summary</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
<div style="margin-top: 20px;">
    <input class="btn_link" type="submit" onclick="get_end_of_term_csv('{download_filename}')" value="Download Data as CSV"></input>
</div>
<table class="coord_term_table">
"""

        csv_header_1 = ""
        csv_header_2 = ""
        csv_data = []

        # gather info on students in all sections taking this class
        term_code=self.get_term_code(semester,year)
        cursor=self.get_cursor()
        cursor.execute("""
SELECT
  cr.xid, reg_stat_code, last_name, IF(preferred_name IS NULL, first_name, preferred_name) AS first_name, cr.term_code, cr.subject_code, cr.course_number, cr.section_number
FROM
  rolls.full_rolls AS cr
  LEFT JOIN
    Banner_info.student_info AS si ON si.xid=cr.xid
WHERE
  term_code={} AND (subject_code='{}' OR subject_code='{}') AND course_number={}
ORDER BY
  section_number, last_name, first_name, xid""".format(term_code, course_info.get('prefix'), course_info.get('alt_prefix'), course_info.get('course_num')))
        all_sections_all_students_info=cursor.fetchall()

        for student_info in all_sections_all_students_info:
            section_number=student_info.get('section_number')
            xid=student_info.get('xid')
            students_by_section_by_xid[section_number][xid]=student_info

        # create a list of xids sorted by last name then first name
        # this is redundant in Python 3.6
        for section_number in students_by_section_by_xid:
            alpha_sorted_xids=sorted(students_by_section_by_xid.get(section_number), key=lambda xid:(
                students_by_section_by_xid.get(section_number).get(xid).get('last_name'),
                students_by_section_by_xid.get(section_number).get(xid).get('first_name'))
            )
            students_by_section_by_xid[section_number]['sorted_xids']=alpha_sorted_xids


        # gather grades for all students in course from all exams
        tuple_of_exams=self.get_exams(course_id,semester, year)
        for exam in tuple_of_exams:
            exam_key_version = self.get_exam_key_version(exam.get('exam_id'))

            exam["num_mc_questions"] = len(self.get_mc_key(exam.get('exam_id'), exam_key_version))
            exam["num_fr_questions"] = len(self.get_fr_questions(exam.get('exam_id'), exam_key_version))
        exam_ids=sorted([exam.get('exam_id') for exam in tuple_of_exams])
        exam_info_by_section_by_exam_id_by_xid=dict()
        for section_num in list_of_section_nums:
            exam_info_by_section_by_exam_id_by_xid[section_num]=dict.fromkeys(exam_ids)
            list_of_xids=students_by_section_by_xid[section_num]['sorted_xids']
            for exam_id in exam_ids:
                exam_info_by_section_by_exam_id_by_xid[section_num][exam_id]=dict.fromkeys(list_of_xids)

        for exam_id in exam_ids:
            cursor.execute("""
SELECT xid, reg_stat_code, section_num, last_name, first_name, absent, mc_version, mc_points, fr_version, fr_points, (IFNULL(mc_points, 0) + IFNULL(fr_points, 0)) AS total_points FROM
(SELECT
 cr.xid, reg_stat_code, cr.section_number AS section_num, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, version AS mc_version, points_earned AS mc_points, (SELECT COUNT(xid) FROM exam_absences AS ea WHERE ea.exam_id = '{0}' AND ea.xid = cr.xid) AS absent, CONCAT_WS(",", (SELECT DISTINCT version FROM fr_scores AS fr WHERE fr.xid = cr.xid AND fr.exam_id = '{0}')) AS fr_version, (SELECT SUM(fr.points_earned) FROM fr_scores AS fr WHERE fr.xid = cr.xid AND fr.exam_id = '{0}') AS fr_points, 0 AS total_points
FROM
 rolls.full_rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
LEFT JOIN
 mc_responses AS r ON r.xid = cr.xid AND exam_id = '{0}'
WHERE
 term_code = {1} AND
 (subject_code = '{2}' OR subject_code = '{3}') AND
 course_number = '{4}') AS temp
ORDER BY section_num, last_name, first_name, xid""".format(exam_id, term_code, course_info.get('prefix'), course_info.get('alt_prefix'), course_info.get('course_num')))
            if cursor.rowcount>0:
                exam_info=cursor.fetchall()
                for exam in exam_info:
                    section_num=exam.get('section_num')
                    xid=exam.get('xid')
                    exam_info_by_section_by_exam_id_by_xid[section_num][exam_id][xid]=exam

        csv_file_contents=[]
        for section in sections:
            offer_id = section.get('offer_id')
            section_num=section.get('section_num')
            section_students=students_by_section_by_xid.get(section_num)
            alpha_list_of_student_xids=section_students.get('sorted_xids')
            section_exam_info=exam_info_by_section_by_exam_id_by_xid.get(section_num)

            if len(section_exam_info) > 0:
                # so we only get the header once
                csv_header_1 = ["Section","Last name","First name","XID","Status"]
                csv_header_2 = ["","","","",""]

                offer_info = self.get_offer_info(offer_id)

                exam_header_1 = ""
                exam_header_2 = ""

                for exam in tuple_of_exams:
                    # rowspan=0 should work, but Chrome doesn't like it
                    exam_header_1 += f"""
        <td class="term_summary_spacer" rowspan="{len(alpha_list_of_student_xids)+2:d}"></td>
        <td colspan="3">{exam.get('title')}</td>
"""
                    exam_header_2 += """
        <td>MC</td>
        <td>FR</td>
        <td>Overall</td>
"""

                    csv_header_1.extend([exam.get('title'),"",""])
                    csv_header_2.extend(["MC","FR","Overall"])

                section_html = f"""
<table class="outline view_term_summary_tbl">
    <tr>
        <td rowspan="2">Last Name</td>
        <td rowspan="2">First Name</td>
        <td rowspan="2">XID</td>
{exam_header_1}
    </tr>
    <tr>
{exam_header_2}
    </tr>
"""

                # for entry in scores:
                for xid in alpha_list_of_student_xids:
                    student_info = section_students.get(xid)
                    student = self.nones_to_blanks(student_info)
                    reg_code = exam_info_by_section_by_exam_id_by_xid.get(section_num).get(exam_id).get(xid).get('reg_stat_code')

                    exam_html = ""
                    cur_scores_csv_data = ""

                    for exam in tuple_of_exams:
                        exam_id=exam.get('exam_id')
                        score_data=exam_info_by_section_by_exam_id_by_xid.get(section_num).get(exam_id).get(xid)

                        mc_class = ""
                        fr_class = ""
                        total_class = ""

                        if score_data.get('absent'):
                            absent_class = " class=\"absent\""
                            mc_class = absent_class
                            fr_class = absent_class
                            total_class = absent_class
                        else:
                            missing_data_class = " class=\"missing_data\""
                            withdraw_class = " class=\"withdrawn\""
                            if exam.get('num_mc_questions') > 0 and score_data.get('mc_points') is None:
                                mc_class = missing_data_class

                            if exam.get('num_fr_questions') > 0 and score_data.get('fr_points') is None:
                                fr_class = missing_data_class

                            if len(mc_class) > 0 or len(fr_class) > 0:
                                total_class = missing_data_class

                            if score_data.get('mc_points') is None and score_data.get('fr_points') is None and reg_code in ['AU', 'AW', 'DD', 'DE', 'DN', 'DW', 'WD', 'WL', 'WW']:
                                mc_class = withdraw_class
                                fr_class = withdraw_class
                                total_class = withdraw_class

                        # wait to do this here so we can assign classes
                        score_data = self.nones_to_blanks(score_data)

                        exam_html += f"""
        <td{mc_class}>{score_data.get('mc_points')}</td>
        <td{fr_class}>{score_data.get('fr_points')}</td>
        <td{total_class}>{score_data.get('total_points')}</td>
        """

                        cur_scores_csv_data += f"{score_data.get('mc_points')},{score_data.get('fr_points')},{score_data.get('total_points')},"

                    section_html += f"""
    <tr>
        <td>{student.get('last_name')}</td>
        <td>{student.get('first_name')}</td>
        <td>{student.get('xid')}</td>
{exam_html}
    </tr>
"""

                    if reg_code in ['AU', 'AW', 'DD', 'DE', 'DN', 'DW', 'WD', 'WL', 'WW']:
                        status = "Withdrawn"
                    else:
                        status = ""

                    new_line=[section.get('section_num'),student.get('last_name'),student.get('first_name'),student.get('xid'),status]
                    new_line.extend(cur_scores_csv_data.split(','))
                    csv_data.append(new_line)

                section_html += "</table>"

                html += f"""
<tr>
    <td>
        <div class="section">{section.get('prefix')} {section.get('course_num')}-{section.get('section_num')}</div>
        <div>{"</div><div>".join(self.get_instructors(section.get('offer_id')))}</div>
    </td>
    <td>
{section_html}
    </td>
</tr>
"""
                csv_file_contents=[csv_header_1, csv_header_2]
                csv_file_contents.extend(csv_data)

            else:
                html = "No data"
                csv_file_contents=""

        # Save CSV file so it is available for download
        with open(local_filename, 'w', newline='') as csvfile:
            filewriter = csv.writer(csvfile, dialect='excel', delimiter=',')
            for line in csv_file_contents:
                filewriter.writerow(line)
        return html

    #=============================================
    # accepts: str semester,
    #          str year,
    #          str local_filename
    # returns: html to view/generate coord end of term summary w/ dropped students
    #          generates csv admin terms summary
    def get_html_to_print_admin_term_summary(self, semester, year, local_filename):
        sections = self.get_sections_for_term(semester, year)
        download_filename=f"""{semester.capitalize()}_{year}_term_summary.csv"""

        html = f"""
<div style="margin-top: 30px;">The following is a summary of all the grades submitted during the term. Items highlighted in orange are missing, while items highlighted in blue mean the student was marked as absent for that item.</div>
<div class="title">{semester.capitalize()} {year} Term Summary</div>
<div class="subtitle">All Sections</div>
<div style="margin-top: 20px;">
    <input class="btn_link" type="submit" onclick="get_end_of_term_csv('{download_filename}')" value="Download Data as CSV"></input>
</div>
<table class="coord_term_table">
"""

        csv_header_1 = ""
        csv_header_2 = ""
        csv_data = []
        csv_file_contents=[]
        #TODO: Delete following line
        # first_offer_info = self.get_offer_info(sections[0].get('offer_id'))
        current_course = ""

        for section in sections:
            offer_id = section.get('offer_id')
            data = self.get_term_summary(offer_id)

            if len(data) > 0:
                offer_info = self.get_offer_info(offer_id)
                prefix=offer_info.get('prefix')
                course_num=offer_info.get('course_num')
                this_course = f"""{prefix}-{course_num}"""

                instructors_list=self.get_instructors(offer_id)
                instructors_string_csv = " / ".join(instructors_list)

                # so we only get the header once per course
                if this_course != current_course:
                    csv_header_1 = ["Subject","Course","Section","Instructor","Last name","First name","XID"]
                    csv_header_2 = ["","","","","","",""]

                exams = data.get('exams')
                scores = data.get('scores')

                exam_header_1 = ""
                exam_header_2 = ""

                for exam in exams:
                    # rowspan=0 should work, but Chrome doesn't like it
                    exam_header_1 += f"""
        <td class="term_summary_spacer" rowspan="{len(scores)+2:d}"></td>
        <td colspan="3">{exam.get('title')}</td>
"""
                    exam_header_2 += """
        <td>MC</td>
        <td>FR</td>
        <td>Overall</td>
"""
                    if this_course != current_course:
                        csv_header_1.extend([exam.get('title'),"",""])
                        csv_header_2.extend(["MC","FR","Overall"])

                section_html = f"""
<table class="outline view_term_summary_tbl">
    <tr>
        <td rowspan="2">Last Name</td>
        <td rowspan="2">First Name</td>
        <td rowspan="2">XID</td>
{exam_header_1}
    </tr>
    <tr>
{exam_header_2}
    </tr>
"""

                if this_course != current_course:
                    csv_data.append([])
                    csv_data.append(csv_header_1)
                    csv_data.append(csv_header_2)
                    current_course = this_course

                for entry in scores:
                    student = self.nones_to_blanks(entry.get('student'))
                    cur_exam_scores = entry.get('exam_scores')

                    exam_html = ""
                    cur_scores_csv_data = []

                    for exam in exams:
                        score_data = cur_exam_scores.get(exam.get('exam_id'))

                        mc_class = ""
                        fr_class = ""
                        total_class = ""

                        if score_data.get('absent'):
                            absent_class = " class=\"absent\""
                            mc_class = absent_class
                            fr_class = absent_class
                            total_class = absent_class
                        else:
                            missing_data_class = " class=\"missing_data\""
                            if exam.get('num_mc_questions') > 0 and score_data.get('mc_points') is None:
                                mc_class = missing_data_class

                            if exam.get('num_fr_questions') > 0 and score_data.get('fr_points') is None:
                                fr_class = missing_data_class

                            if len(mc_class) > 0 or len(fr_class) > 0:
                                total_class = missing_data_class

                            # wait to do this here so we can assign classes
                            score_data = self.nones_to_blanks(score_data)

                            mc_points=self.pretty_print_number(score_data.get('mc_points'))
                            fr_points=self.pretty_print_number(score_data.get('fr_points'))
                            total_points=self.pretty_print_number(score_data.get('total_points'))

                            exam_html += f"""
        <td{mc_class}>{mc_points}</td>
        <td{fr_class}>{fr_points}</td>
        <td{total_class}>{total_points}</td>
        """

                            cur_scores_csv_data.extend([mc_points,fr_points,total_points])

                    section_html += f"""
    <tr>
        <td>{student.get('last_name')}</td>
        <td>{student.get('first_name')}</td>
        <td>{student.get('xid')}</td>
{exam_html}
    </tr>
"""

                    new_line=[offer_info.get('prefix'), offer_info.get('course_num'), section.get('section_num'), instructors_string_csv, student.get('last_name'), student.get('first_name'), student.get('xid')]
                    new_line.extend(cur_scores_csv_data)
                    csv_data.append(new_line)

                section_html += "</table>"

                prefix=section.get('prefix')
                course_num=section.get('course_num')
                section_num=section.get('section_num')
                offer_id=section.get('offer_id')
                instructors_string_html="</div><div>".join(instructors_list)

                html += f"""
<tr>
    <td style="white-space:nowrap;">
        <div class="section">{prefix} {course_num}-{section_num}</div>
        <div>{instructors_string_html}</div>
    </td>
    <td>
{section_html}
    </td
</tr>
"""
                # csv_file_contents=[csv_header_1, csv_header_2]
                # csv_file_contents=[]
                csv_file_contents.extend(csv_data)
            else:
                html = "No data"

        html += "</table>"

        # Save CSV file so it is available for download
        with open(local_filename, 'w', newline="") as csvfile:
            filewriter = csv.writer(csvfile, dialect='excel', delimiter=',')
            for line in csv_file_contents:
                filewriter.writerow(line)
        return html

    #=============================================
    # accepts: list report,
    #          int exam_id,
    #          str course_id
    # returns: html to view/generate course report
    #
    def get_html_to_print_course_report(self, report_type, exam_id, course_id):
        if report_type == 'MC':
            report = self.get_mc_course_report(exam_id)
        elif report_type == 'FR':
            report = self.get_fr_course_report(exam_id)
        elif report_type == 'Overall':
            stats = self.get_overall_course_stats(exam_id)
            report = self.get_overall_course_report(exam_id)
            freq_table_html = ""

            if stats.get('num_test_takers') > 0:
                for i in range(0, len(stats.get('freq_table'))):
                    percentage = float(stats.get('freq_table')[i]) / stats.get('num_test_takers')
                    freq_table_html += f"""
        <tr>
            <td style="text-align: right;">{10*i:.0f}%</td>
            <td style="vertical-align: middle; height: 30px;"><div style="background: #522D80; border: solid 1px #000000; height: 20px; width: {int(percentage*1000)}px;"></div></td>
            <td style="text-align: center;">{percentage*100:.1f}%</td>
        </tr>
"""

        course_info = self.get_course_info(course_id)
        exam_info = self.get_exam(exam_id)
        html = f"""<div class="title">{exam_info.get('title')} {report_type}</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
<div style="margin-top: 20px;">The normalized scores are green (above) or red (below) if the difference in average from other sections is significant at the 95% level. The normalized scores have not been adjusted for student skill level and often exhibit considerable variation over different exams by section.</div>
"""

        report_html = ""

        for section in report:
            section_num = f"{course_info.get('prefix')} {course_info.get('course_num')}-{section.get('section_num')}"

            color_flag = ""

            if section.get('normalized_score') > float(self.get_setting("normalized_score_max")):
                color_flag = """ style="color: #00AA00; font-weight: bold;" """
            elif section.get('normalized_score') < float(self.get_setting("normalized_score_min")):
                color_flag = """ style="color: #FF0000; font-weight: bold;" """

            if report_type == 'MC':
                report_html += f"""
    <tr>
        <td>{section_num}</td>
        <td>{section.get('instructors')}</td>
        <td>{section.get('num_students'):.0f}</td>
        <td>{section.get('avg_mc_percent'):.2f}%</td>
        <td>{section.get('mc_score_avg'):.2f}</td>
        <td>{section.get('mc_score_std'):.2f}</td>
        <td{color_flag}>{section.get('normalized_score'):.2f}</td>
    </tr>
"""
            elif report_type == 'FR':
                report_html += f"""
    <tr>
        <td>{section_num}</td>
        <td>{section.get('instructors')}</td>
        <td>{section.get('num_students'):.0f}</td>
        <td>{section.get('avg_fr_percent'):.2f}%</td>
        <td>{section.get('fr_score_avg'):.2f}</td>
        <td>{section.get('fr_score_std'):.2f}</td>
        <td{color_flag}>{section.get('normalized_score'):.2f}</td>
    </tr>
"""
            elif report_type == 'Overall':
                report_html += f"""
    <tr>
        <td>{section_num}</td>
        <td>{section.get('instructors')}</td>
        <td>{section.get('num_students'):.0f}</td>
        <td>{section.get('score_avg'):.2f}</td>
        <td>{section.get('score_std'):.2f}</td>
        <td{color_flag}>{section.get('normalized_score'):.2f}</td>
    </tr>
"""

        if len(report) > 0:
            if report_type == 'MC' or report_type == 'FR':
                html += f"""
<table class="course_report_table outline">
    <tr>
        <td>Section</td>
        <td>Instructor</td>
        <td>Number of<br>Test Takers</td>
        <td>Average<br>Percent</td>
        <td>Average<br>Score</td>
        <td>Standard<br>Deviation</td>
        <td>Normalized<br>Score</td>
    </tr>
{report_html}
</table>
"""
            else:
                html += f"""
<table class="course_report_table outline">
    <tr>
        <td>Section</td>
        <td>Instructor</td>
        <td>Number of<br>Test Takers</td>
        <td>Average</td>
        <td>Standard<br>Deviation</td>
        <td>Normalized<br>Score</td>
    </tr>
{report_html}
</table>
    <div style="font-weight: bold; font-size: 24px; margin-top: 20px;">Statistics</div>
    <table>
        <tr>
            <td>Number of test takers:</td>
            <td>{stats.get('num_test_takers')}</td>
        </tr>
        <tr>
            <td>Number absent:</td>
            <td>{stats.get('num_absent')}</td>
        </tr>
        <tr>
            <td>Number missing:</td>
            <td>{stats.get('num_missing')}</td>
        </tr>
        <tr>
            <td>Total points:</td>
            <td>{stats.get('total_points')}</td>
        </tr>
        <tr>
            <td>Average Score:</td>
            <td>{stats.get('avg_score'):.1f} ({(stats.get('avg_score')/stats.get('total_points') * 100):.1f}%)</td>
        </tr>
        <tr>
            <td>Standard Deviation:</td>
            <td>{stats.get('std_scores'):.1f} ({(stats.get('std_scores')/stats.get('total_points') * 100):.1f}%)</td>
        </tr>
        <tr>
            <td>Coefficient of Variation:</td>
            <td>{(stats.get('coef_of_variation') * 100):.1f}%</td>
        </tr>
        <tr>
            <td>Median:</td>
            <td>{stats.get('median_score'):.1f} ({(stats.get('median_score') / stats.get('total_points') * 100):.1f}%)</td>
        </tr>
    </table>
    <div style="margin-top: 20px; font-weight: bold; font-size: 24px;">Frequency Table</div>
    <table>
{freq_table_html}
    </table>
"""
        else:
            data_desc = 'MC or FR'
            if report_type == 'MC':
                data_desc='MC'
            elif report_type == 'FR':
                data_desc='scantron'
            html += f"There is no report data yet. It will be available immediately once { data_desc } data is uploaded."
        return html

    #=============================================
    # accepts: str exam_id
    # returns: html to view/generate coord reports for viewing and download
    def get_html_for_overall_course_grades(self, exam_id):
        exam_info = self.get_exam(exam_id)
        course_info = self.get_course_info(exam_info.get('course_id'))

        semester=exam_info.get('semester').replace(' ','_')
        year=exam_info.get('year')
        prefix=course_info.get('prefix').replace(' ','_')
        course_num=course_info.get('course_num').replace(' ','_')
        title=exam_info.get('title').replace(' ','_')
        filename=f"{semester}_{year}_{prefix}_{course_num}_{title}_grades.csv"

        sections = self.get_course_sections(exam_info.get('course_id'), exam_info.get('semester'), exam_info.get('year'))

        html = f"""
<div class="title">{exam_info.get('title')}</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
<div style="margin-top: 20px;">
    <input class="btn_link" type="submit" onclick="get_report_csv('{filename}')" value="Download Data as CSV"></input>
</div>
<table class="coord_grades_table">"""

        csv_file_contents=[['Section Number',\
                           'XID',\
                           'Last Name',\
                           'First Name',\
                           'MC Version',\
                           'MC Points',\
                           'FR Version',\
                           'FR Points',\
                           'Total Points']]

        for section in sections:
            section_scores_html = """
            <table class="faint_outline overall_table">
                <tr>
                    <td>XID</td>
                    <td>Last Name</td>
                    <td>First Name</td>
                    <td>MC version</td>
                    <td>MC points</td>
                    <td>FR version</td>
                    <td>FR points</td>
                    <td>Total points</td>
                </tr>
            """

            scores = self.get_overall_scores(section.get('offer_id'), exam_id)

            for score in scores:
                score = self.nones_to_blanks(score)
                csv_file_contents.append([section.get('section_num'),\
                                          score.get('xid'),\
                                          score.get('last_name'),\
                                          score.get('first_name'),\
                                          score.get('mc_version'),\
                                          self.pretty_print_number(score.get('mc_points')),\
                                          score.get('fr_version'),\
                                          self.pretty_print_number(score.get('fr_points')),\
                                          self.pretty_print_number(score.get('total_points'))])

                section_scores_html += f"""
                <tr>
                    <td>{score.get('xid')}</td>
                    <td>{score.get('last_name')}</td>
                    <td>{score.get('first_name')}</td>
                    <td>{score.get('mc_version')}</td>
                    <td>{self.pretty_print_number(score.get('mc_points'))}</td>
                    <td>{score.get('fr_version')}</td>
                    <td>{self.pretty_print_number(score.get('fr_points'))}</td>
                    <td>{self.pretty_print_number(score.get('total_points'))}</td>
                </tr>
"""

            section_scores_html += "</table>"
            html += f"""
            <tr>
                <td>
                    <div class="section">{section.get('prefix')} {section.get('course_num')}-{section.get('section_num')}</div>
                    <div>{"</div><div>".join(self.get_instructors(section.get('offer_id')))}</div>
                </td>
                <td>{section_scores_html}</td>
            </tr>"""
        html += "</table>"

        return html, csv_file_contents

    #=============================================
    # accepts: str exam_id
    # returns: html to view/generate coord reports for viewing and download
    def get_html_for_overall_course_raw_data(self, exam_id):
        exam_info=self.get_exam(exam_id)
        course_info = self.get_course_info(exam_info.get('course_id'))

        exam_info = self.get_exam(exam_id)
        course_info = self.get_course_info(exam_info.get('course_id'))
        semester=exam_info.get('semester').replace(' ','_')
        year=exam_info.get('year')
        prefix=course_info.get('prefix').replace(' ','_')
        course_num=course_info.get('course_num').replace(' ','_')
        title=exam_info.get('title').replace(' ','_')
        filename=f"{semester}_{year}_{prefix}_{course_num}_{title}_raw_data.csv"

        sections = self.get_course_sections(exam_info.get('course_id'), exam_info.get('semester'), exam_info.get('year'))

        html = f"""
<div class="title">{exam_info.get('title')}</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
<div style="margin-top: 20px;">
    <input class="btn_link" type="submit" onclick="get_report_csv('{filename}')" value="Download Data as CSV"></input>
</div>
<table class="coord_grades_table">"""

        key_version = self.get_exam_key_version(exam_id)
        fr_questions = self.get_fr_questions(exam_id, key_version)
        num_of_fr_questions = len(fr_questions)

        fr_question_num_html = "</td>\n\t<td>".join([question.get('question_num') for question in fr_questions])
        fr_question_num_csv = [question.get('question_num') for question in fr_questions]

        if len(fr_question_num_html) > 0:
            fr_question_num_html = f"<td>{fr_question_num_html}</td>"

        csv_file_header=["Section Number","XID","Last Name","First Name","MC Version","MC responses","Number Right","Number Wrong","Number Blank","Number Mismarked","Graded","MC points","FR version"]
        csv_file_header.extend(fr_question_num_csv)
        csv_file_header.append('FR points')
        csv_file_contents=[csv_file_header]
        for section in sections:
            section_raw_data_html = f"""
            <table class="faint_outline overall_table">
                <tr>
                    <td>XID</td>
                    <td>Last Name</td>
                    <td>First Name</td>
                    <td>MC version</td>
                    <td>MC responses</td>
                    <td>Number<br>Right</td>
                    <td>Number<br>Wrong</td>
                    <td>Number<br>Blank</td>
                    <td>Number<br>Mismarked</td>
                    <td>Graded</td>
                    <td>MC points</td>
                    <td>FR version</td>
{fr_question_num_html}
                    <td>FR points</td>
                    <!--<td>Total points</td>-->
                </tr>
            """

            # we assume that there should be the same set of students in the same order for each of these functions
            mc_data = self.get_mc_responses(section.get('offer_id'), exam_id)
            fr_data = self.get_fr_scores(section.get('offer_id'), exam_id)

            for i in range(0, len(mc_data)):
                cur_mc = self.nones_to_blanks(mc_data[i])
                cur_fr = self.nones_to_blanks(fr_data[i])

                fr_scores_html = "</td>\n\t<td>".join([self.pretty_print_number(temp) for temp in cur_fr.get('scores')])

                if len(fr_scores_html) > 0:
                    fr_scores_html = f"\t<td>{fr_scores_html}</td>"
                else:
                    fr_scores_html = "\t<td></td>\n" * num_of_fr_questions

                section_raw_data_html += f"""
                        <tr>
                            <td>{cur_mc.get('xid')}</td>
                            <td>{cur_mc.get('last_name')}</td>
                            <td>{cur_mc.get('first_name')}</td>
                            <td>{cur_mc.get('version')}</td>
                            <td>{cur_mc.get('responses')}</td>
                            <td>{cur_mc.get('num_right')}</td>
                            <td>{cur_mc.get('num_wrong')}</td>
                            <td>{cur_mc.get('num_blank')}</td>
                            <td>{cur_mc.get('num_mismarked')}</td>
                            <td>{cur_mc.get('graded')}</td>
                            <td>{self.pretty_print_number(cur_mc.get('points_earned'))}</td>
                            <td>{cur_fr.get('version')}</td>
{fr_scores_html}
                            <td>{self.pretty_print_number(cur_fr.get('points_earned'))}</td>
                        </tr>
                    """

                fr_scores_csv = [self.pretty_print_number(temp) for temp in cur_fr.get('scores')]

                if len(fr_scores_csv) == 0:
                    fr_scores_csv = [None] * num_of_fr_questions
                new_line=[section.get('section_num'),\
                                        cur_mc.get('xid'),\
                                        cur_mc.get('last_name'),\
                                        cur_mc.get('first_name'),\
                                        cur_mc.get('version'),\
                                        cur_mc.get('responses'),\
                                        cur_mc.get('num_right'),\
                                        cur_mc.get('num_wrong'),\
                                        cur_mc.get('num_blank'),\
                                        cur_mc.get('num_mismarked'),\
                                        cur_mc.get('graded'),\
                                        self.pretty_print_number(cur_mc.get('points_earned')),\
                                        cur_fr.get('version')]
                new_line.extend(fr_scores_csv)
                new_line.append(self.pretty_print_number(cur_fr.get('points_earned')))
                csv_file_contents.append(new_line)

            section_raw_data_html += "</table>"

            html += f"""
            <tr>
                <td>
                    <div class="section">{section.get('prefix')} {section.get('course_num')}-{section.get('section_num')}</div>
                    <div>{"</div><div>".join(self.get_instructors(section.get('offer_id')))}</div>
                </td>
                <td>{section_raw_data_html}</td>
            </tr>"""
        html += "</table>"

        return html, csv_file_contents

    #=============================================
    # accepts: str semester,
    #          str year,
    #          str course_id
    #          str local_filename
    # returns: html to view/generate end of term data, generates end of term csv file
    def get_html_to_print_view_data(self,semester, year, course_id, local_filename):
        #Set default message
        section_html=""
        csv_file_contents=""
        course_info = self.get_course_info(course_id)

        prefix=course_info.get('prefix')
        course_num=course_info.get('course_num')
        filename=f"{semester.capitalize()}_{year}_{prefix}_{course_num}_term_end_data.csv"

        sections = self.get_course_sections(course_id, semester, year)

        html = f"""
<div style="margin-top: 30px;">If a student is ineligible to pass the class due to a low test/final exam average the note will say "Ineligible to pass the class". If the submitted letter grade differs from the calculated letter grade or university letter grade they will both be highlighted in red.</div>
<div class="title">{semester.capitalize()} {year} End of Term Data</div>
<div class="subtitle">{course_info.get('prefix')} {course_info.get('course_num')}</div>
<div style="margin-top: 20px;">
    <input class="btn_link" type="submit" onclick="get_end_of_term_csv('{filename}')" value="Download Data as CSV"></input>
</div>
<table class="coord_term_table">
"""

        csv_header = ""
        csv_data = []

        for section in sections:
            # so we only get the header once
            csv_header = ["Section","Last name","First name", "XID","Number of Absences","Weighted Test Average","Calculated Course Average","Submitted Letter Grade","Calculated Letter Grade","University Letter Grade","Note"]

            offer_id = section.get('offer_id')

            term_data = self.get_term_end_data(offer_id)
            summary_data = self.get_term_summary_full(offer_id)

            course_exams = summary_data.get('exams')
            scores_data = summary_data.get('scores')

            if len(term_data) > 0:
                offer_info = self.get_offer_info(offer_id)
                offer_id = offer_info.get('course_id')

                section_description = f"{offer_info.get('prefix')} {offer_info.get('course_num')}-{offer_info.get('section_num')}"

                section_html = """
<table class="outline view_term_end_tbl">
    <tr>
        <td>Last Name</td>
        <td>First Name</td>
        <td>XID</td>
        <td>Number of<br>Absences</td>
        <td>Weighted<br>Test Average</td>
        <td>Calculated<br>Course Average</td>
        <td>Submitted<br>Letter Grade</td>
        <td>Calculated<br>Letter Grade</td>
        <td>University<br>Letter Grade</td>
        <td>Note</td>
    </tr>
"""
                for i in range(0, len(term_data)):
                    student = self.nones_to_blanks(term_data[i])
                    # these should be in the same order
                    scores = scores_data[i].get('exam_scores')

                    (calculated_letter_grade, weighted_test_average, grade, min_test_grade, note) = self.get_calculated_letter_grade(course_id, course_exams, scores)


                    univ_letter_grade = student.get('univ_letter_grade')
                    letter_grade = student.get('letter_grade')

                    error_class = " class=\"error\""

                    letter_class = ""
                    calculated_class = ""
                    univ_class = ""

                    if letter_grade != "":
                        if univ_letter_grade != "" and univ_letter_grade != letter_grade:
                            letter_class = error_class
                            univ_class = error_class

                        if calculated_letter_grade != letter_grade:
                            letter_class = error_class
                            calculated_class = error_class
                    elif univ_letter_grade != "" and univ_letter_grade != calculated_letter_grade:
                        univ_class = error_class
                        calculated_class = error_class

                    section_html += f"""
            <tr>
                <td>{student.get('last_name')}</td>
                <td>{student.get('first_name')}</td>
                <td>{student.get('xid')}</td>
                <td>{student.get('num_absences')}</td>
                <td>{weighted_test_average:.2f}%</td>
                <td>{grade:.2f}%</td>
                <td{letter_class}>{letter_grade}</td>
                <td{calculated_class}>{calculated_letter_grade}</td>
                <td{univ_class}>{univ_letter_grade}</td>
                <td>{note}</td>
            </tr>"""

                    new_line=[section.get('section_num'),
                              student.get('last_name'),
                              student.get('first_name'),
                              student.get('xid'),
                              student.get('num_absences'),
                              weighted_test_average,
                              grade,
                              letter_grade,
                              calculated_letter_grade,
                              univ_letter_grade,
                              note]

                    csv_data.append(new_line)

                section_html += "</table>"

                csv_file_contents=[csv_header]
                csv_file_contents.extend(csv_data)
            else:
                #TODO: display this some other way?
                html = "No data"

            if len(section_html)>0:
                html += f"""
    <tr>
        <td>
            <div class="section">{section.get('prefix')} {section.get('course_num')}-{section.get('section_num')}</div>
            <div>{"</div><div>".join(self.get_instructors(section.get('offer_id')))}</div>
        </td>
        <td>
{section_html}
        </td>
    </tr>"""

                html += "</table>"
        # Save CSV file so it is available for download
        with open(local_filename, 'w', newline='') as csvfile:
            filewriter = csv.writer(csvfile, dialect='excel', delimiter=',')
            for line in csv_file_contents:
                filewriter.writerow(line)
        return html

    #=============================================
    # accepts: str semester,
    #          str year,
    #          str local_filename
    # returns: html to view/generate end of term data, generates end of term csv file
    def get_html_to_print_admin_view_data(self,semester, year, local_filename):
        csv_file_contents=""
        section_html=""

        sections = self.get_sections_for_term(semester, year)
        download_filename=f"""{semester.capitalize()}_{year}_term_end_data.csv"""

        html = f"""
<div style="margin-top: 30px;">If a student is ineligible to pass the class due to a low test/final exam average the note will say "Ineligible to pass the class". If the submitted letter grade differs from the calculated letter grade or university letter grade they will both be highlighted in red.</div>
<div class="title">{semester.capitalize()} {year} End of Term Data</div>
<div class="subtitle">All Sections</div>
<div style="margin-top: 20px;">
    <input class="btn_link" type="submit" onclick="get_end_of_term_csv('{download_filename}')" value="Download Data as CSV"></input>
</div>
<table class="coord_term_table">
"""

        # so we only get the header once
        csv_header = ["Subject","Course","Section","Instructor","Last name","First name"," XID","Number of Absences","Weighted Test Average","Calculated Course Average","Submitted Letter Grade","Calculated Letter Grade","University Letter Grade","Note"]
        csv_data = []

        for section in sections:
            offer_id     = section.get('offer_id')
            term_data    = self.get_term_end_data(offer_id)
            summary_data = self.get_term_summary(offer_id)
            course_exams = summary_data.get('exams')
            scores_data  = summary_data.get('scores')

            instructor_list        = self.get_instructors(offer_id)
            instructor_string_csv  = " / ".join(instructor_list)
            instructor_string_html = "</div><div>".join(instructor_list)

            if len(term_data) > 0:
                offer_info = self.get_offer_info(offer_id)
                section_description = f"""{offer_info.get('prefix')} {offer_info.get('course_num')}-{offer_info.get('section_num')}"""

                section_html = """
<table class="outline view_term_end_tbl">
    <tr>
        <td>Last Name</td>
        <td>First Name</td>
        <td>XID</td>
        <td>Number of<br>Absences</td>
        <td>Weighted<br>Test Average</td>
        <td>Calculated<br>Course Average</td>
        <td>Submitted<br>Letter Grade</td>
        <td>Calculated<br>Letter Grade</td>
        <td>University<br>Letter Grade</td>
        <td>Note</td>
    </tr>
"""

                for i in range(0, len(term_data)):
                    student = self.nones_to_blanks(term_data[i])
                    # these should be in the same order
                    scores = scores_data[i].get('exam_scores')

                    (calculated_letter_grade, weighted_test_average, grade, min_test_grade, note) = self.get_calculated_letter_grade(offer_info.get('course_id'), course_exams, scores)

                    univ_letter_grade = student.get('univ_letter_grade')
                    letter_grade = student.get('letter_grade')

                    error_class = " class=\"error\""

                    letter_class = ""
                    calculated_class = ""
                    univ_class = ""

                    if letter_grade != "":
                        if univ_letter_grade != "" and univ_letter_grade != letter_grade:
                            letter_class = error_class
                            univ_class = error_class

                        if calculated_letter_grade != letter_grade:
                            letter_class = error_class
                            calculated_class = error_class
                    elif univ_letter_grade != "" and univ_letter_grade != calculated_letter_grade:
                        univ_class = error_class
                        calculated_class = error_class

                    section_html += f"""
            <tr>
                <td>{student.get('last_name')}</td>
                <td>{student.get('first_name')}</td>
                <td>{student.get('xid')}</td>
                <td>{student.get('num_absences')}</td>
                <td>{self.pretty_print_number(weighted_test_average)}%</td>
                <td>{self.pretty_print_number(grade)}%</td>
                <td{letter_class}>{letter_grade}</td>
                <td{calculated_class}>{calculated_letter_grade}</td>
                <td{univ_class}>{univ_letter_grade}</td>
                <td>{note}</td>
            </tr>"""

                    new_line=[offer_info.get('prefix'),
                              offer_info.get('course_num'),
                              section.get('section_num'),
                              instructor_string_csv,
                              student.get('last_name'),
                              student.get('first_name'),
                              student.get('xid'),
                              student.get('num_absences'),
                              self.pretty_print_number(weighted_test_average),
                              self.pretty_print_number(grade),
                              letter_grade,
                              calculated_letter_grade,
                              univ_letter_grade,
                              note]
                    csv_data.append(new_line)

                section_html += "</table>"
            else:
                #TODO: display this some other way?
                html = "No data"

            html += f"""
    <tr>
        <td>
            <div class="section">{section.get('prefix')} {section.get('course_num')}-{section.get('section_num')}</div>
            <div>{instructor_string_html}</div>
        </td>
        <td>
{section_html}
        </td>
    </tr>"""

        csv_file_contents=[csv_header]
        csv_file_contents.extend(csv_data)
        html += "</table>"

        # Save CSV file so it is available for download
        with open(local_filename, 'w', newline='') as csvfile:
            filewriter = csv.writer(csvfile, dialect='excel', delimiter=',')
            for line in csv_file_contents:
                filewriter.writerow(line)
        return html


    #=============================================
    # accepts: str response
    # returns: html to submit key for exam
    #
    def get_html_to_print_course_roll(self, offer_id, offer_info):
        students = self.get_roll(offer_id)
        content = str(len(students))
        if len(students) > 0:
            section_description = f"{offer_info.get('prefix')} {offer_info.get('course_num')}-{offer_info.get('section_num')}"

            student_count_str = ""

            if len(students) != 1:
                student_count_str = f"({len(students)} students)"
            else:
                student_count_str = "(1 student)"

            content = f"""
<div class="title">{section_description}</div>
<div class="subtitle">{student_count_str}</div>
<table class="outline rolls_tbl">
    <tr>
        <td>Last Name</td>
        <td>First Name</td>
        <td>Username</td>
        <td>XID</td>
    </tr>
"""

            for student in students:
                content += f"""
    <tr>
        <td>{student.get('last_name')}</td>
        <td>{student.get('first_name')}</td>
        <td>{student.get('username')}</td>
        <td>{student.get('xid')}</td>
    </tr>
"""

            content += """
</table>
"""
        return content

    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to view mc data
    #
    def get_html_to_view_mc(self, offer_id, offer_info, exam_id):
        responses = self.get_mc_responses(offer_id, exam_id)

        if len(responses) > 0:
            exam_info = self.get_exam(exam_id)
            section_description = f"{offer_info.get('prefix')} {offer_info.get('course_num')}-{offer_info.get('section_num')}"

            html = f"""
<div style="margin-top: 15px;">If a student was marked as absent for the test, their record will be highlighted.</div>
<div class="title">{exam_info.get('title')}</div>
<div class="subtitle">{section_description}</div>
<table class="outline mc_table">
    <tr>
        <td>Last Name</td>
        <td>First Name</td>
        <td>XID</td>
        <td>Version</td>
        <td>Responses</td>
        <td>Number<br>Right</td>
        <td>Number<br>Wrong</td>
        <td>Number<br>Blank</td>
        <td>Number<br>Mismarked</td>
        <td>Graded</td>
        <td>Points<br>Earned</td>
    </tr>
"""

            for response in responses:
                response = self.nones_to_blanks(response)

                absent_class = ""

                if response.get('absent') == 1:
                    absent_class = """ class="absent_row" """

                # code to highlight wrong answers
                # if we do this then we should add it to the header
                cur_mc_responses = response.get('responses')
                cur_marks = response.get('marks')

                mc_responses_display = ""

                for i in range(0, len(cur_mc_responses)):
                    if cur_marks[i] == "0":
                        mc_responses_display += f"""<span class="wrong_mc">{cur_mc_responses[i]}</span>"""
                    else:
                        mc_responses_display += cur_mc_responses[i]

                html += f"""
    <tr{absent_class}>
        <td>{response.get('last_name')}</td>
        <td>{response.get('first_name')}</td>
        <td>{response.get('xid')}</td>
        <td>{response.get('version')}</td>
        <td>{cur_mc_responses}</td>
        <td>{response.get('num_right')}</td>
        <td>{response.get('num_wrong')}</td>
        <td>{response.get('num_blank')}</td>
        <td>{response.get('num_mismarked')}</td>
        <td>{response.get('graded')}</td>
        <td>{response.get('points_earned')}</td>
    </tr>
"""
            html += "</table>"
        else:
            html = "No responses"
        return html


    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to view fr data
    #
    def get_html_to_view_fr(self, offer_id, offer_info, exam_id):
        fr_scores = self.get_fr_scores(offer_id, exam_id)

        if len(fr_scores) > 0:
            exam_info = self.get_exam(exam_id)
            section_description = f"{offer_info.get('prefix')} {offer_info.get('course_num')}-{offer_info.get('section_num')}"

            key_version = self.get_exam_key_version(exam_id)
            fr_questions = self.get_fr_questions(exam_id, key_version)
            num_of_fr_questions = len(fr_questions)

            fr_question_num_html = "</td>\n\t<td>".join([question["question_num"] for question in fr_questions])

            if len(fr_question_num_html) > 0:
                fr_question_num_html = f"<td>{fr_question_num_html}</td>"

            html = f"""
<div style="margin-top: 15px;">If a student was marked as absent for the test, their record will be highlighted. Also, the column headings are based on the order of the FR questions in the key version.</div>
<div class="title">{exam_info.get('title')}</div>
<div class="subtitle">{section_description}</div>
<table class="outline fr_table">
    <tr>
        <td>Last Name</td>
        <td>First Name</td>
        <td>XID</td>
        <td>Version</td>
{fr_question_num_html}
        <td>Points<br>Earned</td>
    </tr>
"""

            for score in fr_scores:
                #score["scores"] = gl.nones_to_blanks(score["scores"])

                scores_html = "</td>\n\t<td>".join([self.pretty_print_number(temp) for temp in score["scores"]])

                if len(scores_html) > 0:
                    scores_html = f"\t<td>{scores_html}</td>"
                else:
                    scores_html = "\t<td></td>\n" * num_of_fr_questions

                absent_class = ""

                if score.get('absent') == 1:
                    absent_class = """ class="absent_row" """

                html += f"""
    <tr{absent_class}>
        <td>{score.get('last_name')}</td>
        <td>{score.get('first_name')}</td>
        <td>{score.get('xid')}</td>
        <td>{score.get('version')}</td>
{scores_html}
        <td>{self.pretty_print_number(score.get('points_earned'))}</td>
    </tr>
"""

            html += "</table>"
        else:
            html = "No responses"
        return html

    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to view overall exam data
    #
    def get_html_to_view_overall(self, offer_id, offer_info, exam_id):
        scores = self.get_overall_scores(offer_id, exam_id)

        if len(scores) > 0:
            exam_info = self.get_exam(exam_id)
            section_description = f"{offer_info.get('prefix')} {offer_info.get('course_num')}-{offer_info.get('section_num')}"

            html = f"""
<div style="margin-top: 15px;">If a student was marked as absent for the test, their record will be highlighted in blue. Items colored light grey indicate the student was withdrawn.</div>
<div class="title">{exam_info.get('title')}</div>
<div class="subtitle">{section_description}</div>
<table class="outline overall_table">
    <tr>
        <td>Last Name</td>
        <td>First Name</td>
        <td>XID</td>
        <td>MC version</td>
        <td>MC points</td>
        <td>FR version</td>
        <td>FR points</td>
        <td>Total points</td>
    </tr>
"""

            for score in scores:
                score = self.nones_to_blanks(score)

                absent_class = ""
                if score.get('absent') == 1:
                    absent_class = """ class="absent_row" """
                else:
                    missing_data_class = " class=\"missing_data\""
                    withdraw_class = " class=\"withdrawn\""
                    if score.get('mc_points') == '' and score.get('fr_points') == '' and score.get('reg_stat_code') in ['AU', 'AW', 'DD', 'DE', 'DN', 'DW', 'WD', 'WL', 'WW']:
                        absent_class = withdraw_class

                html += f"""
    <tr{absent_class}>
        <td>{score.get('last_name')}</td>
        <td>{score.get('first_name')}</td>
        <td>{score.get('xid')}</td>
        <td>{score.get('mc_version')}</td>
        <td>{self.pretty_print_number(score.get('mc_points'))}</td>
        <td>{score.get('fr_version')}</td>
        <td>{self.pretty_print_number(score.get('fr_points'))}</td>
        <td>{self.pretty_print_number(score.get('total_points'))}</td>
    </tr>
"""

            html += "</table>"
        else:
            html = "No responses"
        return html

    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to mark exam absences
    #
    def get_html_to_mark_absences(self, offer_id, offer_info, exam_id):
        absence_data = self.get_exam_absences(offer_id, exam_id)

        exam_info = self.get_exam(exam_id)
        offer_info = self.get_offer_info(offer_id)
        section_description = f"{offer_info.get('prefix')} {offer_info.get('course_num')}-{offer_info.get('section_num')}"

        html = f"""
<div style="margin-top: 15px; max-width: 600px;">If a student was absent for the test, check the checkbox next to their name. You will not need to enter a grade for them and will not receive emails about their responses/scores being missing. Once you are done, click the Save Absences button at the top or bottom of the page.</div>
<div style="margin-top: 20px;"><button type="button" onclick="save_absences()">Save Absences</button></div>
<div style="max-width: 600px">
    <div class="title">Absences for {exam_info.get('title')}</div>
    <div class="subtitle">{section_description}</div>
    <table class="outline absences_table">
        <tr>
            <td>Last Name</td>
            <td>First Name</td>
            <td>XID</td>
            <td>Absent</td>
        </tr>
"""

        for student in absence_data:
            checked = ""
            if student.get('absent') == 1:
                checked = " checked"

            absence_checkbox = f"""<input type="checkbox" class="absence_checkbox" name="{student.get('xid')}_absent" value="{student.get('xid')}"{checked}>"""

            html += f"""
        <tr>
            <td>{student.get('last_name')}</td>
            <td>{student.get('first_name')}</td>
            <td>{student.get('xid')}</td>
            <td>{absence_checkbox}</td>
        </tr>
"""

        html += f"""
    </table>
    <div style="margin-top: 20px;">
        <button type="button" onclick="save_absences()">Save Absences</button>
        <input type="hidden" id="absence_offer_id" value="{offer_id}">
        <input type="hidden" id="absence_exam_id" value="{exam_id}">
    </div>
</div>
"""
        return html


    #=============================================
    # accepts: str offer_id
    # returns: html to view/generate coord end of term summary
    #
    def get_html_to_print_instructor_term_summary(self, offer_id, local_filename):
        semester = self.get_current_semester()
        year = self.get_current_year()

        data = self.get_term_summary_full(offer_id)

        csv_file_contents=[]
        csv_header_1 = ["Last name","First name","XID","Status"]
        csv_header_2 = ["","","",""]
        csv_data = []

        if len(data) > 0:
            offer_info = self.get_offer_info(offer_id)
            prefix=offer_info.get('prefix')
            course_num=offer_info.get('course_num')
            section_num=offer_info.get('section_num')
            section_description = f"{prefix} {course_num}-{section_num}"

            exams = data.get('exams')
            scores = data.get('scores')

            exam_header_1 = ""
            exam_header_2 = ""

            for exam in exams:
                # rowspan=0 should work, but Chrome doesn't like it
                exam_header_1 += f"""
        <td class="term_summary_spacer" rowspan="{len(scores) + 2}"></td>
        <td colspan="3">{ exam.get('title') }</td>
"""
                exam_header_2 += """
        <td>MC</td>
        <td>FR</td>
        <td>Overall</td>
"""

                csv_header_1.extend([exam.get('title'),"",""])
                csv_header_2.extend(["MC","FR","Overall"])

            semester=self.get_current_semester()
            year= self.get_current_year()
            # js_data={"form_name":"term_end", "offer_id": offer_id, "semester": semester, "year": year, "action": "term_summary"}
            download_filename=f"""{semester}_{year}_{section_description.replace(' ','_')}_term_summary.csv"""

            # data = {form_name: "term_end", course_id: $("#course_id").val(), semester: $("#semester").val(), year: $("#year").val(), action: $("#action").val()};
            html = f"""
<div style="margin-top: 30px;">The following is a summary of all the grades submitted during the term. Items highlighted in orange are missing, while items highlighted in blue mean the student was marked as absent for that item. Items colored light grey indicate the student was withdrawn.</div>
<div class="title">Term Summary</div>
<div class="subtitle">{section_description}</div>
<div style="margin-top: 20px; margin-bottom: 30px;">
    <input class="btn_link" type="submit" onclick="get_end_of_term_csv('{download_filename}','term_summary')" value="Download Data as CSV"></input>
</div>

<table class="outline view_term_summary_tbl">
    <tr>
        <td rowspan="2">Last Name</td>
        <td rowspan="2">First Name</td>
        <td rowspan="2">XID</td>
{exam_header_1}
    </tr>
    <tr>
{exam_header_2}
    </tr>
"""

            for entry in scores:
                student = self.nones_to_blanks(entry.get('student'))
                cur_exam_scores = entry.get('exam_scores')

                exam_html = ""
                cur_scores_csv_data = ""

                for exam in exams:
                    score_data = cur_exam_scores.get(exam.get('exam_id'))
                    reg_code = score_data.get('reg_stat_code')

                    mc_class = ""
                    fr_class = ""
                    total_class = ""

                    if score_data.get('absent'):
                        absent_class = " class=\"absent\""
                        mc_class = absent_class
                        fr_class = absent_class
                        total_class = absent_class

                    else:
                        missing_data_class = " class=\"missing_data\""
                        withdraw_class = " class=\"withdrawn\""
                        if exam.get('num_mc_questions') > 0 and score_data.get('mc_points') is None:
                            mc_class = missing_data_class

                        if exam.get('num_fr_questions') > 0 and score_data.get('fr_points') is None:
                            fr_class = missing_data_class

                        if len(mc_class) > 0 or len(fr_class) > 0:
                            total_class = missing_data_class


                        if score_data.get('mc_points') is None and score_data.get('fr_points') is None and reg_code in ['AU', 'AW', 'DD', 'DE', 'DN', 'DW', 'WD', 'WL', 'WW']:
                            mc_class = withdraw_class
                            fr_class = withdraw_class
                            total_class = withdraw_class

                    # wait to do this here so we can assign classes
                    score_data = self.nones_to_blanks(score_data)

                    exam_html += f"""
        <td{mc_class}>{score_data.get('mc_points')}</td>
        <td{fr_class}>{score_data.get('fr_points')}</td>
        <td{total_class}>{score_data.get('total_points')}</td>
        """

                    cur_scores_csv_data += f"{score_data.get('mc_points')},{score_data.get('fr_points')},{score_data.get('total_points')},"


                html += f"""
    <tr>
        <td>{student.get('last_name')}</td>
        <td>{student.get('first_name')}</td>
        <td>{student.get('xid')}</td>
{exam_html}
    </tr>
"""
                if reg_code in ['AU', 'AW', 'DD', 'DE', 'DN', 'DW', 'WD', 'WL', 'WW']:
                    status = "Withdrawn"
                else:
                    status = ""

                new_line=[student.get('last_name'),student.get('first_name'),student.get('xid'),status]
                new_line.extend(cur_scores_csv_data.split(','))
                csv_data.append(new_line)

            html += "</table>"
            csv_file_contents=[csv_header_1, csv_header_2]
            csv_file_contents.extend(csv_data)
        else:
            html = "No data"
            csv_file_contents=""

        # Save CSV file so it is available for download
        with open(local_filename, 'w', newline='') as csvfile:
            filewriter = csv.writer(csvfile, dialect='excel', delimiter=',')
            for line in csv_file_contents:
                filewriter.writerow(line)

        return html

    #=============================================
    # accepts: str offer_id
    # returns: html to view/generate coord end of term data
    #
    def get_html_to_print_instructor_term_data(self, offer_id, local_filename):
        term_data = self.get_term_end_data(offer_id)
        summary_data = self.get_term_summary_full(offer_id)
        course_exams = summary_data.get('exams')
        scores_data = summary_data.get('scores')

        csv_file_contents=""
        csv_header = ["Section","Last name","First name", "XID","Number of Absences","Weighted Test Average","Calculated Course Average","Submitted Letter Grade","Calculated Letter Grade","University Letter Grade","Note"]
        csv_data = []

        if len(term_data) > 0:
            offer_info = self.get_offer_info(offer_id)
            semester = self.get_current_semester()
            year = self.get_current_year()
            prefix=offer_info.get('prefix')
            course_num=offer_info.get('course_num')
            section_num=offer_info.get('section_num')

            section_description = f"""{prefix} {course_num}-{section_num}"""
            download_filename=f"""{semester}_{year}_{section_description.replace(' ','_')}_term_end.csv"""

            html = f"""
<div style="margin-top: 30px;">If a student is ineligible to pass the class due to a low test/final exam average the note will say &quot;Ineligible to pass the class&quot;. If the submitted letter grade differs from the calculated letter grade or university letter grade they will both be highlighted in red.</div>
<div class="title">End of Term Data</div>
<div class="subtitle">{section_description}</div>
<div style="margin-top: 20px; margin-bottom: 30px;">
    <input class="btn_link" type="submit" onclick="get_end_of_term_csv('{download_filename}','view_data')" value="Download Data as CSV"></input>
</div>
<table class="outline view_term_end_tbl">
    <tr>
        <td>Last Name</td>
        <td>First Name</td>
        <td>XID</td>
        <td>Number of<br>Absences</td>
        <td>Weighted<br>Test Average</td>
        <td>Calculated<br>Course Average</td>
        <td>Submitted<br>Letter Grade</td>
        <td>Calculated<br>Letter Grade</td>
        <td>University<br>Letter Grade</td>
        <td>Note</td>
    </tr>
"""

            for i in range(0, len(term_data)):
                student = self.nones_to_blanks(term_data[i])
                # these should be in the same order
                scores = scores_data[i].get('exam_scores')

                (calculated_letter_grade, weighted_test_average, grade, min_test_grade, note) = self.get_calculated_letter_grade(offer_info.get('course_id'), course_exams, scores)

                univ_letter_grade = student.get('univ_letter_grade')
                letter_grade = student.get('letter_grade')

                error_class = " class=\"error\""

                letter_class = ""
                calculated_class = ""
                univ_class = ""

                if letter_grade != "":
                    if univ_letter_grade != "" and univ_letter_grade != letter_grade:
                        letter_class = error_class
                        univ_class = error_class

                    if calculated_letter_grade != letter_grade:
                        letter_class = error_class
                        calculated_class = error_class
                elif univ_letter_grade != "" and univ_letter_grade != calculated_letter_grade:
                    univ_class = error_class
                    calculated_class = error_class

                html += f"""
    <tr>
        <td>{student.get('last_name')}</td>
        <td>{student.get('first_name')}</td>
        <td>{student.get('xid')}</td>
        <td>{student.get('num_absences')}</td>
        <td>{weighted_test_average:.2f}%</td>
        <td>{grade:.2f}%</td>
        <td{letter_class}>{letter_grade}</td>
        <td{calculated_class}>{calculated_letter_grade}</td>
        <td{univ_class}>{univ_letter_grade}</td>
        <td>{note}</td>
    </tr>
"""
                new_line=[section_num,
                          student.get('last_name'),
                          student.get('first_name'),
                          student.get('xid'),
                          student.get('num_absences'),
                          weighted_test_average,
                          grade,
                          letter_grade,
                          calculated_letter_grade,
                          univ_letter_grade,
                          note]

                csv_data.append(new_line)

            html += "</table>"

            csv_file_contents=[csv_header]
            csv_file_contents.extend(csv_data)
        else:
            html = "No data"

        # Save CSV file so it is available for download
        with open(local_filename, 'w', newline='') as csvfile:
            filewriter = csv.writer(csvfile, dialect='excel', delimiter=',')
            for line in csv_file_contents:
                filewriter.writerow(line)

        return html


    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to revert scantron scores
    #
    def get_html_to_revert_scantron_scores(self, offer_id, exam_id):
        result = self.revert_to_scantron_responses(offer_id, exam_id)

        if result:
            html = """
<div>The scores have been reverted. You can view them by choosing the appropriate option in the dropdown list above.</div>
"""
        else:
            html = """
<div>This section does not exist.</div>
"""
        return html

    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to submit mc
    #
    def get_html_to_submit_mc(self, offer_id, exam_id):
        offer = self.get_offer_info(offer_id)
        exam = self.get_exam(exam_id)

        html = f"""
<div style="margin-top: 10px; padding-left: 2px;">Enter in data in the text area below to update student responses for this section and exam.
    <ul>
        <li>Use one row for each student (they do not need to be in any particular order).</li>
        <li>Columns can be separated by tabs, commas, or a mix of both.</li>
        <li>If a student did not take the test, mark them as absent using the dropdown above. You should not enter blank MC responses for them.</li>
        <li>Only those student responses you include will be updated, all others will remain unchanged.</li>
        <li>Scores will be rounded to two decimal places.</li>
    </ul>
<div class="example_link" id="example_btn" onclick="toggle_examples()">Show Examples</div>
<div id="examples" class="examples">
    <div>Entering the following will:
        <ul>
            <li>Store/update scores for student 1 (who took version A)</li>
            <li>Note student 1 left question 5 blank.</li>
            <li>Store/update scores for student 3 (who took version B)</li>
            <li>Leave student 2's responses unchanged.</li>
        </ul>
    </div>
    <textarea style="width: 250px; height: 80px; margin-top: 15px;" disabled="">
C11111111,A,DABB-ADBCBAC
C33333333,B,DCDAABDABCAD
</textarea>
</div>
</div>
<table class="data_table" style="margin-top: 20px;">
    <tr>
        <td>Section and Exam:</td>
        <td><span style="font-weight: bold; color: #F66733; font-size: 24px;">{offer.get('prefix')} {offer.get('course_num')}-{offer.get('section_num')} {exam.get('title')}</span><br>(if this is not correct then choose the correct section and exam in the dropdown lists above)</td>
    </tr>
    <tr>
        <td>Column format:</td>
        <td>XID, Version, Responses (separated by tabs, commas, or a mix of both)</td>
    </tr>
    <tr>
        <td>Data:</td>
        <td><textarea id="data" style="width: 800px; height: 600px;"></textarea></td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: center;">
            <button type="button" onclick="submit_responses()">Submit Responses</button>
            <input type="hidden" id="response_type" value="mc">
            <input type="hidden" id="data_offer_id" value="{offer_id}">
            <input type="hidden" id="data_exam_id" value="{exam_id}">
        </td>
    </tr>
</table>
"""
        return html

    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to submit fr
    #
    def get_html_to_submit_fr(self, offer_id, offer_info, exam_id):
        offer = self.get_offer_info(offer_id)
        exam = self.get_exam(exam_id)

        html = f"""
<div style="margin-top: 10px; padding-left: 2px;">Enter in data in the text area below to update student FR scores for this section and exam.
    <ul>
        <li>Use one row for each student (they do not need to be in any particular order).</li>
        <li>Columns can be separated by tabs, commas, or a mix of both.</li>
        <li>If a student did not take the test, mark them as absent using the dropdown above. You should not enter blank FR scores for them.</li>
        <li>Only those student scores you include will be updated, all others will remain unchanged.</li>
        <li>Scores will be rounded to two decimal places.</li>
    </ul>
<div class="example_link" id="example_btn" onclick="toggle_examples()">Show Examples</div>
<div id="examples" class="examples">
    <div>Entering the following (when the test has 4 FR questions) will:
        <ul>
            <li>Store/update scores for student 1 (who took version A)</li>
            <li>Store/update scores for student 3 (who took version B)</li>
            <li>Leave student 2's responses unchanged.</li>
        </ul>
    </div>
    <textarea style="width: 250px; height: 80px; margin-top: 15px;" disabled="">
C11111111,A,3,4,4,3
C33333333,B,2.5,4,3.5,3
</textarea>
</div>
</div>
<table class="data_table" style="margin-top: 20px;">
    <tr>
        <td>Section and Exam:</td>
        <td><span style="font-weight: bold; color: #F66733; font-size: 24px;">{offer.get('prefix')} {offer.get('course_num')}-{offer.get('section_num')} {exam.get('title')}</span><br>(if this is not correct then choose the correct section and exam in the dropdown lists above)</td>
    </tr>
    <tr>
        <td>Column format:</td>
        <td>XID, Version, Scores in the order they appear on the version (separated by tabs, commas, or a mix of both)</td>
    </tr>
    <tr>
        <td>Data:</td>
        <td><textarea id="data" style="width: 800px; height: 600px;"></textarea></td>
    </tr>
    <tr>
        <td></td>        <td style="text-align: center;">
            <button type="button" onclick="submit_responses()">Submit Scores</button>
            <input type="hidden" id="response_type" value="fr">
            <input type="hidden" id="data_offer_id" value="{offer_id}">
            <input type="hidden" id="data_exam_id" value="{exam_id}">
        </td>
    </tr>
</table>
"""
        return html

    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to submit end of term data
    #
    def get_html_to_submit_end_of_term_data(self, offer_id):
        offer = self.get_offer_info(offer_id)

        html = f"""
<div sty
le="margin-top: 10px; padding-left: 2px;">Enter in data in the text area below to update student end of term data for this section.
    <ul>
        <li>Use one row for each student (they do not need to be in any particular order)</li>
        <li>Columns can be separated by tabs, commas, or a mix of both</li>
        <li>The data for the students you list below  will be updated, data for all others will remain unchanged</li>
    </ul>
<div class="example_link" id="example_btn" onclick="toggle_examples()">Show Examples</div>
<div id="examples" class="examples">
    <div>Entering the following means:
        <ul>
            <li>Student 1 was absent 3 days and received an A for the course.</li>
            <li>Student 3 was absent 17 days and received an F for the course.</li>
            <li>Leave student 2's data unchanged.</li>
        </ul>
    </div>
    <textarea style="width: 250px; height: 80px; margin-top: 15px;" disabled="">
C11111111,3,A
C33333333,17,F
</textarea>
</div>
</div>
<table class="data_table" style="margin-top: 20px;">
    <tr>
        <td>Section:</td>
        <td><span style="font-weight: bold; color: #F66733; font-size: 24px;">{offer.get('prefix')} {offer.get('course_num')}-{offer.get('section_num')}</span><br>(if this is not correct then choose the correct section and exam in the dropdown lists above)</td>
    </tr>
    <tr>
        <td>Column format:</td>
        <td>XID, number of absences, letter grade (columns separated by tabs, commas, or a mix of both)</td>
    </tr>
    <tr>
        <td>Data:</td>
        <td><textarea id="data" style="width: 800px; height: 600px;"></textarea></td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: center;">
            <button type="button" onclick="submit_data()">Submit Data</button>
            <input type="hidden" id="data_offer_id" value="{offer_id}">
        </td>
    </tr>
</table>
"""
        return html

    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to submit fr
    #
    def get_html_to_submit_responses(self, data, response_type, offer_id, exam_id):
        try:
            if response_type == "mc":
                records = data.replace("\t", ",").split("\n")

                if len(records) > 0:
                    html = """<table class="outline response_tbl">
    <tr>
        <td>XID</td>
        <td>Version</td>
        <td>Responses</td>
        <td>Saved/Error</td>
    </tr>
"""

                    for record in records:
                        # record: XID,version,responses
                        record = record.split(",")
                        # we assume that the C in the XID, the version,
                        # and the responses should all be capital letters
                        record = [x.upper().strip() for x in record]

                        # collapse MC responses to a single string if needed
                        if len(record) > 3:
                            record = [record[0], record[1], "".join(record[2:])]

                        error = False
                        msg = ""

                        ## first we handle people who didn't take the test
                        #if len(record) == 2 and record[1] == "-":
                        #    record.append("")

                        if len(record) != 3:
                            error = True
                            msg = "The number of columns is wrong."
                            if len(record) == 2:
                                msg += " If the student was absent, mark them as such using the dropdown above, and do not record a blank MC version/responses for them."
                        elif not self.is_valid_xid(record[0]):
                            error = True
                            msg = "The XID is not valid."
                        elif not self.is_student_in_section(record[0], offer_id):
                            error = True
                            msg = "This student is not in this section."
                        elif self.was_student_absent(record[0], exam_id):
                            error = True
                            msg = "This student is marked as absent so their MC responses were ignored."
                        elif not self.is_version_valid(exam_id, record[1]):
                            error = True
                            msg = "The version is not valid."
                        else:
                            result = self.are_mc_responses_valid(exam_id, record[1], record[2])
                            if not result[0]:
                                error = True
                                msg = result[1]

                        if error:
                            html += f"""
    <tr>
        <td colspan="3">{",".join(record)}</td>
        <td><span class="error">{msg}</span></td>
    </tr>
"""
                        else:
                            # store the record
                            self.store_mc_responses(record[0], exam_id, record[1], record[2])

                            html += f"""
    <tr>
        <td>{record[0]}</td>
        <td>{record[1]}</td>
        <td>{record[2]}</td>
        <td><span style="color: #00AA00;">Saved</td>
    </tr>
"""

                    html += "</table>"
                else:
                    html = "There doesn't seem to be any data uploaded."
            elif response_type == "fr":
                records = data.replace("\t", ",").split("\n")

                if len(records) > 0:
                    key_version = self.get_exam_key_version(exam_id)
                    fr_questions = self.get_fr_questions(exam_id, key_version)
                    num_of_fr_questions = len(fr_questions)
                    exam_info={'key_version':key_version, 'fr_questions':fr_questions}

                    fr_question_num_html = "</td>\n\t<td>".join([question["question_num"] for question in fr_questions])

                    if len(fr_question_num_html) > 0:
                        fr_question_num_html = f"<td>{fr_question_num_html}</td>"

                    html = f"""<table class="outline response_tbl">
    <tr>
        <td>XID</td>
        <td>Version</td>
{fr_question_num_html}
        <td>Saved/Error</td>
    </tr>
"""

                    for record in records:
                        # record: XID,version,scores
                        record = record.split(",")
                        # we assume that the C in the XID and
                        # the version should all be capital letters
                        record = [x.upper().strip() for x in record]

                        # PW 2021-10-20:
                        # convert scores to floats
                        # round to 2 decimal places and convert back to strings
                        record[2:]=[float(score) for score in record[2:]]
                        record[2:]=[str(score) for score in np.round(record[2:],2)]

                        error = False
                        msg = ""

                        ## first we handle people who didn't take the test
                        #if len(record) == 2 and record[1] == "-":
                        #    record = [record[0]] + ["-"] * num_of_fr_questions

                        if len(record) != num_of_fr_questions + 2:
                            error = True
                            msg = "The number of columns is wrong. There should be {num_of_fr_questions + 2:.0f} columns."

                        if len(record) == 2:
                            msg += " If the student was absent, mark them as such using the dropdown above, and do not record a blank FR version/scores for them."
                        elif not self.is_valid_xid(record[0]):
                            error = True
                            msg = "The XID is not valid."
                        elif not self.is_student_in_section(record[0], offer_id):
                            error = True
                            msg = "This student is not in this section."
                        elif self.was_student_absent(record[0], exam_id):
                            error = True
                            msg = "This student is marked as absent so their FR scores were ignored."
                        elif not self.is_version_valid(exam_id, record[1]):
                            error = True
                            msg = "The version is not valid."
                        else:
                            result = self.are_fr_scores_valid(exam_info, record[2:])

                            if not result[0]:
                                error = True
                                msg = result[1]

                        if error:
                            html += f"""
    <tr>
        <td colspan="{num_of_fr_questions+2}">{",".join(record)}</td>
        <td><span class="error">{msg}</span></td>
    </tr>
"""
                        else:
                            # store the record
                            self.store_fr_scores(record[0], exam_id, record[1], record[2:])

                            fr_scores_html = "</td>\n\t<td>".join(str(score) for score in record[2:])

                            if len(fr_scores_html) > 0:
                                fr_scores_html = f"<td>{fr_scores_html}</td>"

                            html += f"""
    <tr>
        <td>{record[0]}</td>
        <td>{record[1]}</td>
{fr_scores_html}
        <td><span style="color: #00AA00;">Saved</td>
    </tr>
"""

                    html += "</table>"
                else:
                    html = "There doesn't seem to be any data uploaded."

            else:
                html = "Unknown response type ({response_type})"
        except:
            html = f"""An error has occured: {str(sys.exc_info())}"""
            html = html.replace("<", "&lt;").replace(">", "&gt;")
            html = f"""<span class="error">{html}</span>"""
        return html

    #=============================================
    # accepts: int exam_id
    # returns: html for instructors to view keys
    #
    def get_html_for_instructor_view_keys(self, exam_id,offer_info):
        key_version = self.get_exam_key_version(exam_id)
        versions = self.get_exam_versions(exam_id)

        exam_info = self.get_exam(exam_id)
        course_description = f"{offer_info.get('prefix')} {offer_info.get('course_num')}"

        choices = self.get_mc_choices(exam_id, exam_info.get('key_version'))

        question_num_cells = ""

        for choice in choices:
            question_num_cells += f"""
        <td>{choice["question_num"]}</td>"""

        question_num_row = f"""
    <tr>
        <td>Question Number</td>
{question_num_cells}
    </tr>
"""

        html = f"""
<p style="margin-top: 30px;">The highlighted version in the tables below is the key in the grade collection system. The keys for the other versions are calculated from this baseline key using the question choice pairing submitted by the coordinator for each version.</p>
<div class="title">{exam_info.get('title')} Keys</div>
<div class="subtitle">{course_description}</div>
"""

        # print a separate table for each key

        for version in versions:
            html += f"""
<table class="outline version_table">
{question_num_row}
"""

            highlight = ""
            if version == key_version:
                highlight = " class=\"row_highlight\""
            key = self.get_mc_key(exam_id, version)

            point_cells = ""
            key_cells = ""

            for answer in key:
                point_cells += f"""
        <td>{self.pretty_print_number(answer.get('points'))}</td>"""

                key_cells += f"""
        <td>{answer.get('correct_answers')}</td>"""

            html += f"""
    <tr>
        <td>Points</td>
{point_cells}
    </tr>
    <tr{highlight}>
        <td>Version {version}</td>
{key_cells}
    </tr>
</table>
"""

        html += "</table>"
        return html

    #=============================================
    # accepts: int exam_id,
    #          int offer_id,
    #          str action
    # returns: html for instructors to view mc item reports
    #
    def get_html_for_instructor_mc_and_fr_item_reports(self, exam_id, offer_id, action):
        exam_info = self.get_exam(exam_id)
        if action == "mc_section_item_report":
            report = self.get_mc_section_item_report(offer_id, exam_id)
            offer_info = self.get_offer_info(offer_id)
            title = f"{exam_info.get('title')} MC Individual Section Item Report"
            subtitle = f"{offer_info.get('prefix')} {offer_info.get('course_num')}-{offer_info.get('section_num')}"
        elif action == "mc_course_item_report":
            report = self.get_mc_course_item_report(exam_id)
            course_info = self.get_course_info(exam_info.get('course_id'))
            title = f"{exam_info.get('title')} MC All Sections Item Report"
            subtitle = f"{course_info.get('prefix')} {course_info.get('course_num')}"
        elif action == "fr_section_item_report":
            report = self.get_fr_section_item_report(offer_id, exam_id)
            offer_info = self.get_offer_info(offer_id)
            title = f"{exam_info.get('title')} FR Individual Section Item Report"
            subtitle = f"{offer_info.get('prefix')} {offer_info.get('course_num')}-{offer_info.get('section_num')}"
            total_num_responses = report.get('num_test_takers')
        elif action == "fr_course_item_report":
            report = self.get_fr_course_item_report(exam_id)
            course_info = self.get_course_info(exam_info.get('course_id'))
            title = f"{exam_info.get('title')} FR All Sections Item Report"
            subtitle = f"{course_info.get('prefix')} {course_info.get('course_num')}"
            total_num_responses = report.get('num_test_takers')
        else:
            return "Invalid action"

        data = report.get('data')

        freq_table_html = ""

        if report.get('num_test_takers') > 0:
            for i in range(0, len(report.get('freq_table'))):
                percentage = float(report.get('freq_table')[i]) / report.get('num_test_takers')
                freq_table_html += f"""
        <tr>
            <td style="text-align: right;">{10*i:.0f}%</td>
            <td style="vertical-align: middle; height: 30px;"><div style="background: #522D80; border: solid 1px #000000; height: 20px; width: {int(percentage*1000)}px;"></div></td>
            <td style="text-align: center;">{percentage*100:.1f}%</td>
        </tr>
"""


        item_html = ""

        question_choices = []
        question_table_rows = ""

        isFR=action.startswith('fr')
        if not isFR:
            # we have to loop over all choices in order to get the headers
            # and spacing when there are uneven choices
            for i in range(0, len(data)):
                cur_choices = data[i]
                if len(cur_choices) > len(question_choices):
                    question_choices = [choice.get('choice') for choice in cur_choices]

        for i in range(0, len(data)):
            num_zeros = 0
            if not isFR:
                graph = ""
                labels = ""
                percentages = ""

                num_correct = 0
                num_wrong = 0
                num_blank = 0

                cur_choices = data[i]
                choice_percents = []

                for choice in cur_choices:
                    if choice.get('correct'):
                        color = "#F66733"
                        data_color = "#F66733"
                        num_correct += choice.get('count')
                    else:
                        color = "#522D80"
                        data_color = "#000000"
                        if choice.get('choice') == "-":
                            num_blank += choice.get('count')
                        else:
                            num_wrong += choice.get('count')

                    graph += f"""<td style="vertical-align: bottom; text-align: center;"><div style="background: {color}; border: solid 1px #000000; margin: 0px auto; width: 40px; height: {int(1.5*choice.get('percentage'))}px;"></div></td>"""
                    labels += f"""<td style="text-align: center; font-weight: bold; color: {color}">{choice.get('choice')}</td>"""
                    percentages += f"""<td style="text-align: center; width: 55px;">{choice.get('percentage'):.1f}%</td>"""

                    choice_percents.append(f"""<span style="color: {data_color}">{choice.get('percentage'):.1f}</span>""")

                bar_graph = f"""
                <table>
                    <tr>
{graph}
                    </tr>
                    <tr>
{labels}
                    </tr>
                    <tr>
{percentages}
                    </tr>
                </table>
"""

                total_num_responses = num_correct + num_wrong + num_blank

            discriminant_index = report.get('discriminant_index_list')[i]

            notes = ""

            if discriminant_index < float(self.get_setting("discriminant_index_cutoff")):
                notes += """<li style="color: #0000AA;">low discriminant index</li>
"""

            if not isFR:
                if float(num_correct) / total_num_responses < float(self.get_setting("MC_score_lower_cutoff")):
                    notes += """<li style="color: #FF0000;">low % correct</li>
"""
                elif float(num_correct) / total_num_responses > float(self.get_setting("MC_score_upper_cutoff")):
                    notes += """<li style="color: #00AA00;">high % correct</li>
"""

                if len(notes) > 0:
                    notes = f"""
                <ul style="padding-left: 20px; margin-top: 0px; margin-bottom: 0px;">
{notes}
                </ul>
"""

                num_correct_percent = float(num_correct)/total_num_responses * 100
                num_wrong_percent = float(num_wrong)/total_num_responses * 100
                num_blank_percent = float(num_blank)/total_num_responses * 100
            elif isFR:
                if float(data[i].get('points')) == 0:
                    avg_score_percent = 0
                else:
                    avg_score_percent = data[i].get('avg_score')/float(data[i].get('points'))

                if avg_score_percent < float(self.get_setting("FR_score_lower_cutoff")):
                    notes += """<li style="color: #FF0000;">low average score</li>
"""
                elif avg_score_percent > float(self.get_setting("FR_score_upper_cutoff")):
                    notes += """<li style="color: #00AA00;">high average score</li>
"""
                if len(notes) > 0:
                    notes = f"""
                <ul style="padding-left: 20px; margin-top: 0px; margin-bottom: 0px;">
{notes}
                </ul>
"""
                if total_num_responses == 0:
                    num_zeros_percent = 0
                else:
                    num_zeros_percent = float(data[i].get('num_zeros'))/total_num_responses*100

            if isFR:
                item_html += f"""
        <tr>
            <td class="label" style="text-align: left;">Question {i+1}:</td>
            <td class="label">Points</td>
            <td class="label">Discriminant<br>Index</td>
            <td class="label">Average<br>Score</td>
            <td class="label">Average<br>Percent</td>
            <td class="label">Zeros</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
{notes}
            </td>
            <td class="question_stat">{self.pretty_print_number(data[i].get('points'))}</td>
            <td class="question_stat">{discriminant_index:.3f}</td>
            <td class="question_stat">{self.pretty_print_number(data[i].get('avg_score'),False)}</td>
            <td class="question_stat">{self.pretty_print_number(avg_score_percent*100,False)}%</td>
            <td class="question_stat">{self.pretty_print_number(num_zeros_percent,False)}%</td>
        </tr>
        <tr>
            <td colspan="6"><hr></td>
        </tr>
"""
            else:
                item_html += f"""
        <tr>
            <td class="label" style="text-align: left;">Question {i+1}:</td>
            <td class="label">Discriminant<br>Index</td>
            <td class="label">Correct</td>
            <td class="label">Wrong</td>
            <td class="label">Blank</td>
            <td rowspan="2">{bar_graph}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
{notes}
            </td>
            <td class="question_stat">{discriminant_index:.3f}</td>
            <td class="question_stat">{self.pretty_print_number(num_correct_percent,False)}%</td>
            <td class="question_stat">{self.pretty_print_number(num_wrong_percent,False)}%</td>
            <td class="question_stat">{self.pretty_print_number(num_blank_percent,False)}%</td>
        </tr>
        <tr>
            <td colspan="6"><hr></td>
        </tr>
"""

            if not isFR:
                choice_percent_cells = ""

                if len(choice_percents) > 0:
                    choice_percent_cells = "<td>" + "</td>\n\t\t<td>".join(choice_percents[0:-1]) + "</td>"
                    # add blank cells if there are uneven choices
                    if len(choice_percents) != len(question_choices):
                        # note that the header is the longest of the choices so
                        # len(current choice percents) <= len(header list)
                        cols_to_span = len(question_choices) - len(choice_percents)
                        if cols_to_span == 1:
                            choice_percent_cells += "<td></td>"
                        else:
                            choice_percent_cells += f"""<td colspan="{cols_to_span-1}"></td>"""

                    # add the column for blanks at the end
                    choice_percent_cells += f"<td>{choice_percents[-1]}</td>"

            if isFR:
                question_table_rows += f"""
            <tr>
                <td>{data[i].get('question_num')}</td>
                <td>{self.pretty_print_number(data[i].get('points'))}</td>
                <td>{discriminant_index:.3f}</td>
                <td>{self.pretty_print_number(data[i].get('avg_score'), False)}</td>
                <td>{self.pretty_print_number(avg_score_percent*100, False)}%</td>
                <td>{self.pretty_print_number(num_zeros_percent, False)}%</td>
                <td style="text-align: left;">{notes}</td>
            </tr>
"""
            else:
                question_table_rows += f"""
            <tr>
                <td>{i+1}</td>
                <td>{discriminant_index:.3f}</td>
                <td>{self.pretty_print_number(num_correct_percent, False)}%</td>
                <td>{self.pretty_print_number(num_wrong_percent, False)}%</td>
                <td>{self.pretty_print_number(num_blank_percent, False)}%</td>
{choice_percent_cells}
                <td style="text-align: left;">{notes}</td>
            </tr>
"""


        if isFR:
            directions="""The question numbers correspond to the key version of the exam. If a question has a low discriminant index, or is flagged for a high/low correct response rate it will be listed in the notes column."""
        else:
            directions="""The choices in orange are the correct answers. The question numbers and choices correspond to the key version of the exam. Answers that were blank (-), an invalid option (X), or couldn't be read by the scantron machine (?) are all treated as blanks for the summary shown below. If a question has a low discriminant index, or is flagged for a high/low correct response rate it will be noted below the question number."""
            question_choice_headers = ""
            if len(question_choices) > 0:
                question_choice_headers = "<td>" + "</td>\n\t\t<td>".join(question_choices) + "</td>"

        html="<p> No report currently available </p>"
        if report.get('total_points')>0:
            html = f"""
    <div class="title">{title}</div>
    <div class="subtitle">{subtitle}</div>
    <table>
        <tr>
            <td>Number of test takers:</td>
            <td>{report.get('num_test_takers')}</td>
        </tr>
        <tr>
            <td>Number absent:</td>
            <td>{report.get('num_absent')}</td>
        </tr>
        <tr>
            <td>Number missing:</td>
            <td>{report.get('num_missing')}</td>
        </tr>
        <tr>
            <td>Total points:</td>
            <td>{report.get('total_points')}</td>
        </tr>
        <tr>
            <td>Average Score:</td>
            <td>{report.get('avg_score'):.1f} ({report.get('avg_score')/report.get('total_points'):.1f}%)</td>
        </tr>
        <tr>
            <td>Standard Deviation:</td>
            <td>{report.get('std_scores'):.1f} ({report.get('std_scores')/report.get('total_points')*100:.1f}%)</td>
        </tr>
        <tr>
            <td>Coefficient of Variation:</td>
            <td>{report.get('coef_of_variation')*100:.1f}%</td>
        </tr>
        <tr>
            <td>Median:</td>
            <td>{report.get('median_score'):.1f} ({report.get('median_score')/report.get('total_points')*100:.1f}%)</td>
        </tr>
    </table>
    <div style="margin-top: 20px; font-weight: bold; font-size: 24px;">Frequency Table</div>
    <table>
{freq_table_html}
    </table>
    <div style="margin-top: 20px; font-weight: bold; font-size: 24px;">Item Analysis</div>
    <div style="margin-bottom: 20px;">{directions}</div>
    {'' if isFR else '<div style="margin-top: 20px; margin-bottom: 20px; font-weight: bold; font-size: 24px;">Data Table</div>'}
    <table class="mc_question_table faint_outline">
        <tr>
            <td>Question</td>
            {'<td>Points</td>' if isFR else ''}
            <td>Discriminant<br>Index</td>
            <td>{'Average<br>Score' if isFR else 'Correct'}</td>
            <td>{'Average<br>Percent' if isFR else 'Wrong'}</td>
            <td>{'Zeros' if isFR else 'Blank'}</td>
            {'' if isFR else question_choice_headers}
            <td>Notes</td>
        </tr>
{question_table_rows}
    </table>
"""
        if not isFR and report.get('total_points')>0:
            html+=f"""<div style="margin-top: 20px; font-weight: bold; font-size: 24px;">Summary</div>
    <div style="margin-bottom: 20px;">See the item analysis explanation above for full details.</div>
    <table class="item_report_tbl">
{item_html}
    </table>
"""
        return html


    #=============================================
    # accepts: int exam_id,
    #          int offer_id,
    #          str action
    # returns: html for instructors to view mc item reports
    #
    def get_html_for_instructor_course_report(self, exam_id, offer_id, user, action):
        if action == 'mc_course_report':
            report = self.get_mc_course_report(exam_id)
            # we sort it by {mc,fr}_score_avg
            report = sorted(report, key=itemgetter('mc_score_avg'))
            # report = sorted(report, key=lambda dict: dict.get('mc_score_avg'))
            mc_fr='MC'
        elif action == 'fr_course_report':
            report = self.get_fr_course_report(exam_id)
            report = sorted(report, key=itemgetter('fr_score_avg'))
            mc_fr='FR'
        elif action == 'overall_course_report':
            stats  = self.get_overall_course_stats(exam_id)
            report = self.get_overall_course_report(exam_id)
            report = sorted(report, key=itemgetter('score_avg'))
            mc_fr='' #left blank intentionally
        else:
            return "Invalid action"

        offer_info = self.get_offer_info(offer_id)
        exam_info = self.get_exam(exam_id)

        if mc_fr=='':
            freq_table_html = ""

            if stats.get('num_test_takers') > 0:
                for i in range(0, len(stats.get('freq_table'))):
                    percentage = float(stats.get('freq_table')[i]) / stats.get('num_test_takers')
                    freq_table_html += f"""
        <tr>
            <td style="text-align: right;">{10*i:.0f}%</td>
            <td style="vertical-align: middle; height: 30px;"><div style="background: #522D80; border: solid 1px #000000; height: 20px; width: {int(percentage*1000)}px;"></div></td>
            <td style="text-align: center;">{percentage*100:.1f}%</td>
        </tr>
"""

        #sections_being_taught = gl.get_current_sections(user, exam_info.get('course_id'))
        sections_being_taught = self.get_sections_taught(user, exam_info.get('course_id'), offer_info.get('semester'), offer_info.get('year'))

        html = f"""<div class="title">{exam_info.get('title')} {mc_fr}</div>
<div class="subtitle">{offer_info.get('prefix')} {offer_info.get('course_num')}</div>
"""

        report_html = ""

        for section in report:
            section_num = "***"

            if section.get('section_num') in sections_being_taught:
                section_num = f"{offer_info.get('prefix')} {offer_info.get('course_num')}-{section.get('section_num')}"

            if action == 'mc_course_report':
                report_html += f"""
    <tr>
        <td>{section_num}</td>
        <td>{self.pretty_print_number(section.get('avg_mc_percent'))}%</td>
        <td>{section.get('mc_score_avg'):.2f}</td>
        <td>{section.get('mc_score_std'):.2f}</td>
    </tr>
"""

            if action == 'fr_course_report':
                report_html += f"""
    <tr>
        <td>{section_num}</td>
        <td>{self.pretty_print_number(section.get('avg_fr_percent'))}%</td>
        <td>{section.get('fr_score_avg'):.2f}</td>
        <td>{section.get('fr_score_std'):.2f}</td>
    </tr>
"""

            if action == 'overall_course_report':
                report_html += f"""
    <tr>
        <td>{section_num}</td>
        <td>{section.get('score_avg'):.2f}</td>
        <td>{section.get('score_std'):.2f}</td>
    </tr>
"""

        if len(report) > 0:
            if action == 'overall_course_report':
                html += f"""
<table class="course_report_table outline">
    <tr>
        <td>Section</td>
        <td>Average</td>
        <td>Standard<br>Deviation</td>
    </tr>
{report_html}
</table>
    <div style="font-weight: bold; font-size: 24px; margin-top: 20px;">Statistics</div>
    <table>
        <tr>
            <td>Number of test takers:</td>
            <td>{stats.get('num_test_takers')}</td>
        </tr>
        <tr>
            <td>Number absent:</td>
            <td>{stats.get('num_absent')}</td>
        </tr>
        <tr>
            <td>Number missing:</td>
            <td>{stats.get('num_missing')}</td>
        </tr>
        <tr>
            <td>Total points:</td>
            <td>{stats.get('total_points')}</td>
        </tr>
        <tr>
            <td>Average Score:</td>
            <td>{stats.get('avg_score'):.1f} ({stats.get('avg_score')/stats.get('total_points')*100:.1f}%)</td>
        </tr>
        <tr>
            <td>Standard Deviation:</td>
            <td>{stats.get('std_scores'):.1f} ({stats.get('std_scores')/stats.get('total_points')*100:.1f}%)</td>
        </tr>
        <tr>
            <td>Coefficient of Variation:</td>
            <td>{stats.get('coef_of_variation')*100:.1f}%</td>
        </tr>
        <tr>
            <td>Median:</td>
            <td>{stats.get('median_score'):.1f} ({stats.get('median_score')/stats.get('total_points')*100:.1f}%)</td>
        </tr>
    </table>
    <div style="margin-top: 20px; font-weight: bold; font-size: 24px;">Frequency Table</div>
    <table>
{freq_table_html}
    </table>
"""
            else:
                html += f"""
<table class="course_report_table outline">
    <tr>
        <td>Section</td>
        <td>Average<br>Percent</td>
        <td>Average<br>Score</td>
        <td>Standard<br>Deviation</td>
    </tr>
{report_html}
</table>
"""
        else:
            if action == 'mc_course_report':
                html += "There is no report data yet. It will be available immediately once scantron data is uploaded."
            if action == 'fr_course_report':
                html += "There is no report data yet. It will be available immediately once FR data is uploaded."
            if action == 'overall_course_report':
                html += "This report does not exist for this course and exam."
        return html

    #=============================================
    # accepts: int exam_id,
    #          int offer_id,
    #          str action
    # returns: html to list all current exams for admins
    #
    def get_html_to_list_all_exams(self):
        content=""
        courses = self.get_active_courses()

        for course in courses:
            exams = self.get_current_exams(course.get('course_id'))

            exam_tbl = ""

            for exam in exams:
                if type(exam.get('date_given')) is not datetime.date:
                    date_given_str = ""
                else:
                    date_given_str = exam.get('date_given').strftime("%m-%d-%Y")

                if type(exam.get('grades_due')) is not datetime.date:
                    grades_due_str = ""
                else:
                    grades_due_str=exam.get('grades_due').strftime("%m-%d-%Y")

                exam_tbl += f"""
                    <tr>
                        <td><img class="img_btn" onclick="delete_exam({exam.get('exam_id')})" src="static/images/del.png"></td>
                        <td><img class="img_btn" onclick="manage_exam_AJAX('edit',{exam.get('exam_id')},{course.get('course_id')})" src="static/images/edit.png"></td>
                        <td>{exam.get('title')}</td>
                        <td>{date_given_str}</td>
                        <td>{grades_due_str}</td>
                        <td>{exam.get('weight'):1.3f}</td>
                    </tr>
"""

            if len(exams) > 0:
                exam_tbl = f"""
                <table class="faint_outline course_exam_tbl">
                    <tr>
                        <td colspan="2"></td>
                        <td>Title</td>
                        <td>Given</td>
                        <td>Grades due</td>
                        <td>Weight</td>
                    </tr>

{exam_tbl}
                </table>
"""

            content += f"""
    <tr>
        <td>{course.get('prefix')} {course.get('course_num')}</td>
        <td>
            <div class="exam_container">
{exam_tbl}
            </div>
            <hr>
            <img class="img_btn" onclick="manage_exam_AJAX('add','',{course.get('course_id')})" src="static/images/add.png"> Add a new exam
        </td>
    </tr>
"""

        if len(content) > 0:
            content = f"""
<div class="title">Exams</div>
<table class="outline exams_tbl">
{content}
</table>"""
        return content

    #=============================================
    # accepts: int exam_id,
    #          int offer_id,
    #          str action
    # returns: html to list all current exams for admins
    #
    def get_html_for_exam_add_edit_pages(self, form_name, exam_id, course_id):
        course_info = self.get_course_info(course_id)

        course_description = f"{course_info.get('prefix','')} {course_info.get('course_num','')}"

        exam = self.get_exam(exam_id)

        if type(exam.get('date_given')) is not datetime.date:
            date_given_str = ""
        else:
            date_given_str = exam.get('date_given').strftime("%m-%d-%Y")

        if type(exam.get('grades_due')) is not datetime.date:
            grades_due_str = ""
        else:
            grades_due_str = exam.get('grades_due').strftime("%m-%d-%Y")

        content = f"""
    <div class="title">{form_name.capitalize()} Exam</div>
    <div style="text-align: center; margin-bottom: 15px;">({course_description})</div>
    <div id="msg"></div>
    <form action="javascript:submit_form('{form_name}_exam',{course_id},{exam_id})">
      <table class="exam_edit_tbl">
        <tr>
            <td>Title</td>
            <td>Date Given</td>
            <td>Grades Due</td>
            <td style="text-align: center;">Weight</td>
        </tr>
        <tr>
            <td><input type="text" id="title" value="{exam.get('title')}" size="20" required="True"></td>
            <td><input type="text" class="date" id="date_given" value="{date_given_str}" placeholder="MM-DD-YYYY" required='True' required="True"></td>
            <td><input type="text" class="date" id="grades_due" value="{grades_due_str}" placeholder="MM-DD-YYYY" required="True"></td>
            <td><input type="text" id="weight" style="text-align: right;" value="{exam.get('weight'):1.3f}" placeholder="0.000" size="5" required="True"></td>
        </tr>
      </table>
      <div style="text-align: center;">
        <button type="submit">Save Exam</button>
      </div>
    </form>
"""
        return content

    #=============================================
    # accepts: string semester
    #          int year
    #          int MATH_1040_avg_item_id
    #          int only_missing
    # returns: html for actions page
    #
    def get_html_for_actions_page(self, semester, year, MATH_1040_avg_item_id, only_missing,info=''):
        if len(info) > 0:
            info = f"""
<div style="margin-top: 15px; margin-bottom: 15px;">
{info}
</div>
<hr>
"""

        cursor = self.get_cursor()
        cursor.execute("""SELECT course_id FROM course.course_list WHERE (prefix='MATH' OR old_prefix='MTHSC' OR alt_prefix='MTHS') AND (course_num=1070 OR old_course_num=107)""");

        # 7 should be the course id for MATH 1070
        # old system had this hard-coded as 7. Setting defaults to 7 just in case
        result=cursor.fetchall()
        if len(result)>0:
            math1070_course_id=result[0].get('course_id',7)
        else:
            math1070_course_id=7

        exams = self.get_exams(math1070_course_id, self.get_current_semester(), self.get_current_year())

        display = []
        values = []

        for exam in exams:
            display.append(exam.get('title'))
            values.append(exam.get('exam_id'))

        MATH_1070_exam_dropdown = self.get_dropdown_html(values, display, MATH_1040_avg_item_id, "MATH_1040_avg_item_id")

        if only_missing == 1:
            checked_html = " checked"
        else:
            checked_html = ""

        content = f"""
{info}
<form method="POST" action="actions" onsubmit="return verify_import()">
<div style="margin-top: 25px;">Import the MATH 1040 averages from a previous semester for the current MATH 1070 students.</div>
<div style="margin-top: 25px;">
    <ol>
        <li>
            <div>Select the semester to import MATH 1040 averages from</div>
            <div style="margin: 20px;">{self.get_semester_dropdown(semester)} {self.get_year_dropdown(year)}</div>
        </li>
        <li>
            <div>The averages should be imported to</div>
            <div style="margin: 20px; margin-bottom: 10px;">{MATH_1070_exam_dropdown}</div>
            <div>* this item should have exactly 1 version named A</div>
            <div style="margin-bottom: 20px;">* there should be 1 FR question worth 100 points</div>
        </li>
        <li>
            <div style="margin-bottom: 20px;"><input type="checkbox" name="only_missing" id="only_missing" value="1"{checked_html}> Replace only missing grades or zeros.</div>
        </li>
        <li><input type="submit" value="Import Grades"></li>
    </ol>
    <input type="hidden" name="form_name" value="1040_grade_import">
</div>
</form>
<br><hr><br>
<a href="../rolls/view_rolls">Shortcut to download rolls from Banner</a> (fixes problems when automated download doesn't complete)
"""

        return content

    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to submit end of term data
    #
    def import_1040_grades(self, semester, year, MATH_1040_avg_item_id, only_missing):
        # check that the selected item has exactly 1 FR question worth 100 points
        fr_questions = self.get_fr_questions(MATH_1040_avg_item_id, "A")

        if len(fr_questions) != 1 or fr_questions[0].get('points') != 100:
            info = "The item you are importing grades into must have 1 FR question worth 100 points for version A."
        else:
            # get all the 1040 averages from the specified semester
            old_semester = semester
            old_year = int(year)

            # 91 is the course_id for MATH 1040
            math1040_course_id=self.get_course_id('math',1040,91)
            old_sections = self.get_course_sections(math1040_course_id, old_semester, old_year)

            MATH_1040_student_data = {}

            for section in old_sections:
                # get the term summary data for the 1040 students from the last semester
                summary_data = self.get_term_summary(section.get('offer_id'))

                old_exams = summary_data.get('exams')
                old_scores = summary_data.get('scores')

                if old_scores:
                    for entry in old_scores:
                        student = self.nones_to_blanks(entry.get('student'))
                        student_scores = entry.get('exam_scores')

                        # 91 is the course_id for MATH 1040
                        (calculated_letter_grade, weighted_test_average, grade, min_test_grade, note) = self.get_calculated_letter_grade(math1040_course_id, old_exams, student_scores)

                        if calculated_letter_grade not in ["?", "F", "I"]:
                            MATH_1040_student_data[student.get('xid')] = grade

            # 7 is the course_id for MATH 1070
            math1070_course_id=self.get_course_id('math',1070,7)
            offers = self.get_offers_of_course(self.get_current_semester(), self.get_current_year(), math1070_course_id)

            num_imported = 0
            num_skipped = 0

            missing_data = []

            for offer in offers:
                students = self.get_roll(offer.get('offer_id'))

                for student in students:
                    if student.get('xid') in MATH_1040_student_data.keys():

                        if only_missing:
                            # check that this student does not have a grade already
                            fr_data = self.get_student_fr_scores(student.get('xid'), MATH_1040_avg_item_id)
                            if fr_data.get('points_earned') != 0:
                                num_skipped += 1
                                # jump to the next student since we don't import this grade
                                continue

                        # insert their grade from 1040 into the specified item
                        num_imported += 1
                        # print("%s: %d, %s, %s"% (student["xid"], MATH_1040_avg_item_id, "A", ["%0.2f" % MATH_1040_student_data[student["xid"]]]))
                        # self.store_fr_scores(student.get('xid'), MATH_1040_avg_item_id, "A", ["%0.2f" % MATH_1040_student_data.get(student.get('xid'))])
                    else:
                        missing_data.append({"section_num": offer["section_num"], "xid": student["xid"], "last_name": student["last_name"], "first_name": student["first_name"]})


            missing_html = ""
            if len(missing_data) > 0:
                missing_rows_html = ""

                for student in missing_data:
                    missing_rows_html += f"""
        <tr>
            <td>MATH 1040-{student.get('section_num')}</td>
            <td>{student.get('xid')}</td>
            <td>{student.get('last_name')}, {student.get('first_name')}</td>
        </tr>
"""

            missing_html = f"""
    <div style="font-weight: bold;">Grades were not found for the following students.</div>
    <table style="margin: 10px auto;" class="outline">
        <tr>
            <td>Section</td>
            <td>XID</td>
            <td>Name</td>
        </tr>
{missing_rows_html}
    </table>
"""

        # summarize what happened during the import
        info = f"""
    <div>Grades were found and imported for {num_imported} students.</div>
    <div>Grades were found but not imported for {num_skipped} students because they already had a grade recorded.</div>
{missing_html}
"""
        return info


    #=============================================
    # accepts: int offer_id,
    #          dict offer_info,
    #          int exam_id
    # returns: html to submit end of term data
    #
    def submit_end_of_term_data(self, data, offer_id):
        try:

            records = data.replace("\t", ",").split("\n")

            if len(records) > 0:
                html = """<table class="outline term_end_tbl">
    <tr>
        <td>XID</td>
        <td>Number of<br>Absences</td>
        <td>Letter<br>Grade</td>
        <td>Saved/Error</td>
    </tr>
"""

                for record in records:
                    # record: XID,number of absences,letter grade
                    record = record.split(",")
                    # we assume that the C in the XID and
                    # the letter grade should all be capital letters
                    record = [x.upper().strip() for x in record]

                    error = False
                    msg = ""

                    if len(record) != 3:
                        error = True
                        msg = "The number of columns is wrong."
                    elif not self.is_valid_xid(record[0]):
                        error = True
                        msg = "The XID is not valid."
                    elif not self.is_student_in_section(record[0], offer_id):
                        error = True
                        msg = "This student is not in this section."
                    elif not self.is_letter_grade_valid(record[2]):
                        error = True
                        msg = "The letter grade should be one of {A,B,C,D,F,I,P,NP}"

                    if error:
                        html += f"""
    <tr>
        <td colspan="3">{",".join(record)}</td>
        <td><span class="error">{msg}</span></td>
    </tr>
"""
                    else:
                        # store the record
                        self.store_term_end_data(offer_id, record[0], record[1], record[2])


                        html += f"""
    <tr>
        <td>{record[0]}</td>
        <td>{record[1]}</td>
        <td>{record[2]}</td>
        <td><span style="color: #00AA00;">Saved</span></td>
    </tr>
"""

                html += "</table>"
            else:
                html = "There doesn't seem to be any data uploaded."

        except:
            html = f"""An error has occured: {str(sys.exc_info())}"""
            html = html.replace("<", "&lt;").replace(">", "&gt;")
            html = f"""<span class="error">{html}</span>"""
        return html

    #=============================================
    # accepts: str info
    # returns: html to print admin settings content
    #
    def get_html_settings_content(self,info):
        now = datetime.datetime.now()
        # now = datetime.now()
        date_year = now.year

        semester_dropdown=self.get_semester_dropdown(self.get_current_semester())
        year_dropdown=self.get_max_year_dropdown(self.get_current_year(), date_year + 1)

        content = f"""
<div style="font-weight: bold; font-size: 25px;">Current semester and year</div>
<table style="margin-left: 25px;">
    <tr>
        <td>semester</td>
        <td>year</td>
    </tr>
    <tr>
        <td>{semester_dropdown}</td>
        <td>{year_dropdown}</td>
    </tr>
</table>
"""

        # create dropdowns of exams for each course that takes the BST
        # the course ids are hard coded since they shouldn't be changing anytime soon
        exams_1040 = self.get_BST_dropdown_html(91, "1040_bst_exam_id")
        exams_1060 = self.get_BST_dropdown_html(6, "1060_bst_exam_id")
        exams_1070 = self.get_BST_dropdown_html(7, "1070_bst_exam_id")
        exams_1080 = self.get_BST_dropdown_html(8, "1080_bst_exam_id")

        content += f"""
<div style="font-weight: bold; font-size: 25px; margin-top: 25px;">Current BST exams</div>
<table style="margin-left: 25px;">
    <tr>
        <td>MTHS 1040 BST exam:</td>
        <td>{exams_1040}</td>
    </tr>
    <tr>
        <td>MTHS 1060 BST exam:</td>
        <td>{exams_1060}</td>
    </tr>
    <tr>
        <td>MTHS 1070 BST exam:</td>
        <td>{exams_1070}</td>
    </tr>
    <tr>
        <td>MTHS 1080 BST exam:</td>
        <td>{exams_1080}</td>
    </tr>
</table>
"""

        content += f"""
<div style="font-weight: bold; font-size: 25px; margin-top: 25px;">Cutoffs for reports</div>
<table style="margin-left: 25px;">
    <tr>
        <td>Discriminant index cutoff:</td>
        <td><input type="text" style="width: 40px;" name="discriminant_index_cutoff" value="{self.get_setting('discriminant_index_cutoff')}"></td>
    </tr>
    <tr>
        <td>FR score lower cutoff:</td>
        <td><input type="text" style="width: 40px;" name="FR_score_lower_cutoff" value="{self.get_setting('FR_score_lower_cutoff')}"></td>
    </tr>
    <tr>
        <td>FR score upper cutoff:</td>
        <td><input type="text" style="width: 40px;" name="FR_score_upper_cutoff" value="{self.get_setting('FR_score_upper_cutoff')}"></td>
    </tr>
    <tr>
        <td>MC score lower cutoff:</td>
        <td><input type="text" style="width: 40px;" name="MC_score_lower_cutoff" value="{self.get_setting('MC_score_lower_cutoff')}"></td>
    </tr>
    <tr>
        <td>MC score upper cutoff:</td>
        <td><input type="text" style="width: 40px;" name="MC_score_upper_cutoff" value="{self.get_setting('MC_score_upper_cutoff')}"></td>
    </tr>
    <tr>
        <td>Normalized score min:</td>
        <td><input type="text" style="width: 40px;" name="normalized_score_min" value="{self.get_setting('normalized_score_min')}"></td>
    </tr>
    <tr>
        <td>Normalized score max:</td>
        <td><input type="text" style="width: 40px;" name="normalized_score_max" value="{self.get_setting('normalized_score_max')}"></td>
    </tr>
</table>
"""

#         content += f"""
# <div style="margin-top: 25px;">TODO: Manage the courses that are being used in grade collection this semester.</div>
# """

        content = f"""
<div style="text-align: center; margin-top: 15px; margin-bottom: 15px; min-height: 30px;">
    <span id="info_box" style="display: inline-block; padding: 15px; background: #FFFF99; border: solid 2px #000000;">{info}</span>
</div>
<form method="POST" action="settings">
{content}
<div style="margin-top: 25px;">
    <input type="submit" value="Save Settings">
    <input type="hidden" name="form_name" value="settings">
</div>
</form><br>
"""
        return content


    #=============================================
    # accepts: None
    # returns: html to print extra content
    #
    def get_html_extra_content(self):
        extra_content="""
<hr><div style="font-weight: bold; font-size: 25px; margin-top: 25px;">Manage Access</div>
	<form method="POST" action="settings">
		<input type="text" name="user_to_add" placeholder="USER ID" style="text-transform:uppercase;"></input>
		<select name="access_to_add">
			<option value="admin">admin</option>
			<option value="staff">staff</option>
		</select>
		<input type="submit" value="Add Access">
    	<input type="hidden" name="form_name" value="add_access">
	</form><br>
"""
        return extra_content


    #=============================================
    # accepts: None
    # returns: html to print access list
    #
    def get_html_access_table(self):
        access_list = self.get_access_list()
        access_table = """
        <table>
                <tr>
			<th>USER ID</th>
			<th>Access</th>
			<th>Revoke</th>
		</tr>
"""
        for user in access_list:
            access_table += f"""
	        <tr>
                        <td>{user.get('username')}</td>
                        <td>{user.get('role')}</td>
                        <td><form method="POST" action="settings">
                                        <input type="hidden" name="user_to_revoke" value="{user.get('username')}"></input>
                                        <input type="hidden" name="access_to_revoke" value="{user.get('role')}"></input>
                                        <input type="hidden" name="form_name" value="revoke_access"></input>
                                        <input type="submit" value="Revoke Access"></input>
                                </form>
                        </td>
                </tr>
"""
        return access_table

    #=============================================
    # accepts: None
    # returns: html to print access list
    #
    def get_html_for_admin_term_end(self):
        pass

    #=============================================
    # accepts: str xid
    #
    # returns: html to print access list
    #
    def get_grade_report(self,xid):
        content = ""

        cursor =self.get_cursor()

        #=====================
        # Student Name
        #=====================
        name_sql = """SELECT name FROM Banner_info.student_info WHERE xid = "{}" """
        cursor.execute(name_sql.format(xid))

        if cursor.rowcount == 1:
            name = cursor.fetchone().get('name')
            content += f"""<h4 align=left>Scores for {name} (XID: {xid})</h4>"""

        #=====================
        # MthSc Course Grades
        #=====================
        content += "<caption><b>MthSc Course Scores</b></caption>"

        # get math courses
        rolls_sql = """SELECT * FROM rolls.rolls WHERE xid = "{}" """
        cursor.execute(rolls_sql.format(xid))

        if cursor.rowcount > 0:
            courses = sorted(cursor.fetchall(), key=itemgetter('term_code'), reverse=True)

            # for each math course
            for course in courses:

                # get semester and year from term code
                year = course.get('term_code')[:4]
                term_digits = course.get('term_code')[4:]
                semester = ""
                if term_digits == "01":
                    semester = "spring"
                if term_digits == "08":
                    semester = "fall"
                term = f"{semester.capitalize()} {year}"
                section = f"{course.get('subject_code')} {course.get('course_number')}-{course.get('section_number')}"

                # lookup course id from course_list
                get_course_id_sql = f"""SELECT course_id FROM course.course_list WHERE (prefix="{course.get('subject_code')}" OR alt_prefix="{course.get('subject_code')}") AND course_num="{course.get('course_number')}" """
                cursor.execute(get_course_id_sql)
                course_id = cursor.fetchone().get('course_id')

                # use course_id and section to find offer_id
                get_offer_id_sql = f"""SELECT IF(offer_id IS NULL,"-1",offer_id) as offer_id FROM course.course_offerings WHERE course_id="{course_id}" AND section_num="{course.get('section_number')}" AND semester="{semester}" AND year="{year}" """
                cursor.execute(get_offer_id_sql)
                if cursor.rowcount > 0:
                    offer_id = cursor.fetchone().get('offer_id')
                else:
                    offer_id = -1

                if offer_id != -1:
                    # use offer_id to get instructor
                    get_instructor_sql = f""" SELECT CONCAT(first_name," ",last_name) AS instructor FROM course.people_to_offers_link INNER JOIN dept_info.person ON employee_username = username WHERE offer_id = "{offer_id}" """
                    cursor.execute(get_instructor_sql)
                    instructor = cursor.fetchone().get('instructor')

                    # test scores from grade collection
                    # get exam data
                    exams = self.get_exams(course_id, semester, year)
                    for exam in exams:
                        exam_data = self.get_overall_scores(offer_id, exam.get('exam_id'))
                        for score in exam_data:
                             if score["xid"] == xid:
                                 exam["score"] = score.get('total_points')

                    # get end of term data [lifted from coord_term_end.py]
                    term_data = self.get_term_end_data(offer_id)
                    summary_data = self.get_term_summary(offer_id)

                    # print('\n+++++++++++<br>\n');
                    # print(course_id, semester, year, course);
                    # print('\n<br>+++++++++++<br>\n');

                    course_exams = summary_data.get('exams')
                    scores_data = summary_data.get('scores')

                if len(term_data) > 0:
                    for i in range(0, len(term_data)):
                        student = self.nones_to_blanks(term_data[i])
                        if student["xid"] == xid:
                             scores = scores_data[i].get('exam_scores')

                             (calculated_letter_grade, weighted_test_average, grade, min_test_grade, note) = self.get_calculated_letter_grade(course_id, course_exams, scores)

                             univ_letter_grade = student.get('univ_letter_grade')
                             letter_grade = student.get('letter_grade')
                             num_absences = student.get('num_absences')

                # display grades
                if len(exams) > 0:
                    # headings
                    content += """<table width="100%" border=1 cellpadding=2 cellspacing=0>
                         <tr><th>Term</th><th>Course-Section</th><th>Instructor</th>"""

                    for exam in exams:
                        content += f"""<th>{exam.get('title')}</th> """
                    content += """<th>Number of<br>Absences</th>
                        <th>Weighted<br>Test Average</th>
                        <th>Calculated<br>Course Average</th>
                        <th>Submitted<br>Letter Grade</th>
                        <th>Calculated<br>Letter Grade</th>
                        <th>University<br>Letter Grade</th></tr>"""

                    # data
                    content += f"""<tr><td>{term}</td><td>{section}</td><td>{instructor}</td> """
                    for exam in exams:
                        content += f"""<td >{exam.get('score')}</td>"""
                    content += f"""<td>{num_absences}</td><td>{weighted_test_average:0.3f}</td><td>{grade:0.3f}</td><td>{letter_grade}</td><td>{calculated_letter_grade}</td><td>{univ_letter_grade}</td> """
                    content += "</tr></table><br>"

        #=====================
        # MthSc Course Grades
        #=====================
        rolls_sql = """SELECT * FROM rolls.rolls WHERE xid = "{}" """
        cursor.execute(rolls_sql.format(xid))

        if cursor.rowcount > 0:
            courses = sorted(cursor.fetchall(), key=itemgetter('term_code'), reverse=True)

            content += """<table border=1 cellpadding=4 cellspacing=0>
          <caption><b>MthSc Course Grades</b></caption>
          <tr>
          <th>Term</th>
          <th>Course - Section</th>
          <th>Grade</th>
          </tr>"""

            for course in courses:
                year = course.get('term_code')[:4]
                term_digits = course.get('term_code')[4:]
                term = ""
                if term_digits == "01":
                    term = "Spring " + year
                if term_digits == "08":
                    term = "Fall " + year
                content += f"""<tr>
               <td>{term}</td>
               <td>{course.get('subject_code')} {course.get('course_number')} - {course.get('section_number')}</td>
               <td>{course.get('univ_letter_grade')}</td>
               </tr>"""
            content += "</table><br>"

        #=====================
        # CMPT Scores
        #=====================
        cmpt_sql = """SELECT * FROM cmpt.cmpt_scores WHERE xid = "{}" """
        cursor.execute(cmpt_sql.format(xid))

        if cursor.rowcount > 0:
            cmpt_scores = sorted(cursor.fetchall(), key=itemgetter('test_date'), reverse=True)

            content += """<table border=1 cellpadding=4 cellspacing=0>
          <caption><b>CMPT Scores</b></caption>
          <tr>
          <th>CMPT Date</th>
          <th>Score</th>
          <th>Attempt #</th>
          <th>Approval</th>
          </tr>"""
            for score in cmpt_scores:
                if score['approval'] == "":
                    approval = 'pending'
                else:
                    approval = score.get('approval')
                content += f"""<tr>
               <td>{score.get('test_date')}</td>
               <td>{score.get('score')}</td>
               <td>{score.get('test_number')}</td>
               <td>{approval}</td>
               </tr>"""
            content += "</table>"

        #=====================
        # SAT/ACT Scores from University Oracle Database
        #=====================
        # banner_cursor = con.cursor()
        banner_cursor = self.get_banner_cursor()
        banner_cursor.execute("""SELECT QTY_SAT_VERBAL, QTY_SAT_MATH, QTY_ACT_MATH, QTY_ACT_COMPOSITE FROM SIS_MTHSC_ACCEPTED_STUDENTS WHERE xid = '%s'""" % xid)

        if banner_cursor.rowcount > 0:
            scores = banner_cursor.fetchone()
            if scores is not None:
                content += """<table border=1 cellpadding=4 cellspacing=0>
               <caption><br><b>SAT/ACT Scores</b></caption>
               <tr><th>SAT Verbal</th><th>SAT Math</th><th>ACT Math</th><th>ACT Total</th></tr>"""
                content += f"""<tr>
                  <td>{scores[0]}</td>
                  <td>{scores[1]}</td>
                  <td>{scores[2]}</td>
                  <td>{scores[3]}</td>
                  </tr>"""
                content += "</table>"
        return content


    #=============================================
    # accepts: str username,
    #          str semester,
    #          str year
    # returns: True if this user is a coordinator for the term; False otherwise
    #
    def is_coordinator(self,username, semester, year):
        cursor = self.get_cursor()

        # TODO: only count courses included in grade collection
        cursor.execute("SELECT COUNT(employee_username) AS valid FROM course.course_coordinators WHERE employee_username = '{}' AND semester = '{}' AND year = '{}'".format(username, semester, year))

        if cursor.fetchone().get('valid') > 0:
            return True
        else:
            return False

    #=============================================
    # accepts: str username,
    #          str semester,
    #          str year
    # returns: True if this user is an instructor for the term; False otherwise
    #
    def is_instructor(self,username, semester, year):
        cursor = self.get_cursor()

        # TODO: only count courses included in grade collection
        cursor.execute("SELECT COUNT(employee_username) AS valid FROM course.people_to_offers_link AS pol LEFT JOIN course.course_offerings AS co ON co.offer_id = pol.offer_id WHERE employee_username = '{}' AND semester = '{}' AND year = '{}'".format(username, semester, year))

        if cursor.fetchone().get('valid') > 0:
            return True
        else:
            return False


    #=============================================
    # accepts: str username,
    #          str semester,
    #          str year
    # returns: True if this user is a coordinator for the term and course; False otherwise
    #
    def is_coordinating(self,username, semester, year, course_id):
        cursor = self.get_cursor()

        # TODO: only count courses included in grade collection
        cursor.execute("SELECT COUNT(employee_username) AS valid FROM course.course_coordinators WHERE employee_username = '{}' AND semester = '{}' AND year = '{}' AND course_id = '{}'".format(username, semester, year, course_id))

        if cursor.fetchone().get('valid') > 0:
            return True
        else:
            return False


    #=============================================
    # accepts: str username
    # returns: True if this user is an instructor for the current term; False otherwise
    #
    def is_current_instructor(self,username):
        return self.is_instructor(username, self.get_current_semester(), self.get_current_year())


    #=============================================
    # accepts: str username
    # returns: True if this user is a coordinator for the current term; False otherwise
    #
    def is_current_coordinator(self,username):
        return self.is_coordinator(username, self.get_current_semester(), self.get_current_year())


    #=============================================
    # accepts: none
    # returns: list of courses the system is being used for
    #
    def get_active_courses(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT cl.course_id, cl.prefix, cl.alt_prefix, cl.course_num FROM active_courses AS ac LEFT JOIN course.course_list AS cl ON cl.course_id = ac.course_id WHERE ac.active = 1 ORDER BY prefix, course_num")

        return cursor.fetchall()


    #=============================================
    # accepts: str username,
    #          str semester,
    #          str year,
    #          list courses
    # returns: list of offers being taught or in the list of courses
    #
    def get_offers(self,username, semester, year, courses):
        cursor = self.get_cursor()

        courses_sql = ""

        if len(courses) > 0:
            courses_sql = "OR co.course_id IN (" + ", ".join(courses) + ")"

        cursor.execute("""
SELECT cl.course_id, cl.prefix, cl.alt_prefix, cl.course_num, section_num, co.offer_id FROM course.people_to_offers_link AS pol
LEFT JOIN course.course_offerings AS co
ON pol.offer_id = co.offer_id
LEFT JOIN course.course_list AS cl
ON cl.course_id = co.course_id
LEFT JOIN grade_collection.active_courses AS ac
ON ac.course_id = co.course_id
WHERE
 (employee_username = '{}'""" + courses_sql + """) AND
 co.semester = '{}' AND
 co.year = '{}' AND
 ac.active = 1
ORDER BY prefix, course_num, section_num""".format(username, semester, year))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: str semester,
    #          str year
    # returns: list of offers being taught
    #
    def get_all_offers(self, semester, year):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT cl.course_id, cl.prefix, cl.alt_prefix, cl.course_num, section_num, co.offer_id FROM course.course_offerings AS co
LEFT JOIN course.course_list AS cl
ON cl.course_id = co.course_id
LEFT JOIN active_courses AS ac
ON ac.course_id = co.course_id
WHERE
 co.semester = '{}' AND
 co.year = '{}' AND
 ac.active = 1
ORDER BY prefix, course_num, section_num""".format(semester, year))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: str semester,
    #          str year,
    #          int course id
    # returns: list of offers for this course
    #
    def get_offers_of_course(self,semester, year, course_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT cl.course_id, cl.prefix, cl.alt_prefix, cl.course_num, section_num, co.offer_id FROM course.course_offerings AS co
LEFT JOIN course.course_list AS cl
ON cl.course_id = co.course_id
LEFT JOIN active_courses AS ac
ON ac.course_id = co.course_id
WHERE
 co.semester = '{}' AND
 co.year = '{}' AND
 co.course_id = '{}' AND
 ac.active = 1
ORDER BY prefix, course_num, section_num""".format(semester, year, course_id))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: str username,
    #          int course_id
    # returns: list of sections being taught
    #
    def get_current_sections(self, username, course_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT section_num FROM course.people_to_offers_link AS pol
LEFT JOIN course.course_offerings AS co
ON pol.offer_id = co.offer_id
LEFT JOIN active_courses AS ac
ON ac.course_id = co.course_id
WHERE
 co.course_id = '{}; AND
 employee_username = '{}' AND
 co.semester = '{}' AND
 co.year = '{}' AND
 ac.active = 1
ORDER BY section_num""".format(course_id, username, self.get_current_semester(), self.get_current_year()))

        if cursor.rowcount > 0:
            return [section.get('section_num') for section in cursor.fetchall()]
        else:
            return []

    #=============================================
    # accepts: str username,
    #          int course_id
    # returns: list of sections being taught
    #
    def get_sections_taught(self,username, course_id, semester, year):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT section_num FROM course.people_to_offers_link AS pol
LEFT JOIN course.course_offerings AS co
ON pol.offer_id = co.offer_id
WHERE
 co.course_id = '{}' AND
 employee_username = '{}' AND
 co.semester = '{}' AND
 co.year = '{}'
ORDER BY section_num""".format(course_id, username, semester, year))

        if cursor.rowcount > 0:
            return [section.get('section_num') for section in cursor.fetchall()]
        else:
            return []


    #=============================================
    # accepts: int course_id,
    #          str semester,
    #          int year
    # returns: list of sections for this course
    #
    def get_course_sections(self, course_id, semester, year):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT prefix, alt_prefix, course_num, section_num, offer_id FROM course.course_offerings AS o
LEFT JOIN grade_collection.active_courses AS ac
ON ac.course_id = o.course_id
LEFT JOIN course.course_list AS cl
ON cl.course_id = o.course_id
WHERE
 o.course_id = '{}' AND
 o.semester = '{}' AND
 o.year = '{}' AND
 ac.active = 1 AND
 CONCAT(prefix,"_",course_num,"_",section_num)
 NOT IN
 (SELECT CONCAT(es.prefix,"_", es.course_num,"_", es.section_num) FROM grade_collection.excluded_sections AS es)
ORDER BY section_num""".format(course_id, semester, year))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: int course_id,
    #          str semester,
    #          int year
    # returns: list of sections for this course
    #
    def get_sections_for_term(self,semester, year):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT prefix, alt_prefix, course_num, section_num, offer_id FROM course.course_offerings AS o
LEFT JOIN grade_collection.active_courses AS ac
ON ac.course_id = o.course_id
LEFT JOIN course.course_list AS cl
ON cl.course_id = o.course_id
WHERE
 o.semester = '{}' AND
 o.year = '{}' AND
 ac.active = 1 AND
 CONCAT(prefix,"_",course_num,"_",section_num)
 NOT IN
 (SELECT CONCAT(es.prefix,"_", es.course_num,"_", es.section_num) FROM grade_collection.excluded_sections AS es)
ORDER BY prefix,course_num,section_num""".format(semester, year))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: str username,
    #          str semester,
    #          str year
    # returns: list of courses being coordinated
    #
    def get_coord_courses(self, username, semester, year):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT cl.course_id, cl.prefix, cl.alt_prefix, cl.course_num FROM course.course_coordinators AS cc
LEFT JOIN course.course_list AS cl
ON cl.course_id = cc.course_id
LEFT JOIN active_courses AS ac
ON ac.course_id = cc.course_id
WHERE
 employee_username = '{}' AND
 cc.semester = '{}' AND
 cc.year = '{}' AND
 ac.active = 1
ORDER BY prefix, course_num""".format(username, semester, year))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int offer_id
    # returns: list of students in the section
    #
    def get_roll(self, offer_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("SELECT cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name FROM rolls.rolls AS cr LEFT JOIN Banner_info.student_info AS si ON si.xid = cr.xid WHERE term_code = {} AND (subject_code = '{}' OR subject_code = '{}') AND course_number = '{}' AND section_number = '{}' ORDER BY last_name, first_name, xid".format(term_code, offer.get("prefix"), offer.get("alt_prefix"), offer.get("course_num"), offer.get("section_num")))

            if cursor.rowcount > 0:
                return cursor.fetchall()
            else:
                return []
        else:
            return []


    #=============================================
    # accepts: int offer_id
    # returns: list of students in the course
    #
    def get_current_course_roll(self, course_id):
        cursor = self.get_cursor()

        course_info = self.get_course_info(course_id)

        if len(course_info) > 0:
            # get list of students
            term_code = self.get_term_code(self.get_current_semester(), self.get_current_year())
            cursor.execute("SELECT cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name FROM rolls.rolls AS cr LEFT JOIN Banner_info.student_info AS si ON si.xid = cr.xid WHERE term_code = {} AND (subject_code = '{}' OR subject_code = '{}') AND course_number = '{}' ORDER BY xid".format(term_code, course_info.get("prefix"), course_info.get("alt_prefix"), course_info.get("course_num")))

            if cursor.rowcount > 0:
                return cursor.fetchall()
            else:
                return []
        else:
            return []


    #=============================================
    # accepts: int offer_id,
    #          int exam_id
    # returns: list of (student data, whether they were absent for the exam)
    #
    def get_exam_absences(self, offer_id, exam_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("SELECT cr.xid, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, (SELECT COUNT(ea.xid) FROM exam_absences AS ea WHERE ea.xid = cr.xid AND ea.exam_id = '{}') AS absent FROM rolls.rolls AS cr LEFT JOIN Banner_info.student_info AS si ON si.xid = cr.xid WHERE term_code = {} AND (subject_code = '{}' OR subject_code = '{}') AND course_number = '{}' AND section_number = '{}' ORDER BY last_name, first_name, xid".format(exam_id, term_code, offer.get('prefix'), offer.get('alt_prefix'), offer.get('course_num'), offer.get('section_num')))

            if cursor.rowcount > 0:
                return cursor.fetchall()
            else:
                return []
        else:
            return []


    #=============================================
    # accepts: int xid,
    #          int exam_id
    # returns: True if the student was absent; False otherwise
    #
    def was_student_absent(self, xid, exam_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT COUNT(xid) AS absent FROM exam_absences WHERE xid = '{}' AND exam_id = '{}'".format(xid, exam_id))

        if cursor.fetchone().get('absent') == 1:
            return True
        else:
            return False


    #=============================================
    # accepts: int offer_id,
    #          int exam_id,
    #          list absences
    # returns: True if the absences were saved, False otherwise
    #
    def update_exam_absences(self, offer_id, exam_id, absences):
        cursor = self.get_cursor()

        roll = self.get_roll(offer_id)

        for student in roll:
            if student.get('xid') in absences:
                # mark them as absent
                cursor.execute("INSERT INTO exam_absences (xid, exam_id) VALUES ('{}', '{}') ON DUPLICATE KEY UPDATE xid = xid".format(student.get('xid'), exam_id))
            else:
                # make sure they aren't marked as absent
                cursor.execute("DELETE FROM exam_absences WHERE xid = '{}' AND exam_id = '{}'".format(student.get('xid'), exam_id))

        return True # we really should be checking to see if everything succeeded


    #=============================================
    # accepts: str username
    # returns: str xid (or empty string if not found)
    #
    def get_xid_from_username(self, username):
        cursor = self.get_cursor()
        cursor.execute("SELECT xid FROM Banner_info.student_info WHERE username = '{}'".format(username))

        if cursor.rowcount == 1:
            return cursor.fetchone().get('xid')
        else:
            return ""


    #=============================================
    # accepts: int offer_id
    # returns: int course_id
    #
    def get_course_id_from_offer_id(self, offer_id):
        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            return offer.get('course_id')
        else:
            return 0


    #=============================================
    # accepts: int course_id,
    #          str semester,
    #          str year
    # returns: list of exams
    #
    def get_exams(self, course_id, semester, year):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM exams WHERE course_id = '{}' AND semester = '{}' AND year = '{}' ORDER BY date_given".format(course_id, semester, year))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: int exam_id
    # returns: dict of exam info
    #
    def get_exam(self, exam_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM exams WHERE exam_id = '{}'".format(exam_id))

        if cursor.rowcount == 1:
            return cursor.fetchone()
        else:
            return {"course_id": 0, "semester": "", "year": 0, "title": "", "date_given": "", "grades_due": "", "weight": 0, "key_version": ""}


    #=============================================
    # accepts: int course_id
    # returns: list of exams
    #
    def get_current_exams(self, course_id):
        return self.get_exams(course_id, self.get_current_semester(), self.get_current_year())


    #=============================================
    # accepts: int exam_id
    # returns: (bool status, msg)
    #
    def delete_exam(self,exam_id):
        cursor = self.get_cursor()

        # TODO: we should check that there aren't any student responses associated with this exam
        cursor.execute("DELETE FROM exams WHERE exam_id = '{}'".format(exam_id))

        return (True, "") # we really should be checking to see if it succeeded


    #=============================================
    # accepts: str title,
    #          str date_given,
    #          str grades_due,
    #          float weight
    # returns: (bool status, msg)
    #
    def add_exam(self, course_id, title, date_given, grades_due, weight):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO exams (semester, year, course_id, title, date_given, grades_due, weight) VALUES ('{}', '{}', '{}', '{}', '{}', '{}', '{}')".format(self.get_current_semester(), self.get_current_year(), course_id, title, date_given, grades_due, weight))

        return (True, "") # we really should be checking to see if it succeeded


    #=============================================
    # accepts: int exam_id,
    #          str title,
    #          str date_given,
    #          str grades_due,
    #          float weight
    # returns: (bool status, msg)
    #
    def update_exam(self, exam_id, title, date_given, grades_due, weight):
        cursor = self.get_cursor()

        cursor.execute("UPDATE exams SET title = '{}', date_given = '{}', grades_due = '{}', weight = '{}' WHERE exam_id = '{}'".format(title, date_given, grades_due, weight, exam_id))

        return (True, "") # we really should be checking to see if it succeeded


    #=============================================
    # accepts: int offer_id
    # returns: info about offer/course
    #
    def get_offer_info(self, offer_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT co.course_id, cl.prefix, cl.alt_prefix, cl.course_num, section_num, semester, year FROM course.course_offerings AS co
LEFT JOIN course.course_list AS cl
ON cl.course_id = co.course_id
WHERE co.offer_id = '{}'""".format(offer_id))

        if cursor.rowcount == 1:
            return cursor.fetchone()
        else:
            return {}


    #=============================================
    # accepts: str username,
    #          int offer_id
    # returns: true
    #
    def is_teaching(self, username, offer_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT COUNT(employee_username) AS valid FROM course.course_offerings AS co
LEFT JOIN course.people_to_offers_link AS pol
ON pol.offer_id = co.offer_id
WHERE
 employee_username = '{}' AND co.offer_id = '{}'""".format(username, offer_id))

        valid = cursor.fetchone().get('valid')
        if valid == 1:
            return True
        else:
            return False


    #=============================================
    # accepts: int course_id
    # returns: info about course
    #
    def get_course_info(self, course_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT course_id, prefix, alt_prefix, course_num FROM course.course_list WHERE course_id = '{}'""".format(course_id))

        if cursor.rowcount == 1:
            return cursor.fetchone()
        else:
            return {}


    #=============================================
    # accepts: int offer_id,
    #          int exam_id
    # returns: list of responses
    #
    def get_mc_responses(self, offer_id, exam_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("""
SELECT
 cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, version, responses, marks, num_right, num_wrong, num_blank, num_mismarked, graded, points_earned, (SELECT COUNT(xid) FROM exam_absences AS ea WHERE ea.exam_id = '{0}' AND ea.xid = cr.xid) AS absent
FROM
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
LEFT JOIN
 mc_responses AS r ON r.xid = cr.xid AND exam_id = '{0}'
WHERE
 term_code = {1} AND
 (subject_code = '{2}' or subject_code = '{3}') AND
 course_number = '{4}' AND
 section_number = '{5}'
ORDER BY last_name, first_name, xid""".format(exam_id, term_code, offer.get('prefix'), offer.get('alt_prefix'), offer.get('course_num'), offer.get('section_num')))

            if cursor.rowcount > 0:
                return cursor.fetchall()
            else:
                return []
        else:
            return []


    #=============================================
    # accepts: int exam_id
    # returns: list of responses
    #
    def get_course_mc_responses(self, exam_id):
        cursor = self.get_cursor()

        exam_info = self.get_exam(exam_id)
        course_info = self.get_course_info(exam_info.get('course_id'))

        # get list of students
        term_code = self.get_term_code(exam_info.get('semester'), exam_info.get('year'))
        cursor.execute("""
SELECT
 cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, version, responses, marks, num_right, num_wrong, num_blank, num_mismarked, graded, points_earned, (SELECT COUNT(xid) FROM exam_absences AS ea WHERE ea.exam_id = '{}' AND ea.xid = cr.xid) AS absent
FROM
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
LEFT JOIN
 mc_responses AS r ON r.xid = cr.xid AND exam_id = '{}'
WHERE
 term_code = {} AND
 (subject_code = '{}' OR subject_code = '{}') AND
 course_number = '{}'
ORDER BY last_name, first_name, xid""".format(exam_id, exam_id, term_code, course_info.get('prefix'), course_info.get('alt_prefix'), course_info.get('course_num')))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: int offer_id,
    #          int exam_id
    # returns: list of FR scores
    #
    def get_student_fr_scores(self, xid, exam_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT version, points_earned, s.question_num, q.display_order FROM fr_scores AS s LEFT JOIN fr_questions AS q ON q.exam_id = s.exam_id AND q.question_num = s.question_num WHERE xid = '{}' AND s.exam_id = '{}' ORDER BY q.display_order".format(xid, exam_id))

        version = ""
        fr_scores = []
        points_earned = 0

        if cursor.rowcount > 0:
            for row in cursor.fetchall():
                version = row.get('version')
                fr_scores.append(row.get('points_earned'))

            try:
                points_earned = sum(fr_scores)
            except:
                points_earned = 0

        return {"version": version, "fr_scores": fr_scores, "points_earned": points_earned}


    #=============================================
    # accepts: int offer_id,
    #          int exam_id
    # returns: list of FR scores
    #
    def get_fr_scores(self, offer_id, exam_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("""
SELECT
 cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, (SELECT COUNT(xid) FROM exam_absences AS ea WHERE ea.exam_id = '{}' AND ea.xid = cr.xid) AS absent
FROM
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
WHERE
 term_code = {} AND
 (subject_code = '{}' OR subject_code = '{}') AND
 course_number = '{}' AND
 section_number = '{}'
ORDER BY last_name, first_name, xid""".format(exam_id, term_code, offer.get('prefix'), offer.get('alt_prefix'), offer.get('course_num'), offer.get('section_num')))

            if cursor.rowcount > 0:
                students = cursor.fetchall()
            else:
                students = []


            # get the FR scores for each user
            for student in students:
                # TODO: replace with get_student_fr_scores function above
                cursor.execute("SELECT version, points_earned, s.question_num, q.display_order FROM fr_scores AS s LEFT JOIN fr_questions AS q ON q.exam_id = s.exam_id AND q.question_num = s.question_num WHERE xid = '{}' AND s.exam_id = '{}' ORDER BY q.display_order".format(student.get('xid'), exam_id))
                scores = []
                student["version"] = ""
                if cursor.rowcount > 0:
                    for score in cursor.fetchall():
                        student["version"] = score.get('version')
                        scores.append(score.get('points_earned'))

                student["scores"] = scores
                try:
                    student["points_earned"] = sum(scores)
                except:
                    # the scores may all be nulls
                    student["points_earned"] = 0

            return students
        else:
            return []

    #=============================================
    # accepts: int offer_id,
    #          int exam_id
    # returns: list of FR scores
    #
    def get_fr_scores_version(self, offer_id, exam_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("""
SELECT
 cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, (SELECT COUNT(xid) FROM exam_absences AS ea WHERE ea.exam_id = '{}' AND ea.xid = cr.xid) AS absent
FROM
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
WHERE
 term_code = {} AND
 (subject_code = '{}' OR subject_code = '{}') AND
 course_number = '{}' AND
 section_number = '{}'
ORDER BY last_name, first_name, xid""".format(exam_id, term_code, offer.get('prefix'), offer.get('alt_prefix'), offer.get('course_num'), offer.get('section_num')))

            if cursor.rowcount > 0:
                students = cursor.fetchall()
            else:
                students = []


            # get the FR scores for each user
            for student in students:
                # TODO: replace with get_student_fr_scores function above
                #cursor.execute("SELECT version, points_earned, s.question_num, q.display_order FROM fr_scores AS s LEFT JOIN fr_questions AS q ON q.exam_id = s.exam_id AND q.question_num = s.question_num WHERE xid = '{}' AND s.exam_id = '{}' ORDER BY q.display_order".format(student.get('xid'), exam_id))
                #This query added 9-25-18 by KDH to replace the above query which didn't take into account different fr versions
                #cursor.execute("SELECT s.version, s.points_earned, s.question_num, vq.key_version_question_num, vq.display_order FROM fr_scores AS s LEFT JOIN version_fr_questions vq ON vq.exam_id = s.exam_id AND vq.question_num = s.question_num AND vq.version = s.version WHERE xid = '{}' AND s.exam_id = '{}' ORDER BY CAST(vq.key_version_question_num AS UNSIGNED), display_order", (student.get('xid'), exam_id))
                #KDH replaced the above query with the following to fix the sorting error, 10-23-18
                cursor.execute("SELECT s.version, s.points_earned, s.question_num, vq.key_version_question_num, q.display_order FROM `fr_scores` AS s LEFT JOIN version_fr_questions vq ON vq.exam_id = s.exam_id AND vq.question_num = s.question_num AND vq.version = s.version LEFT JOIN fr_questions AS q ON q.exam_id = s.exam_id AND q.question_num = vq.key_version_question_num WHERE xid = '{}' AND s.exam_id = '{}' ORDER BY q.display_order".format(student.get('xid'), exam_id))
                scores = []
                student["version"] = ""
                if cursor.rowcount > 0:
                    for score in cursor.fetchall():
                        student["version"] = score.get('version')
                        scores.append(score.get('points_earned'))

                student["scores"] = scores
                try:
                    student["points_earned"] = sum(scores)
                except:
                    # the scores may all be nulls
                    student["points_earned"] = 0

            return students
        else:
            return []


    #=============================================
    # accepts: int exam_id
    # returns: list of FR scores
    #
    def get_course_fr_scores(self, exam_id):
        cursor = self.get_cursor()

        exam = self.get_exam(exam_id)
        course = self.get_course_info(exam.get('course_id'))

        if len(course) > 0:
            # get list of students
            term_code = self.get_term_code(exam.get('semester'), exam.get('year'))
            cursor.execute("""
SELECT
 cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, (SELECT COUNT(xid) FROM exam_absences AS ea WHERE ea.exam_id = '{}' AND ea.xid = cr.xid) AS absent
FROM
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
WHERE
 term_code = {} AND
 (subject_code = '{}' OR subject_code = '{}') AND
 course_number = '{}'
ORDER BY last_name, first_name, xid""".format(exam_id, term_code, course.get('prefix'), course.get('alt_prefix'), course.get('course_num')))

            if cursor.rowcount > 0:
                students = cursor.fetchall()
            else:
                students = []


            # get the FR scores for each user
            for student in students:
                # TODO: replace with get_student_fr_scores
                cursor.execute("SELECT version, points_earned, s.question_num, q.display_order FROM fr_scores AS s LEFT JOIN fr_questions AS q ON q.exam_id = s.exam_id AND q.question_num = s.question_num WHERE xid = '{}' AND s.exam_id = '{}' ORDER BY q.display_order".format(student.get('xid'), exam_id))
                scores = []
                student["version"] = ""
                if cursor.rowcount > 0:
                    for score in cursor.fetchall():
                        student["version"] = score.get('version')
                        scores.append(score.get('points_earned'))

                student["scores"] = scores
                try:
                    student["points_earned"] = sum(scores)
                except:
                    # the scores may all be nulls
                    student["points_earned"] = 0

            return students
        else:
            return []


    #=============================================
    # accepts: int exam_id
    # returns: list of FR scores
    #
    def get_course_fr_scores_version(self, exam_id):
        cursor = self.get_cursor()

        exam = self.get_exam(exam_id)
        course = self.get_course_info(exam.get('course_id'))

        if len(course) > 0:
            # get list of students
            term_code = self.get_term_code(exam.get('semester'), exam.get('year'))
            cursor.execute("""
SELECT
 cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, (SELECT COUNT(xid) FROM exam_absences AS ea WHERE ea.exam_id = '{}' AND ea.xid = cr.xid) AS absent
FROM
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
WHERE
 term_code = {} AND
 (subject_code = '{}' OR subject_code = '{}') AND
 course_number = '{}'
ORDER BY last_name, first_name, xid""".format(exam_id, term_code, course.get('prefix'), course.get('alt_prefix'), course.get('course_num')))

            if cursor.rowcount > 0:
                students = cursor.fetchall()
            else:
                students = []


            # get the FR scores for each user
            for student in students:
                # TODO: replace with get_student_fr_scores
                #cursor.execute("SELECT version, points_earned, s.question_num, q.display_order FROM fr_scores AS s LEFT JOIN fr_questions AS q ON q.exam_id = s.exam_id AND q.question_num = s.question_num WHERE xid = '{}' AND s.exam_id = '{}' ORDER BY q.display_order".format(student.get('xid'), exam_id))
                #This query added 9-25-18 by KDH to replace the above query which didn't take into account different fr versions
                #cursor.execute("SELECT s.version, s.points_earned, s.question_num, vq.key_version_question_num, vq.display_order FROM fr_scores AS s LEFT JOIN version_fr_questions vq ON vq.exam_id = s.exam_id AND vq.question_num = s.question_num AND vq.version = s.version WHERE xid = '{}' AND s.exam_id = '{}' ORDER BY CAST(vq.key_version_question_num AS UNSIGNED), vq.display_order".format(student.get('xid'), exam_id))
                #KDH replaced the above query with the following to fix the sorting error, 10-23-18
                cursor.execute("SELECT s.version, s.points_earned, s.question_num, vq.key_version_question_num, q.display_order FROM `fr_scores` AS s LEFT JOIN version_fr_questions vq ON vq.exam_id = s.exam_id AND vq.question_num = s.question_num AND vq.version = s.version LEFT JOIN fr_questions AS q ON q.exam_id = s.exam_id AND q.question_num = vq.key_version_question_num WHERE xid = '{}' AND s.exam_id = '{}' ORDER BY q.display_order".format(student.get('xid'), exam_id))
                scores = []
                student["version"] = ""
                if cursor.rowcount > 0:
                    for score in cursor.fetchall():
                        student["version"] = score.get('version')
                        scores.append(score.get('points_earned'))

                student["scores"] = scores
                try:
                    student["points_earned"] = sum(scores)
                except:
                    # the scores may all be nulls
                    student["points_earned"] = 0

            return students
        else:
            return []

    #=============================================
    # accepts: int offer_id,
    #          int exam_id,
    #          optional dict of offer info
    # returns: list of responses
    #
    def get_overall_scores(self, offer_id, exam_id, offer=""):
        cursor = self.get_cursor()

        if offer=="":
            offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("""
SELECT xid, reg_stat_code, last_name, first_name, absent, mc_version, mc_points, fr_version, fr_points, (IFNULL(mc_points, 0) + IFNULL(fr_points, 0)) AS total_points FROM
(SELECT
 cr.xid, reg_stat_code, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, version AS mc_version, points_earned AS mc_points, (SELECT COUNT(xid) FROM exam_absences AS ea WHERE ea.exam_id = '{0}' AND ea.xid = cr.xid) AS absent, CONCAT_WS(",", (SELECT DISTINCT version FROM fr_scores AS fr WHERE fr.xid = cr.xid AND fr.exam_id = '{0}')) AS fr_version, (SELECT SUM(fr.points_earned) FROM fr_scores AS fr WHERE fr.xid = cr.xid AND fr.exam_id = '{0}') AS fr_points, 0 AS total_points
FROM
 rolls.full_rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
LEFT JOIN
 mc_responses AS r ON r.xid = cr.xid AND exam_id = '{0}'
WHERE
 term_code = {1} AND
 (subject_code = '{2}' OR subject_code = '{3}') AND
 course_number = '{4}' AND
 section_number = '{5}') AS temp
ORDER BY last_name, first_name, xid""".format(exam_id, term_code, offer.get('prefix'), offer.get('alt_prefix'), offer.get('course_num'), offer.get('section_num')))

            if cursor.rowcount > 0:
                return cursor.fetchall()
            else:
                return []
        else:
            return []

    #start TODO: delete this function
    #=============================================
    # accepts: int offer_id,
    #          list of ints exam_ids,
    #          optional dict of offer info
    # returns: list of responses
    #
    def get_overall_scores_wo_absences(self, offer_id, exam_id, offer=""):
        cursor = self.get_cursor()

        if offer=="":
            offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("""
SELECT xid, reg_stat_code, last_name, first_name, mc_version, mc_points, fr_version, fr_points, (IFNULL(mc_points, 0) + IFNULL(fr_points, 0)) AS total_points FROM
(SELECT
 cr.xid, reg_stat_code, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, version AS mc_version, points_earned AS mc_points, CONCAT_WS(",", (SELECT DISTINCT version FROM fr_scores AS fr WHERE fr.xid = cr.xid AND fr.exam_id IN {0})) AS fr_version, (SELECT SUM(fr.points_earned) FROM fr_scores AS fr WHERE fr.xid = cr.xid AND fr.exam_id IN {0}) AS fr_points, 0 AS total_points
FROM
 rolls.full_rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
LEFT JOIN
 mc_responses AS r ON r.xid = cr.xid AND exam_id IN {0}
WHERE
 term_code = {1} AND
 (subject_code = '{2}' OR subject_code = '{3}') AND
 course_number = '{4}' AND
 section_number = '{5}') AS temp
ORDER BY last_name, first_name, xid""".format(tuple([912,913]), term_code, offer.get('prefix'), offer.get('alt_prefix'), offer.get('course_num'), offer.get('section_num')))

            if cursor.rowcount > 0:
                return cursor.fetchall()
            else:
                return []
        else:
            return []
    #end


    #=============================================
    # accepts: int exam_id
    # returns: list of responses
    #
    def get_course_overall_scores(self, exam_id):
        cursor = self.get_cursor()

        exam = self.get_exam(exam_id)
        course = self.get_course_info(exam.get('course_id'))

        if len(course) > 0:
            # get list of students
            term_code = self.get_term_code(exam.get('semester'), exam.get('year'))
            cursor.execute("""
SELECT xid, last_name, first_name, absent, mc_version, mc_points, IF(LENGTH(fr_version) = 0, NULL, fr_version) AS fr_version, fr_points, (IFNULL(mc_points, 0) + IFNULL(fr_points, 0)) AS total_points FROM
(SELECT
 cr.xid, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, version AS mc_version, points_earned AS mc_points, (SELECT COUNT(xid) FROM exam_absences AS ea WHERE ea.exam_id = '{}' AND ea.xid = cr.xid) AS absent, CONCAT_WS(",", (SELECT DISTINCT version FROM fr_scores AS fr WHERE fr.xid = cr.xid AND fr.exam_id = '{}')) AS fr_version, (SELECT SUM(fr.points_earned) FROM fr_scores AS fr WHERE fr.xid = cr.xid AND fr.exam_id = '{}') AS fr_points, 0 AS total_points
FROM
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
LEFT JOIN
 mc_responses AS r ON r.xid = cr.xid AND exam_id = '{}'
WHERE
 term_code = {} AND
 (subject_code = '{}' OR subject_code = '{}') AND
 course_number = '{}') AS temp
ORDER BY last_name, first_name, xid""".format(exam_id, exam_id, exam_id, exam_id, term_code, course.get('prefix'), course.get('alt_prefix'), course.get('course_num')))

            if cursor.rowcount > 0:
                return cursor.fetchall()
            else:
                return []
        else:
            return []


    #=============================================
    # accepts: int offer_id
    # returns: list of term summary data
    #
    def get_term_summary(self, offer_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get exam info
            exams = self.get_exams(offer.get('course_id'), offer.get('semester'), offer.get('year'))

            for exam in exams:
                exam_key_version = self.get_exam_key_version(exam.get('exam_id'))

                exam["num_mc_questions"] = len(self.get_mc_key(exam.get('exam_id'), exam_key_version))
                exam["num_fr_questions"] = len(self.get_fr_questions(exam.get('exam_id'), exam_key_version))

            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("""
SELECT
 cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name
FROM
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
WHERE
 term_code = {} AND
 (subject_code = '{}' OR subject_code = '{}') AND
 course_number = '{}' AND
 section_number = '{}'
ORDER BY last_name, first_name, xid""".format(term_code, offer.get('prefix'), offer.get('alt_prefix'), offer.get('course_num'), offer.get('section_num')))

            if cursor.rowcount > 0:
                students = cursor.fetchall()

                scores = dict([(student.get('xid'), {}) for student in students])

                for exam in exams:
                    exam_id=exam.get('exam_id')
                    exam_data = self.get_overall_scores(offer_id, exam_id)

                    # print('-----------\n');
                    # print(offer_id, exam.get('exam_id'), exam_data);
                    # print('-----------\n');

                    for score in exam_data:
                        if score.get('reg_stat_code') in ['RW','RM','RE']:
                            scores[score.get('xid')][exam_id] = score

               # print('==========\n');
               # print(scores);
               # print('==========\n');

                # combine the score data back with the list of students
                data = []
                for student in students:
                    data.append({"student": student, "exam_scores": scores[student.get('xid')]})
#                print('-----------\n');
#                print(exams);
#                print('\n+++++++++++\n');
#                print(data);
#                print('\n===========\n');
                return {"exams": exams, "scores": data}
            else:
                return {}
        else:
            return {}

    #=============================================
    # accepts: int offer_id
    # returns: list of term summary data including dropped students
    #
    def get_term_summary_full(self, offer_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get exam info
            exams = self.get_exams(offer.get('course_id'), offer.get('semester'), offer.get('year'))

            for exam in exams:
                exam_key_version = self.get_exam_key_version(exam.get('exam_id'))

                exam["num_mc_questions"] = len(self.get_mc_key(exam.get('exam_id'), exam_key_version))
                exam["num_fr_questions"] = len(self.get_fr_questions(exam.get('exam_id'), exam_key_version))

            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("""
SELECT
 cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, reg_stat_code
FROM
 rolls.full_rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
WHERE
 term_code = {} AND
 subject_code = '{}' AND
 course_number = {} AND
 section_number = {}
ORDER BY last_name, first_name, xid""".format(term_code, offer.get('prefix'), offer.get('course_num'), offer.get('section_num')))

            if cursor.rowcount > 0:
                students = cursor.fetchall()

                scores = dict([(student.get('xid'), {}) for student in students])

                for exam in exams:
                    #TODO redefine get_overall_scores with optional arg of offer_info
                    exam_data = self.get_overall_scores(offer_id, exam.get('exam_id'),offer)

                    for score in exam_data:
                        scores[score.get('xid')][exam.get('exam_id')] = score

                # combine the score data back with the list of students
                data = []
                for student in students:
                    data.append({"student": student, "exam_scores": scores.get(student.get('xid'))})

                return {"exams": exams, "scores": data}
            else:
                return {}
        else:
            return {}



    #=============================================
    # accepts: dict exam info
    # returns: True
    #
    def is_final_exam(self, exam):
        # THIS IS A HACK for Fall 2013
        if exam.get('title').strip() == "Final Exam":
            return True
        else:
            return False


    #=============================================
    # accepts: dict exam info
    # returns: True
    #
    def is_regular_test(self, exam):
        # THIS IS A HACK for Fall 2013
        if exam.get('title')[0:4] in ["Test","Exam"] or exam.get('title')[0:7] == "Midterm" or exam.get('title') in ["Previous Course Average", "1040 Course Average"]:
        #if exam.get('title')[0:3] != "BST" and exam.get('title') != "Final Exam":
            return True
        else:
            return False


    #=============================================
    # accepts: int course_id,
    #          list exams for the course,
    #          list exam scores data
    # returns: tuple containing
    #            str calculated letter grade,
    #            float weighted_test_average,
    #            float grade,
    #            float min_test_grade,
    #            str note (about eligibility to pass)
    #
    def get_calculated_letter_grade(self, course_id, exams, exam_scores):
        math1010id=self.get_course_id('math',1010) #1
        math1020id=self.get_course_id('math',1020) #2
        math1040id=self.get_course_id('math',1040) #91
        math1060id=self.get_course_id('math',1060) #6
        math1070id=self.get_course_id('math',1070) #7
        math1080id=self.get_course_id('math',1080) #8
        math2070id=self.get_course_id('math',2070) #23
        math9850id=self.get_course_id('math',9850) #87
        stat2220id=self.get_course_id('stat',2220) #185
        stat2300id=self.get_course_id('stat',2300) #226
        stat3090id=self.get_course_id('stat',3090) #225

        # print(exam_scores)
        # THIS IS A HACK for Fall 2013
        letter = "?"
        weighted_test_average = 0.0
        grade = 0.0
        min_test_grade = 0.0
        note = ""

        course_id = int(course_id)

        if len(exams) == 0:
            return ("", weighted_test_average, grade, min_test_grade, note)

        final_exam = []
        tests = []
        others = []

        # see if we should replace the lowest test grade with the final
        for exam in exams:
            if exam_scores.get(exam.get('exam_id')):
                exam_score = float(exam_scores.get(exam.get('exam_id')).get('total_points'))
                exam_weight = float(exam.get('weight'))

                if self.is_final_exam(exam):
                    # there should only be 1 of these
                    final_exam.append((exam_score, exam_weight))
                elif self.is_regular_test(exam):
                    tests.append((exam_score, exam_weight))
                else:
                    others.append((exam_score, exam_weight))

        if len(tests)>0 and len(final_exam)>0:
            min_test_grade = min(tests, key=itemgetter(0))[0]
            final_exam_grade = final_exam[0][0]

            if min_test_grade < final_exam_grade:
                # replace the low test grade with the grade from the final
                for i in range(0, len(tests)):
                    if tests[i][0] == min_test_grade:
                        test_weight = tests[i][1]
                        tests.pop(i)
                        tests.insert(i, (final_exam_grade, test_weight))
                        break

            # see if they are eligibile to pass the class
            combined_test_scores = sum([item[0] * item[1] for item in tests]) + final_exam_grade * final_exam[0][1]
            combined_test_weights = sum([item[1] for item in tests]) + final_exam[0][1]

            weighted_test_average = combined_test_scores / combined_test_weights

            grade = sum([item[0] * item[1] for item in others]) + sum([item[0] * item[1] for item in tests]) + final_exam_grade * final_exam[0][1]

            # assign letter grades using standard rounding
            # bump letter grade up for those who got a 59, 69, 79 or 89 on the exam
            if grade >= 89.5 or (grade >= 89 and final_exam_grade >= 90):
                letter = "A"
            elif grade >= 79.5 or (grade >= 79 and final_exam_grade >= 80):
                letter = "B"
            elif grade >= 69.5 or (grade >= 69 and final_exam_grade >= 70):
                letter = "C"
            elif grade >= 59.5 or (grade >= 59 and final_exam_grade >= 60):
                letter = "D"
            else:
                letter = "F"

            #check if they are eligible to pass the class
            min_passing_grade = 59.5

            if course_id == math1040id:
                min_passing_grade = 64.5
                # min_passing_grade changed from 63.5 to 64.5 by KDH on 12-13-21


            # if course_id not in [1, 2, 7, 8, 23, 185, 225, 226]:
            if course_id not in [math1010id,math1020id,math1070id,math1080id,
                                 math2070id,stat2220id,stat3090id,stat2300id]:
                # MATH 2070, and STAT 2300 do not require this
                # MATH 1020 no longer requires this either, KDH added 2018-12-15
                # MATH 1070 no longer requires this either, KDH added 2021-12-13
                # MATH 1080 no longer requires this either, PW added 2021-12-13
                # STAT 2220 no longer requires this either, PW added 2022-05-11
                # STAT 3090 (225) no longer requires this either, KDH added 2022-12-06
                if final_exam[0][0] < min_passing_grade and weighted_test_average < min_passing_grade:
                    letter = "F"
                    note = "Ineligible to pass the class."

            if course_id == math1040id:
                # MATH 1040: they need a 70% to pass; grades are P or NP
                if letter in ["A", "B", "C"]:
                    letter = "P"
                else:
                    letter = "NP"

        return (letter, weighted_test_average, grade, min_test_grade, note)


    #=============================================
    # accepts: int offer_id
    # returns: list of end of term data
    #
    def get_term_end_data(self, offer_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("""
SELECT
 cr.xid, username, last_name, IF( preferred_name IS NULL, first_name, preferred_name) AS first_name, num_absences, cr.univ_letter_grade, letter_grade
FROM
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
LEFT JOIN
 end_of_term AS eot
ON
 eot.xid = cr.xid AND
 eot.course_id = '{}' AND
 eot.semester = '{}' AND
 eot.year = '{}'
WHERE
 term_code = {} AND
 (subject_code = '{}' OR subject_code = '{}') AND
 course_number = '{}' AND
 section_number = '{}'
ORDER BY last_name, first_name, xid""".format(offer.get('course_id'), offer.get('semester'), offer.get('year'), term_code, offer.get('prefix'), offer.get('alt_prefix'), offer.get('course_num'), offer.get('section_num')))

            if cursor.rowcount > 0:
                return cursor.fetchall()
            else:
                return []
        else:
            return []


    #=============================================
    # accepts: int offer_id,
    #          str xid,
    #          int number of absences,
    #          str letter grade
    # returns: True if the data was saved; False otherwise
    #
    def store_term_end_data(self, offer_id, xid, num_absences, letter_grade):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        cursor.execute("INSERT INTO end_of_term (xid, course_id, semester, year, num_absences, letter_grade) VALUES ('{}', '{}', '{}', '{}', '{}', '{}') ON DUPLICATE KEY UPDATE num_absences = '{}', letter_grade = '{}'".format(xid, offer.get('course_id'), offer.get('semester'), offer.get('year'), num_absences, letter_grade, num_absences, letter_grade))

        return True # we really should be checking to see if this succeeded or not


    #=============================================
    # accepts: str xid
    # returns: list of scores for this student
    #
    def get_bst_scores(self, xid):

        # just in case we have some responses without XIDs (this should never happen)
        if xid == "":
            return []

        cursor = self.get_cursor()

        # get list of scores
        exam_id_1040 = self.get_setting("1040_bst_exam_id")
        exam_id_1060 = self.get_setting("1060_bst_exam_id")
        exam_id_1070 = self.get_setting("1070_bst_exam_id")
        exam_id_1080 = self.get_setting("1080_bst_exam_id")

        cursor.execute("""
SELECT
 prefix, alt_prefix, course_num, points_earned AS score
FROM
 mc_responses AS r LEFT JOIN
 exams AS e ON e.exam_id = r.exam_id LEFT JOIN
 course.course_list AS cl ON cl.course_id = e.course_id
WHERE
 xid = '{}' AND
 r.exam_id IN ('{}', '{}', '{}', '{}')
ORDER BY course_num""".format(xid, exam_id_1040, exam_id_1060, exam_id_1070, exam_id_1080))

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: int offer_id,
    #          int exam_id
    # returns: true if the responses were reverted; False otherwise
    #
    def revert_to_scantron_responses(self, offer_id, exam_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            # get list of students
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("""
UPDATE
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
LEFT JOIN
 mc_responses AS r ON r.xid = cr.xid AND exam_id = '{}'
SET
 version = orig_scantron_version,
 responses = orig_scantron_responses,
 num_right = 0,
 num_wrong = 0,
 num_blank = 0,
 num_mismarked = 0,
 graded = False,
 points_earned = 0
WHERE
 term_code = {} AND
 (subject_code = '{}' OR subject_code = '{}') AND
 course_number = '{}' AND
 section_number = '{}'
""".format(exam_id, term_code, offer.get('prefix'), offer.get('alt_prefix'), offer.get('course_num'), offer.get('section_num')))

            responses = self.get_mc_responses(offer_id, exam_id)

            # for response in responses:
                #TODO: grade all exams for exam_id
            self.grade_mc_responses(exam_id)

            return True
        else:
            return False



    #=============================================
    # accepts: int course_id,
    #          int exam_id
    # returns: list of responses grouped by section
    #
    def get_current_course_responses(self, course_id, exam_id):
        cursor = self.get_cursor()

        course = self.get_course_info(course_id)

        term_code = self.get_term_code(self.get_current_semester(), self.get_current_year())
        cursor.execute("""
SELECT
 subject_code AS prefix, course_number AS course_num, section_number AS section_num, cr.xid, name, version, orig_scantron_version, responses, orig_scantron_responses, num_right, num_wrong, num_blank, graded, points_earned
FROM
 rolls.rolls AS cr
LEFT JOIN
 Banner_info.student_info AS si ON si.xid = cr.xid
LEFT JOIN
 mc_responses AS r ON r.xid = cr.xid AND exam_id = '{}'
WHERE
 term_code = {} AND
 (subject_code = '{}' OR subject_code = '{}') AND
 course_number = '{}' AND
 CONCAT(subject_code,"_",course_number,"_",section_number)
 NOT IN
 (SELECT CONCAT(prefix,"_",course_num,"_",section_num) FROM excluded_sections)
ORDER BY course_number, section_number, xid""".format(exam_id, term_code, course.get("prefix"), course.get("alt_prefix"), course.get("course_num")))

        responses = cursor.fetchall()


        cur_section_num = 0
        output = []
        cur_responses = []

        for response in responses:
            if cur_section_num == 0:
                cur_section_num = response.get('section_num')

            if response.get('section_num') != cur_section_num:
                # store what we have so far since this is a new section
                output.append({"prefix": course.get('prefix'), "alt_prefix": course.get('alt_prefix'), "course_num": course.get('course_num'), "section_num": cur_section_num, "responses": cur_responses})

                cur_section_num = response.get('section_num')
                cur_responses = []

            cur_responses.append(response)

        # add in the responses for the last section
        if len(responses) > 0:
            output.append({"prefix": course.get('prefix'), "alt_prefix": course.get('alt_prefix'), "course_num": course.get('course_num'), "section_num": cur_section_num, "responses": cur_responses})

        return output

    #=============================================
    # accepts: str semester,
    #          str extras
    # returns: the html for a drop down list of semesters
    #
    def get_semester_dropdown(self, selected, extras="", menu_name="semester"):
        semesters = {
            "spring": "",
            "summer I": "",
            "summer II": "",
            "fall": ""
        }

        semesters[selected] = " selected"

        output = f"""
    <select name={menu_name} id={menu_name} {extras}>
        <option value="spring"{semesters.get('spring')}>Spring</option>
        <option value="summer I"{semesters.get('summer I')}>Summer I</option>
        <option value="summer II"{semesters.get('summer II')}>Summer II</option>
        <option value="fall"{semesters.get('fall')}>Fall</option>
    </select>
"""
        return output


    #=============================================
    # accepts: str username,
    #          str semester,
    #          int year,
    #          str offer_id,
    #          str extras
    # returns: the html for a drop down list of current offers
    #
    def get_offers_dropdown_html(self, username, semester, year, offer_id, extras=""):
        if self.is_admin(username):
            offers = self.get_all_offers(semester, year)
        else:
            courses = []

            # we only show course offers if they are the current coordinator
            if self.is_current_coordinator(username):
                courses = self.get_coord_courses(username, self.get_current_semester(), self.get_current_year())
                courses = [str(course.get('course_id')) for course in courses]

            offers = self.get_offers(username, semester, year, courses)

        display = []
        values = []

        for offer in offers:
            display.append(f"'{  offer.get('prefix')  }' '{  offer.get('course_num')  }'-'{  offer.get('section_num')  }'")
            values.append(offer.get("offer_id"))

        return self.get_dropdown_html(values, display, offer_id, "offer_id", extras)


    #=============================================
    # accepts: str username,
    #          str offer_id,
    #          str extras
    # returns: the html for a drop down list of current offers
    #
    def get_current_offers_dropdown_html(self, username, offer_id, extras=""):
        if self.is_admin(username):
            offers = self.get_all_offers(self.get_current_semester(), self.get_current_year())
        else:
            courses = []

            if self.is_current_coordinator(username):
                courses = self.get_coord_courses(username, self.get_current_semester(), self.get_current_year())
                courses = [str(course.get('course_id')) for course in courses]

            offers = self.get_offers(username, self.get_current_semester(), self.get_current_year(), courses)

        display = []
        values = []

        for offer in offers:
            display.append(f"{offer.get('prefix')} {offer.get('course_num')}-{offer.get('section_num')}")
            values.append(offer.get("offer_id"))

        return self.get_dropdown_html(values, display, offer_id, "offer_id", extras)


    #=============================================
    # accepts: str offer_id,
    #          str extras
    # returns: the html for a drop down list of current offers
    #
    def get_all_current_offers_dropdown_html(self, offer_id, extras=""):
        offers = self.get_all_offers(self.get_current_semester(), self.get_current_year())

        display = []
        values = []

        for offer in offers:
            display.append(f"'{offer.get('prefix')}' '{offer.get('course_num')}'-'{offer.get('section_num')}'")
            values.append(offer.get("offer_id"))

        return self.get_dropdown_html(values, display, offer_id, "offer_id", extras)


    #=============================================
    # accepts: str username,
    #          str course_id,
    #          str extras
    # returns: the html for a drop down list of courses coordinated by this user
    #
    def get_current_coord_courses_dropdown_html(self, username, course_id, extras=""):
        courses = self.get_coord_courses(username, self.get_current_semester(), self.get_current_year())

        display = []
        values = []

        for course in courses:
            display.append("'{  course.get('prefix')  }' '{  course.get('course_num')  }'")
            values.append(course.get("course_id"))

        return self.get_dropdown_html(values, display, course_id, "course_id", extras)


    #=============================================
    # accepts: str course_id,
    #          str setting name
    # returns: the html for a drop down list of all exams for this course
    #
    def get_BST_dropdown_html(self, course_id, setting_name):
        exams = self.get_exams(course_id, self.get_current_semester(), self.get_current_year())

        display = ["-- None --"]
        values = [-1]

        for exam in exams:
            display.append(exam.get('title'))
            values.append(exam.get('exam_id'))

        return self.get_dropdown_html(values, display, int(self.get_setting(setting_name)), setting_name)


    #=============================================
    # accepts: str username,
    #          str course_id,
    #          str extras
    # returns: the html for a drop down list of all active grade collection courses
    #
    def get_active_courses_dropdown_html(self, username, course_id, extras=""):
        courses = self.get_active_courses()

        display = []
        values = []

        for course in courses:
            display.append(f"{  course.get('prefix')  } {  course.get('course_num')  }")
            values.append(course.get("course_id"))

        return self.get_dropdown_html(values, display, course_id, "course_id", extras)


    #=============================================
    # accepts: dict obj
    # returns: dict obj (with None => "")
    #
    def nones_to_blanks(self, obj):
        if isinstance(obj, list):
            for i in range(0, len(obj)):
                if obj[i] is None:
                    obj[i] = ""
        else:
            for (key, val) in obj.items():
                if val is None:
                    obj[key] = ""

        return obj


    #=============================================
    # accepts: str xid
    # returns: True if the xid is valid; False otherwise
    #
    def is_valid_xid(self, xid):
        if len(xid) != 9:
            return False
        elif xid[0] != "C":
            return False
        elif not str(xid)[1:8].isdigit():
            return False
        else:
            return True


    #=============================================
    # accepts: str letter grade
    # returns: True if the letter grade is valid; False otherwise
    #
    def is_letter_grade_valid(self, grade):
        if grade in ["A", "B", "C", "D", "F", "I", "P", "NP"]:
            return True
        else:
            return False


    #=============================================
    # accepts: str xid,
    #          int offer_id
    # returns: True if the student is in the section; False otherwise
    #
    def is_student_in_section(self, xid, offer_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            term_code = self.get_term_code(offer.get('semester'), offer.get('year'))
            cursor.execute("SELECT xid FROM rolls.rolls WHERE xid = '{}' AND term_code = {} AND (subject_code = '{}' OR subject_code = '{}') AND course_number = '{}' AND section_number = '{}'".format(xid, term_code, offer.get('prefix'), offer.get('alt_prefix'), offer.get('course_num'), offer.get('section_num')))

            if cursor.rowcount == 1:
                return True
            else:
                return False
        else:
            return False


    #=============================================
    # accepts: int offer_id,
    #          int exam_id
    # returns: True if the section took the exam; False otherwise
    #
    def did_section_take_exam(self, offer_id, exam_id):
        cursor = self.get_cursor()

        offer = self.get_offer_info(offer_id)

        if len(offer) > 0:
            cursor.execute("SELECT exam_id FROM exams WHERE course_id = '{}' AND exam_id = '{}'".format(offer.get('course_id'), exam_id))

            if cursor.rowcount == 1:
                return True
            else:
                return False
        else:
            return False


    #=============================================
    # accepts: int exam_id,
    #          str  version
    # returns: True if the exam has this version; False otherwise
    #
    def is_version_valid(self, exam_id, version):
        #if version == "-":
        #    # it is always allowed not to take the test
        #    return True

        cursor = self.get_cursor()

        # note: the version comparison is case sensitive
        cursor.execute("SELECT version FROM versions WHERE exam_id = '{}' AND BINARY version = '{}'".format(exam_id, version))

        if cursor.rowcount == 1:
            return True
        else:
            return False


    #=============================================
    # accepts: int exam_id,
    #          str version
    # returns: list of choices
    #
    def get_mc_choices(self, exam_id, version):
        cursor = self.get_cursor()

        cursor.execute("SELECT question_num, key_version_question_num, choices FROM choices AS c LEFT JOIN versions AS v ON v.version = c.version AND v.exam_id = c.exam_id WHERE v.exam_id = '{}' AND v.version = '{}' ORDER BY question_num".format(exam_id, version))

        return cursor.fetchall()


    #=============================================
    # accepts: int exam_id,
    #          str version,
    #          str responses
    # returns: (bool status, str msg)
    #
    def are_mc_responses_valid(self, exam_id, version, responses):
        choices = self.get_mc_choices(exam_id, version)

        #if version == "-" and len(responses) == 0:
        #    return (True, "")
        if len(responses) != len(choices):
            return (False, f"There should be '{  len(choices)  }' responses and you submitted '{  len(responses)  }'.")
        else:
            for i in range(0, len(responses)):
                # note we assume that the choices are numbered consecutively so we can iterate over them and the responses and keep the question numbers "in sync"
                # TODO: allow X or ? for when the scantron machine sees an answer like AB or can't determine a value
                if choices[i].get('choices').find(responses[i]) == -1 and responses[i] not in ["-", "?"]:
                    return (False, f"Response '{  i+1  }' was '{  responses[i]  }', but should be one of {  choices[i].get('choices')  }.")

        return (True, "")


    #=============================================
    # accepts: int exam_id,
    #          str version,
    #          str responses
    # returns: (bool status, str responses, str msg)
    #
    def force_mc_responses_to_be_valid(self, exam_id, version, responses, choices_dict):
        #PW 2021-07-06: choices_dict defined in process_and_get_scantron_table to reduce db queries
        choices = choices_dict[version]
        num_choices = len(choices)
        if num_choices == 0: #No key uploaded
            return (False, responses, "No exam info uploaded.")
        else:

            msgs = []
            output_responses = ""


            # edited by Kevin 8-29-17 to adjust for new scantron program
            # instead of throwing an error, the script just truncates the response string to the expected number of responses
            # it then proceeds as if there were no error
            if len(responses) != num_choices:
                responses = responses[:num_choices]
                #return (False, output_responses, "There should be '{}' responses and you submitted '{}'.".format(len(choices), len(responses)))
            #else:
            for i in range(0, len(responses)):
                # note we assume that the choices are numbered consecutively so we can iterate over them and the responses and keep the question numbers "in sync"
                # TODO: allow X or ? for when the scantron machine sees an answer like AB or can't determine a value
                if choices[i].get('choices').find(responses[i]) == -1 and responses[i] not in ["-", "?"]:
                    msgs.append(f"Response '{  i+1  }' was '{  responses[i]  }', but should be one of '{  choices[i].get('choices')  }' so it was changed to 'X'.")
                    output_responses += "X"
                else:
                    output_responses += responses[i]

            return (True, output_responses, ", ".join(msgs))


    #=============================================
    # accepts: str xid,
    #          int exam_id,
    #          str version,
    #          str responses
    # returns: True if the responses were stored; False otherwise
    #
    def store_mc_responses(self, xid, exam_id, version, responses):
        cursor = self.get_cursor()
        cursor.execute("INSERT INTO mc_responses (xid, exam_id, version, responses) VALUES ('{}', '{}', '{}', '{}') ON DUPLICATE KEY UPDATE version = '{}', responses = '{}'".format(xid, exam_id, version, responses, version, responses))

        # now grade the response
        self.grade_mc_responses(exam_id)

        return True # we really should be checking to see if everything succeeded

    #=============================================
    # accepts: int exam_id,
    #          list of tuples of (str xid, int exam_id, str version, str version, str responses, str responses)
    # returns: True if the responses were stored; False otherwise
    #
    # new function used with manage_scantrons.py to bulk save student responses
    def store_all_valid_scantron_mc_responses(self, exam_id, valid_mc_responses_list):
        cursor = self.get_cursor()
        query="INSERT INTO mc_responses (xid, exam_id, version, orig_scantron_version, responses, orig_scantron_responses) VALUES ( %s, %s, %s, %s, %s, %s ) ON DUPLICATE KEY UPDATE version = VALUES(version), orig_scantron_version=VALUES(orig_scantron_version), responses = VALUES(responses), orig_scantron_responses=VALUES(orig_scantron_responses)"
        cursor.executemany(query, valid_mc_responses_list)

        # now grade the responses
        self.grade_mc_responses(exam_id)

        return True # we really should be checking to see if everything succeeded

    #=============================================
    # accepts: str xid,
    #          int exam_id,
    #          str version,
    #          str responses
    # returns: True if the scantron responses were stored; False otherwise
    #
    # old function used with manage_scantrons.py that saved each student's responses individually
    def store_scantron_mc_responses(self, xid, exam_id, version, responses):
        cursor = self.get_cursor()

        cursor.execute("INSERT INTO mc_responses (xid, exam_id, version, orig_scantron_version, responses, orig_scantron_responses) VALUES ('{}', '{}', '{}', '{}', '{}', '{}') ON DUPLICATE KEY UPDATE version = '{}', orig_scantron_version = '{}', responses = '{}', orig_scantron_responses = '{}'".format(xid, exam_id, version, version, responses, responses, version, version, responses, responses))


        # now grade the response
        self.grade_mc_responses(exam_id)

        return True # we really should be checking to see if everything succeeded


    #=============================================
    # accepts: int exam_id
    # returns: True if the responses were graded; False otherwise
    #
    # PW 2021-07-14: grade_mc_responses was rewritten to perform
    #                the same action as this function; this function
    #                name left here for legacy purposes
    def regrade_mc_responses(self, exam_id):
        # cursor = self.get_cursor()

        # cursor.execute("SELECT xid, version FROM mc_responses WHERE exam_id = '{}'".format(exam_id))

        # responses = cursor.fetchall()

        # for response in responses:
        self.grade_mc_responses(exam_id)

        return True


    #=============================================
    # accepts: str xid
    # returns: True if the responses for an entire
    #          class were graded;
    #
    # PW 2021-07-14: function rewritten to grade entire class
    def grade_mc_responses(self, exam_id):
        cursor = self.get_cursor()

        # grab all student exams currently in database
        cursor.execute("SELECT xid, version, responses FROM mc_responses WHERE exam_id = {} ".format(exam_id))
        student_exams=cursor.fetchall()

        # initialize list of tuples to push to db
        graded_exams=[None]*len(student_exams)

        # grab keys for all versions of the exam
        cursor.execute("SELECT version FROM versions WHERE exam_id = '{}'".format(exam_id))
        version_list=[item.get('version') for item in cursor.fetchall()]
        keys={}
        for version in version_list:
            keys[version]=self.get_mc_key(exam_id, version)

        for response_num in range(len(student_exams)):
            this_student=student_exams[response_num]
            xid=this_student.get('xid')
            version=this_student.get('version')
            responses=this_student.get('responses')

            marks = ""
            num_right = 0
            num_wrong = 0
            num_blank = 0
            num_mismarked = 0
            num_points = 0.0

            key = keys.get(version)

            if version == "-" and len(responses) == 0:
                # Blank version or no responses get graded by default version
                if len(version_list > 0):
                    key = keys.get(version_list[0])
                else:
                    key = self.get_mc_key(exam_id, self.get_exam_key_version(exam_id))
                num_wrong = len(key)
                marks = "0" * len(key)
            elif len(responses) != len(key):
                num_wrong = len(key)
                num_mismarked = len(key)
                marks = "0" * len(key)
            else:
                for i in range(0, len(responses)):
                    if responses[i] == "-":
                        num_blank += 1
                        num_wrong += 1
                        marks += "0"
                    elif responses[i] == "?":
                        num_mismarked += 1
                        num_wrong += 1
                        marks += "0"
                    elif responses[i] in key[i].get('correct_answers'):
                        num_right += 1
                        num_points += float(key[i].get('points'))
                        marks += "1"
                    else:
                        num_wrong += 1
                        marks += "0"
            graded_exams[response_num]=(marks, num_right, num_wrong, num_blank, num_mismarked, num_points, xid, exam_id, version)

        query="UPDATE mc_responses SET marks = %s, num_right = %s, num_wrong = %s, num_blank = %s, num_mismarked = %s, graded = TRUE, points_earned = %s WHERE xid = %s AND exam_id = %s AND version = %s"
        cursor.executemany(query, graded_exams)

        return True
        # else:
            # return False


    #=============================================
    # accepts: str xid,
    #          int exam_id,
    #          str version,
    #          list scores
    # returns: True if the scores were stored; False otherwise
    #
    def store_fr_scores(self, xid, exam_id, version, scores):
        cursor = self.get_cursor()

        # delete the existing FR scores
        cursor.execute("DELETE FROM fr_scores WHERE xid = '{}' AND exam_id = '{}'".format(xid, exam_id))

        fr_questions = self.get_fr_questions(exam_id, version)

        # now add the new FR scores
        for i in range(0, len(scores)):
            cursor.execute("INSERT INTO fr_scores (xid, exam_id, version, question_num, points_earned) VALUES ('{}', '{}', '{}', '{}', '{}')".format(xid, exam_id, version, fr_questions[i].get('question_num'), scores[i]))

        return True # we really should be checking to see if everything succeeded


    #=============================================
    # accepts: dict exam_info={'key_version':key_version, 'fr_questions':fr_questions},
    #          list scores
    # returns: (bool status, str msg)
    #
    def are_fr_scores_valid(self, exam_info, scores):
        key_version  = exam_info.get('key_version')
        fr_questions = exam_info.get('fr_questions')
        num_of_fr_questions = len(fr_questions)

        if len(scores) != num_of_fr_questions:
            return (False, f"There are '{  num_of_fr_questions  }' FR questions, and you submitted '{  len(scores)  }' scores.")
        else:
            # pattern for allowing blanks "(^-$)|(^\d\d?\d?(\.\d)?$)"
            pattern = re.compile("^\d\d?\d?(\.\d\d?)?$")
            for i in range(0, len(scores)):
                questionNum   =fr_questions[i].get('key_version_question_num')
                questionPoints=fr_questions[i].get('points')
                # KDH 6-20-16 Added to prevent instructors from inputting a point value of more than 100
                # if (float(scores[i]) > questionPoints ):
                    # return (False, f"Score '{  i+1  }' was '{  scores[i]  }', but should be in [0,{questionPoints:.2f}] with at most two additional decimal digits. No score higher than 100.")

                # PW 2021-11-11: modified to prevent instructors from inputting a point value more than each question is worth and to provide more descript feedback
                if not pattern.match(scores[i]) or (float(scores[i]) > questionPoints ):
                    return (False, f"Score '{  questionNum  }' was '{  scores[i]  }', but should be in [0, {questionPoints:.2f}] with at most two additional decimal digits.")

        return (True, "")


    #=============================================
    # accepts: int exam_id
    # returns: str version that is the key
    #
    def get_exam_key_version(self, exam_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT key_version FROM exams WHERE exam_id = '{}'".format(exam_id))

        if cursor.rowcount == 1:
            return cursor.fetchone().get('key_version')
        else:
            return ""


    #=============================================
    # accepts: int exam_id
    # returns: list answers for this exam
    #
    def get_raw_key(self, exam_id):
        cursor = self.get_cursor()

        # get the correct answers for the key version
        cursor.execute("SELECT question_num, correct_answers, points FROM answer_keys WHERE exam_id = '{}' ORDER BY question_num".format(exam_id))

        if cursor.rowcount > 0:
            # we are assuming the question numbers are consecutive
            return [{"correct_answers": answer.get('correct_answers'), "points": answer.get('points')} for answer in cursor.fetchall()]
        else:
            return []

    #=============================================
    # accepts: int exam_id,
    #          str version
    # returns: list answers and points for this version of the exam
    #
    def get_mc_key(self, exam_id, version):
        cursor = self.get_cursor()

        # get the correct answers for the key version
        cursor.execute("SELECT question_num, correct_answers, points FROM answer_keys WHERE exam_id = '{}' ORDER BY question_num".format(exam_id))

        if cursor.rowcount > 0:
            key_answers = cursor.fetchall()
            # we assume that the questions are numbered consecutively

            # get the order of the choices in the key version
            key_version = self.get_exam_key_version(exam_id)
            key_choices = self.get_mc_choices(exam_id, key_version)

            if len(key_choices) > 0:
                key_choices = [choices.get('choices') for choices in key_choices]

                # get the order of the choices for this version
                version_choices = self.get_mc_choices(exam_id, version)

                if len(version_choices) > 0:
                    # now we make the key for this version
                    version_answers = []

                    try:
                        for i in range(0, len(version_choices)):
                            key_version_question_num = version_choices[i].get('key_version_question_num')
                            cur_choices = version_choices[i].get('choices')

                            cur_answer = ""
                            # there may be multiple correct answers
                            for j in range(0, len(key_answers[key_version_question_num - 1].get('correct_answers'))):
                                #pos = key_choices[key_version_question_num - 1].find(key_answers[key_version_question_num - 1].get('correct_answers')[j])
                                #cur_answer += cur_choices[pos]

                                pos = cur_choices.find(key_answers[key_version_question_num - 1].get('correct_answers')[j])
                                cur_answer += key_choices[key_version_question_num - 1][pos]


                            version_answers.append({"correct_answers": cur_answer, "points": key_answers[key_version_question_num - 1].get('points')})
                    except:
                        # the length of the baseline key and the version choices may not be the same so we return whatever we have so far
                        pass

                    return version_answers
                else:
                    return []
            else:
                return []
        else:
            return []


    #=============================================
    # accepts: int exam_id,
    #          str key_version,
    #          list key,
    #          list fr_questions
    # returns: True if the key was updated; False otherwise
    #
    def update_key(self, exam_id, key_version, key, fr_questions):
        cursor = self.get_cursor()

        # update the key_version
        cursor.execute("UPDATE exams SET key_version = '{}' WHERE exam_id = '{}'".format(key_version, exam_id))

        # remove the current key
        cursor.execute("DELETE FROM answer_keys WHERE exam_id = '{}'".format(exam_id))

        # add in the new MC key
        for i in range(0, len(key)):
            cur_question = key[i]
            cursor.execute("INSERT INTO answer_keys (exam_id, question_num, correct_answers, points) VALUES('{}', '{}', '{}', '{}')".format(exam_id, cur_question.get('question_num'), cur_question.get('correct_answers'), cur_question.get('points')))

        # remove the current FR questions
        cursor.execute("DELETE FROM fr_questions WHERE exam_id = '{}'".format(exam_id))

        # add in the new FR questions
        for i in range(0, len(fr_questions)):
            cur_question = fr_questions[i]
            cursor.execute("INSERT INTO fr_questions (exam_id, question_num, points, display_order) VALUES('{}', '{}', '{}', '{}')".format(exam_id, cur_question.get('question_num'), cur_question.get('points'), i))

        return True


    #=============================================
    # accepts: int exam_id,
    #          str version
    # returns: list FR questions for this version of the exam
    #
    def get_fr_questions(self, exam_id, version):
        cursor = self.get_cursor()

        cursor.execute("SELECT vfr.question_num, vfr.key_version_question_num, fr.points FROM version_fr_questions AS vfr LEFT JOIN fr_questions AS fr ON fr.exam_id = vfr.exam_id AND fr.question_num = key_version_question_num WHERE vfr.exam_id = '{}' AND vfr.version = '{}' ORDER BY vfr.display_order".format(exam_id, version))

        if cursor.rowcount > 0:
            # we are assuming the questions numbers are consecutive
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: int exam_id,
    #          str version
    # returns: list FR questions for this exam
    #
    def get_raw_fr_questions(self, exam_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT question_num, points FROM fr_questions WHERE exam_id = '{}' ORDER BY display_order".format(exam_id))

        if cursor.rowcount > 0:
            # we are assuming the questions numbers are consecutive
            return cursor.fetchall()
        else:
            return []


    #=============================================
    # accepts: int exam_id
    # returns: list of versions
    #
    def get_exam_versions(self, exam_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT version FROM versions WHERE exam_id = '{}' ORDER BY version".format(exam_id))

        return [version.get('version') for version in cursor.fetchall()]


    #=============================================
    # accepts: int exam_id,
    #          str version
    # returns: (bool status, str msg)
    #
    def delete_version(self, exam_id, version):
        cursor = self.get_cursor()

        cursor.execute("SELECT COUNT(version) AS num_responses FROM mc_responses WHERE exam_id = '{}' AND version = '{}'".format(exam_id, version))

        if cursor.fetchone().get('num_responses') == 0:
            cursor.execute("SELECT COUNT(version) AS num_responses FROM fr_scores WHERE exam_id = '{}' AND version = '{}'".format(exam_id, version))

            if cursor.fetchone().get('num_responses') == 0:
                # delete the version
                cursor.execute("DELETE FROM versions WHERE exam_id = '{}' AND version = '{}'".format(exam_id, version))

                # delete the MC choices
                cursor.execute("DELETE FROM choices WHERE exam_id = '{}' AND version = '{}'".format(exam_id, version))

                # delete the FR questions
                cursor.execute("DELETE FROM version_fr_questions WHERE exam_id = '{}' AND version = '{}'".format(exam_id, version))

                return (True, "")
            else:
                return (False, "There is FR score data associated with this version. You must remove it before deleting the version.")
        else:
            return (False, "There is MC response data associated with this version. You must remove it before deleting the version.")


    #=============================================
    # accepts: int exam_id,
    #          str version,
    #          list choices,
    #          list fr_questions
    # returns: (bool status, str msg)
    #
    def add_version(self, exam_id, version, choices, fr_questions):
        versions = self.get_exam_versions(exam_id)
        print('exam versions:')
        print(versions)

        if version in versions:
            return (False, "This version already exists for this exam. View the versions and click the pencil to edit it.")
        else:
            cursor = self.get_cursor()

            cursor.execute("INSERT INTO versions (exam_id, version) VALUES ('{}', '{}')".format(exam_id, version))

            # now add the MC choices for this version
            for choice in choices:
                print(f"""inside MC loop: {i}""")
                cursor.execute("INSERT INTO choices (exam_id, version, question_num, key_version_question_num, choices) VALUES ('{}', '{}', '{}', '{}', '{}')".format(exam_id, version, choice.get('question_num'), choice.get('key_version_question_num'), choice.get('choices')))

            # now add the FR questions for this version
            for i in range(0, len(fr_questions)):
                print(f"""inside FR loop: {i}""")
                question = fr_questions[i]
                cursor.execute("INSERT INTO version_fr_questions (exam_id, version, question_num, key_version_question_num, display_order) VALUES ('{}', '{}', '{}', '{}', '{}')".format(exam_id, version, question.get('question_num'), question.get('key_version_question_num'), i))

            return (True, "") # we really should be checking to see if everything succeeded


    #=============================================
    # accepts: int exam_id,
    #          str old_version,
    #          str new_version,
    #          list choices,
    #          list fr_questions
    # returns: True if the version was stored; False otherwise
    #
    def update_version(self, exam_id, old_version, new_version, choices, fr_questions):
        cursor = self.get_cursor()

        cursor.execute("UPDATE versions SET version = '{}' WHERE exam_id = '{}' AND version = '{}'".format(new_version, exam_id, old_version))

        # delete the old MC choice data for the old version
        cursor.execute("DELETE FROM choices WHERE exam_id = '{}' AND version = '{}'".format(exam_id, old_version))

        # now add the new MC choices for this version
        for choice in choices:
            cursor.execute("INSERT INTO choices (exam_id, version, question_num, key_version_question_num, choices) VALUES ('{}', '{}', '{}', '{}', '{}')".format(exam_id, new_version, choice.get('question_num'), choice.get('key_version_question_num'), choice.get('choices')))

        # delete the old FR questions for the old version
        cursor.execute("DELETE FROM version_fr_questions WHERE exam_id = '{}' AND version = '{}'".format(exam_id, old_version))

        # now add the new FR questions for this version
        for i in range(0, len(fr_questions)):
            question = fr_questions[i]
            cursor.execute("INSERT INTO version_fr_questions (exam_id, version, question_num, key_version_question_num, display_order) VALUES ('{}', '{}', '{}', '{}', '{}')".format(exam_id, new_version, question.get('question_num'), question.get('key_version_question_num'), i))

        return (True, "") # we really should be checking to see if everything succeeded


    #=============================================
    # accepts: int exam_id,
    #          str section_num
    # returns: dict of stats
    #
    def get_mc_stats_for_section(self, exam_id, section_num):
        cursor = self.get_cursor()

        exam = self.get_exam(exam_id)

        term_code = self.get_term_code(exam.get('semester'), exam.get('year'))

        cursor.execute("""
SELECT
 COUNT(resp.xid) AS num_students, IFNULL(AVG(points_earned), 0) AS mc_score_avg, IFNULL(STD(points_earned), 0) AS mc_score_std
FROM grade_collection.mc_responses AS resp
LEFT JOIN
 grade_collection.exams AS e ON e.exam_id = resp.exam_id
LEFT JOIN
 course.course_list AS cl ON cl.course_id = e.course_id
LEFT JOIN
 rolls.rolls ON rolls.xid = resp.xid
 AND (rolls.subject_code = cl.prefix OR rolls.subject_code = cl.alt_prefix)
 AND rolls.course_number = cl.course_num
 AND rolls.term_code = {}
LEFT JOIN
 exam_absences AS ea ON ea.exam_id = resp.exam_id
 AND
 ea.xid = resp.xid
WHERE
 e.exam_id = '{}' AND
 resp.graded = TRUE AND
 ea.xid IS NULL AND
 section_number = '{}' AND
 CONCAT(subject_code,"_",course_number,"_",section_number)
 NOT IN
 (SELECT CONCAT(es.prefix,"_", es.course_num,"_", es.section_num) FROM grade_collection.excluded_sections AS es)
""".format(term_code, exam_id, section_num))

        stats = cursor.fetchone()

        return {"num_students": stats.get('num_students'), "mc_score_avg": float(stats.get('mc_score_avg')), "mc_score_std": float(stats.get('mc_score_std'))}


    #=============================================
    # accepts: int exam_id,
    #          str section_num (to exclude)
    # returns: dict of stats
    #
    def get_mc_stats_for_other_sections(self, exam_id, section_num):
        cursor = self.get_cursor()

        exam = self.get_exam(exam_id)

        term_code = self.get_term_code(exam.get('semester'), exam.get('year'))

        cursor.execute("""
SELECT
 COUNT(resp.xid) AS num_students, IFNULL(AVG(points_earned), 0) AS mc_score_avg, IFNULL(STD(points_earned), 0) AS mc_score_std
FROM grade_collection.mc_responses AS resp
LEFT JOIN
 grade_collection.exams AS e ON e.exam_id = resp.exam_id
LEFT JOIN
 course.course_list AS cl ON cl.course_id = e.course_id
LEFT JOIN
 rolls.rolls ON rolls.xid = resp.xid
 AND (rolls.subject_code = cl.prefix OR rolls.subject_code = cl.alt_prefix)
 AND rolls.course_number = cl.course_num
 AND rolls.term_code = {}
LEFT JOIN
 exam_absences AS ea ON ea.exam_id = resp.exam_id
 AND
 ea.xid = resp.xid
WHERE
 e.exam_id = '{}' AND
 resp.graded = TRUE AND
 ea.xid IS NULL AND
 section_number != '{}' AND
 CONCAT(subject_code,"_",course_number,"_",section_number)
 NOT IN
 (SELECT CONCAT(es.prefix,"_", es.course_num,"_", es.section_num) FROM grade_collection.excluded_sections AS es)
""".format(term_code, exam_id, section_num))

        stats = cursor.fetchone()

        return {"num_students": stats.get('num_students'), "mc_score_avg": float(stats.get('mc_score_avg')), "mc_score_std": float(stats.get('mc_score_std'))}


    #=============================================
    # accepts: int offer_id
    # returns: list of instructors
    #
    def get_instructors(self, offer_id):
        cursor = self.get_cursor()

        cursor.execute("""
SELECT CONCAT_WS(" ", first_name, last_name) AS name
FROM course.people_to_offers_link AS pol
LEFT JOIN
 dept_info.person AS p ON p.username = pol.employee_username
WHERE pol.offer_id = '{}'
ORDER BY last_name, first_name
""".format(offer_id))

        return [instructor.get('name') for instructor in cursor.fetchall()]


    #=============================================
    # accepts: exam_id
    # returns: list of report data
    #
    def get_mc_course_report(self, exam_id):
        exam = self.get_exam(exam_id)

        mc_key = self.get_mc_key(exam_id, self.get_exam_key_version(exam_id))

        mc_points_total = sum([float(question.get('points')) for question in mc_key])

        sections = self.get_course_sections(exam.get('course_id'), exam.get('semester'), exam.get('year'))

        report = []

        for section in sections:
            stats = self.get_mc_stats_for_section(exam_id, section.get('section_num'))
            other_stats = self.get_mc_stats_for_other_sections(exam_id, section.get('section_num'))

            if stats.get('mc_score_std') > 0 and other_stats.get('mc_score_std') > 0:
                normalized_score = (stats.get('mc_score_avg') - other_stats.get('mc_score_avg')) / pow(stats.get('mc_score_std')**2 / stats.get('num_students') + other_stats.get('mc_score_std')**2 / other_stats.get('num_students'), 0.5)
            else:
                normalized_score = 0

            if mc_points_total == 0:
                avg_mc_percent = 0
            else:
                avg_mc_percent = stats.get('mc_score_avg') / mc_points_total * 100

            report.append({"prefix": section.get('prefix'), "course_num": section.get('course_num'), "section_num": section.get('section_num'), "instructors": ", ".join(self.get_instructors(section.get('offer_id'))), "num_students": stats.get('num_students'), "mc_score_avg": stats.get('mc_score_avg'), "mc_score_std": stats.get('mc_score_std'), "normalized_score": normalized_score, "avg_mc_percent": avg_mc_percent})

        return report


    #=============================================
    # accepts: int exam_id,
    #          str section_num
    # returns: dict of stats
    #
    def get_fr_stats_for_section(self, exam_id, section_num):
        cursor = self.get_cursor()

        exam = self.get_exam(exam_id)

        term_code = self.get_term_code(exam.get('semester'), exam.get('year'))

        cursor.execute("""
SELECT COUNT(xid) AS num_students, IFNULL(AVG(fr_points), 0) AS fr_score_avg, IFNULL(STD(fr_points), 0) AS fr_score_std FROM
(SELECT
rolls.xid, (SELECT IFNULL(SUM(fr.points_earned), 0) FROM fr_scores AS fr WHERE fr.xid = rolls.xid AND fr.exam_id = e.exam_id) AS fr_points
FROM grade_collection.exams AS e
LEFT JOIN
 course.course_list AS cl ON cl.course_id = e.course_id
LEFT JOIN
 rolls.rolls ON (rolls.subject_code = cl.prefix OR rolls.subject_code = cl.alt_prefix)
 AND rolls.course_number = cl.course_num
 AND rolls.term_code = {}
LEFT JOIN
 exam_absences AS ea ON ea.exam_id = e.exam_id
 AND
 ea.xid = rolls.xid
WHERE
 e.exam_id = '{}' AND
 ea.xid IS NULL AND
 section_number = '{}' AND
 CONCAT(subject_code,"_",course_number,"_",section_number)
 NOT IN
 (SELECT CONCAT(es.prefix,"_", es.course_num,"_", es.section_num) FROM grade_collection.excluded_sections AS es))
AS temp
""".format(term_code, exam_id, section_num))

        stats = cursor.fetchone()

        return {"num_students": stats.get('num_students'), "fr_score_avg": float(stats.get('fr_score_avg')), "fr_score_std": float(stats.get('fr_score_std'))}


    #=============================================
    # accepts: int exam_id,
    #          str section_num (to exclude)
    # returns: dict of stats
    #
    def get_fr_stats_for_other_sections(self, exam_id, section_num):
        cursor = self.get_cursor()

        exam = self.get_exam(exam_id)

        term_code = self.get_term_code(exam.get('semester'), exam.get('year'))

        cursor.execute("""
SELECT COUNT(xid) AS num_students, IFNULL(AVG(fr_points), 0) AS fr_score_avg, IFNULL(STD(fr_points), 0) AS fr_score_std FROM
(SELECT
rolls.xid, (SELECT IFNULL(SUM(fr.points_earned), 0) FROM fr_scores AS fr WHERE fr.xid = rolls.xid AND fr.exam_id = e.exam_id) AS fr_points
FROM grade_collection.exams AS e
LEFT JOIN
 course.course_list AS cl ON cl.course_id = e.course_id
LEFT JOIN
 rolls.rolls ON (rolls.subject_code = cl.prefix OR rolls.subject_code = cl.alt_prefix)
 AND rolls.course_number = cl.course_num
 AND rolls.term_code = {}
LEFT JOIN
 exam_absences AS ea ON ea.exam_id = e.exam_id
 AND
 ea.xid = rolls.xid
WHERE
 e.exam_id = '{}' AND
 ea.xid IS NULL AND
 section_number != '{}' AND
 CONCAT(subject_code,"_",course_number,"_",section_number)
 NOT IN
 (SELECT CONCAT(es.prefix,"_", es.course_num,"_", es.section_num) FROM grade_collection.excluded_sections AS es))
AS temp
""".format(term_code, exam_id, section_num))

        stats = cursor.fetchone()

        return {"num_students": stats.get('num_students'), "fr_score_avg": float(stats.get('fr_score_avg')), "fr_score_std": float(stats.get('fr_score_std'))}


    #=============================================
    # accepts: exam_id
    # returns: list of report data
    #
    def get_fr_course_report(self, exam_id):
        exam = self.get_exam(exam_id)

        fr_key_version_questions = self.get_fr_questions(exam_id, self.get_exam_key_version(exam_id))
        fr_points_total = sum([float(question.get('points')) for question in fr_key_version_questions])

        sections = self.get_course_sections(exam.get('course_id'), exam.get('semester'), exam.get('year'))

        report = []

        for section in sections:
            stats = self.get_fr_stats_for_section(exam_id, section.get('section_num'))
            other_stats = self.get_fr_stats_for_other_sections(exam_id, section.get('section_num'))

            if stats.get('fr_score_std') > 0 and other_stats.get('fr_score_std') > 0:
                normalized_score = (stats.get('fr_score_avg') - other_stats.get('fr_score_avg')) / pow(stats.get('fr_score_std')**2 / stats.get('num_students') + other_stats.get('fr_score_std')**2 / other_stats.get('num_students'), 0.5)
            else:
                normalized_score = 0

            if fr_points_total == 0:
                avg_fr_percent = 0
            else:
                avg_fr_percent = stats.get('fr_score_avg') / fr_points_total * 100

            report.append({"prefix": section.get('prefix'), "course_num": section.get('course_num'), "section_num": section.get('section_num'), "instructors": ", ".join(self.get_instructors(section.get('offer_id'))), "num_students": stats.get('num_students'), "fr_score_avg": stats.get('fr_score_avg'), "fr_score_std": stats.get('fr_score_std'), "normalized_score": normalized_score, "avg_fr_percent": avg_fr_percent})

        return report


    #=============================================
    # accepts: int exam_id,
    #          str section_num
    # returns: dict of stats
    #
    def get_overall_stats_for_section(self, exam_id, section_num):
        cursor = self.get_cursor()

        exam = self.get_exam(exam_id)

        course = self.get_course_info(exam.get('course_id'))

        term_code = self.get_term_code(exam.get('semester'), exam.get('year'))

        cursor.execute("""
SELECT COUNT(xid) AS num_students, IFNULL(AVG(mc_points + fr_points), 0) AS score_avg, IFNULL(STD(mc_points + fr_points), 0) AS score_std FROM
(SELECT
cr.xid, IFNULL(points_earned, 0) AS mc_points, (SELECT IFNULL(SUM(fr.points_earned), 0) FROM grade_collection.fr_scores AS fr WHERE fr.xid = cr.xid AND fr.exam_id = '{}') AS fr_points
FROM
 rolls.rolls AS cr
LEFT JOIN
 grade_collection.mc_responses AS r ON r.xid = cr.xid AND r.exam_id = '{}'
LEFT JOIN
 grade_collection.exam_absences AS ea ON ea.xid = cr.xid AND ea.exam_id = '{}'
WHERE
 (cr.subject_code = '{}' OR cr.subject_code = '{}')
 AND cr.course_number = '{}'
 AND cr.term_code = {}
 AND cr.section_number = '{}'
 AND CONCAT(subject_code,"_",course_number,"_",section_number)
 NOT IN
 (SELECT CONCAT(es.prefix,"_", es.course_num,"_", es.section_num) FROM grade_collection.excluded_sections AS es)
 AND ea.xid IS NULL
)
AS temp
""".format(exam.get('exam_id'), exam.get('exam_id'), exam.get('exam_id'), course.get('prefix'), course.get('alt_prefix'), course.get('course_num'), term_code, section_num))

        stats = cursor.fetchone()

        return {"num_students": stats.get('num_students'), "score_avg": float(stats.get('score_avg')), "score_std": float(stats.get('score_std'))}


    #=============================================
    # accepts: int exam_id,
    #          str section_num (to exclude)
    # returns: dict of stats
    #
    def get_overall_stats_for_other_sections(self, exam_id, section_num):
        cursor = self.get_cursor()

        exam = self.get_exam(exam_id)

        course = self.get_course_info(exam.get('course_id'))

        term_code = self.get_term_code(exam.get('semester'), exam.get('year'))

        cursor.execute("""
SELECT COUNT(xid) AS num_students, IFNULL(AVG(mc_points + fr_points), 0) AS score_avg, IFNULL(STD(mc_points + fr_points), 0) AS score_std FROM
(SELECT
cr.xid, IFNULL(points_earned, 0) AS mc_points, (SELECT IFNULL(SUM(fr.points_earned), 0) FROM grade_collection.fr_scores AS fr WHERE fr.xid = cr.xid AND fr.exam_id = '{}') AS fr_points
FROM
 rolls.rolls AS cr
LEFT JOIN
 grade_collection.mc_responses AS r ON r.xid = cr.xid AND r.exam_id = '{}'
LEFT JOIN
 grade_collection.exam_absences AS ea ON ea.xid = cr.xid AND ea.exam_id = '{}'
WHERE
 (cr.subject_code = '{}' OR cr.subject_code = '{}')
 AND cr.course_number = '{}'
 AND cr.term_code = {}
 AND cr.section_number != '{}'
 AND CONCAT(subject_code,"_",course_number,"_",section_number)
 NOT IN
 (SELECT CONCAT(es.prefix,"_", es.course_num,"_", es.section_num) FROM grade_collection.excluded_sections AS es)
 AND ea.xid IS NULL
)
AS temp
""".format(exam.get('exam_id'), exam.get('exam_id'), exam.get('exam_id'), course.get('prefix'), course.get('alt_prefix'), course.get('course_num'), term_code, section_num))

        stats = cursor.fetchone()

        return {"num_students": stats.get('num_students'), "score_avg": float(stats.get('score_avg')), "score_std": float(stats.get('score_std'))}


    #=============================================
    # accepts: exam_id
    # returns: list of report data
    #
    def get_overall_course_report(self, exam_id):
        exam = self.get_exam(exam_id)

        sections = self.get_course_sections(exam.get('course_id'), exam.get('semester'), exam.get('year'))

        report = []

        for section in sections:
            stats = self.get_overall_stats_for_section(exam_id, section.get('section_num'))
            other_stats = self.get_overall_stats_for_other_sections(exam_id, section.get('section_num'))

            if stats.get('score_std') > 0 and other_stats.get('score_std') > 0:
                normalized_score = (stats.get('score_avg') - other_stats.get('score_avg')) / pow(stats.get('score_std')**2 / stats.get('num_students') + other_stats.get('score_std')**2 / other_stats.get('num_students'), 0.5)
            else:
                normalized_score = 0

            report.append({"prefix": section.get('prefix'), "course_num": section.get('course_num'), "section_num": section.get('section_num'), "instructors": ", ".join(self.get_instructors(section.get('offer_id'))), "num_students": stats.get('num_students'), "score_avg": stats.get('score_avg'), "score_std": stats.get('score_std'), "normalized_score": normalized_score})

        return report


    #=============================================
    # accepts: int offer id,
    #          int exam id
    # returns: list of report data
    #
    def get_mc_section_item_report(self, offer_id, exam_id):
        section_responses = self.get_mc_responses(offer_id, exam_id)

        return self.get_mc_item_report_from_data(exam_id, section_responses)


    #=============================================
    # accepts: int exam id
    # returns: list of report data
    #
    def get_mc_course_item_report(self, exam_id):
        course_responses = self.get_course_mc_responses(exam_id)

        return self.get_mc_item_report_from_data(exam_id, course_responses)


    #=============================================
    # accepts: int exam id,
    #          list data for report
    # returns: list of report data
    #
    def get_mc_item_report_from_data(self, exam_id, data):
        key_version = self.get_exam_key_version(exam_id)
        versions = self.get_exam_versions(exam_id)
        answer_key = self.get_mc_key(exam_id, key_version)
        num_of_questions = len(answer_key)
        total_points = sum([float(question.get('points')) for question in answer_key])

        choices = {}

        for version in versions:
            choices[version] = self.get_mc_choices(exam_id, version)

        num_absent = 0
        num_missing = 0
        num_test_takers = 0

        scores = []
        discriminant_score_data = []
        freq_table = [0 for i in range(0, 11)]
        results = []

        if key_version != "":
            for question in choices[key_version]:
                results.append({})
                for letter in question.get('choices'):
                    results[-1][letter] = 0

                results[-1]["-"] = 0

        if total_points > 0:
            for response in data:
                if response.get('absent'):
                    num_absent += 1
                else:
                    version = response.get('version')
                    responses = response.get('responses')

                    # if there is something wrong/missing about the response then don't include them in the total
                    if version not in versions:
                        num_missing += 1
                    elif responses is None:
                        num_missing += 1
                    elif len(responses) != num_of_questions:
                        num_missing += 1
                    else:
                        num_test_takers += 1

                        scores.append(float(response.get('points_earned')))
                        discriminant_score_data.append(response)
                        bucket = int((float(response.get('points_earned')) / total_points) * 10)
                        freq_table[bucket] += 1

                        for i in range(0, len(responses)):
                            letter = responses[i]

                            if letter in ["-", "X", "?"]:
                                key_letter = "-"
                            else:
                                # we assume that only single letters are valid options
                                # maybe we should get the position of the letter from the key_version choices for this question (not sure ?)
                                pos = "ABCDE".index(letter)
                                key_letter = choices[version][i].get('choices')[pos]

                            results[choices[version][i].get('key_version_question_num') - 1][key_letter] += 1

        # we sort the valid responses for the discriminant calulations by score
        # and then by xid to satisfy some randomness assumptions
        discriminant_score_data = sorted(discriminant_score_data, key=itemgetter("points_earned", "xid"))
        group_size = int(len(discriminant_score_data) * 0.273)
        discriminant_index_list = []

        # we "sort" the choices for final output,
        # mark the correct answers,
        # and compute some stats
        output = []
        for i in range(0, len(results)):
            choice_list = []

            for letter in (choices[key_version][i].get('choices') + "-"):
                if letter in answer_key[i].get('correct_answers'):
                    correct = True
                else:
                    correct = False

                count = results[i][letter]
                choice_list.append({"choice": letter, "count": count, "correct": correct, "percentage": (float(count) / num_test_takers * 100)})

            output.append(choice_list)

            # calculate the discriminant index for this question

            high_group_correct = 0
            # see how many got this question correct in the high group
            for j in range(len(discriminant_score_data) - group_size, len(discriminant_score_data)):
                if discriminant_score_data[j].get('marks')[i] == "1":
                    high_group_correct += 1

            low_group_correct = 0
            # see how many got this question correct in the low group
            for j in range(0, group_size):
                if discriminant_score_data[j].get('marks')[i] == "1":
                    low_group_correct += 1

            discriminant_index = float(high_group_correct - low_group_correct) / float(group_size)

            discriminant_index_list.append(discriminant_index)

        if len(scores)>0:
            avg = np.average(scores)
            std = np.std(scores)
            cov = (std / avg);
            med = np.median(scores)
        else:
            avg = 0
            std = 0
            cov = 0
            med = 0

        return {"num_absent": num_absent, "num_test_takers": num_test_takers, "num_missing": num_missing, "total_points": total_points, "avg_score": avg, "std_scores": std, "coef_of_variation": cov, "median_score": med, "data": output, "freq_table": freq_table, "discriminant_index_list": discriminant_index_list}


    #=============================================
    # accepts: int offer id,
    #          int exam id
    # returns: list of report data
    #
    def get_fr_section_item_report(self, offer_id, exam_id):
        section_responses = self.get_fr_scores_version(offer_id, exam_id)

        return self.get_fr_item_report_from_data(exam_id, section_responses)


    #=============================================
    # accepts: int exam id
    # returns: list of report data
    #
    def get_fr_course_item_report(self, exam_id):
        course_responses = self.get_course_fr_scores_version(exam_id)

        return self.get_fr_item_report_from_data(exam_id, course_responses)


    #=============================================
    # accepts: int exam id,
    #          list data for report
    # returns: list of report data
    #
    def get_fr_item_report_from_data(self, exam_id, data):
        key_version = self.get_exam_key_version(exam_id)
        versions = self.get_exam_versions(exam_id)
        fr_key_version_questions = self.get_fr_questions(exam_id, key_version)
        num_of_questions = len(fr_key_version_questions)
        total_points = sum([float(question.get('points')) for question in fr_key_version_questions])

        num_absent = 0
        num_missing = 0
        num_test_takers = 0

        scores = []
        discriminant_score_data = []
        freq_table = [0 for i in range(0, 11)]
        question_scores = [0] * num_of_questions
        question_zeros = [0] * num_of_questions

        for response in data:
            if response.get('absent'):
                num_absent += 1
            else:
                version = response.get('version')

                # if there is something wrong/missing about the response then don't include them in the total
                if version not in versions:
                    num_missing += 1
                elif len(response.get('scores')) != num_of_questions:
                    num_missing += 1
                else:
                    num_test_takers += 1

                    scores.append(float(response.get('points_earned')))
                    discriminant_score_data.append(response)
                    bucket = int((float(response.get('points_earned')) / total_points) * 10)
                    #PW 2021-11-03: hack to handle case
                    # where points_earned > total_points so bucket > 10
                    bucket = min(bucket,10)
                    freq_table[bucket] += 1

                    for i in range(0, len(response.get('scores'))):
                        # TODO: account for FR questions being permuted
                        question_scores[i] += float(response.get('scores')[i])

                        if float(response.get('scores')[i]) == 0:
                            question_zeros[i] += 1

        # we sort the valid responses for the discriminant calulations by score
        # and then by xid to satisfy some randomness assumptions
        discriminant_score_data = sorted(discriminant_score_data, key=itemgetter("points_earned", "xid"))
        group_size = int(len(discriminant_score_data) * 0.273)
        discriminant_index_list = []

        # we compute some stats
        question_data = []
        for i in range(0, len(fr_key_version_questions)):
            # package up the question data
            if num_test_takers == 0:
                avg_score = 0
            else:
                avg_score = question_scores[i] / float(num_test_takers)
            question_data.append({"question_num": fr_key_version_questions[i].get('question_num'), "points": float(fr_key_version_questions[i].get('points')), "avg_score": avg_score, "num_zeros": question_zeros[i]})

            # calculate the discriminant index for this question

            high_group_score = 0
            # see how many points the high group got for this question
            for j in range(len(discriminant_score_data) - group_size, len(discriminant_score_data)):
                # TODO: handle permuting the version since FR questions may be in a different order on the FR (right now we assume they are not permuted)
                high_group_score += discriminant_score_data[j].get('scores')[i]

            low_group_score = 0
            # see how many points the low group got for this question
            for j in range(0, group_size):
                # TODO: handle permuting the version since FR questions may be in a different order on the FR (right now we assume they are not permuted)
                low_group_score += discriminant_score_data[j].get('scores')[i]

                #print(f"group size: '{  group_size  }'")
            if group_size == 0 or total_points == 0:
                discriminant_index = 0
            else:
                discriminant_index = float(high_group_score - low_group_score) / float(group_size * total_points)

            discriminant_index_list.append(discriminant_index)

        if len(scores)>0:
            avg = np.average(scores)
            std = np.std(scores)
            cov = (std / avg);
            med = np.median(scores)
        else:
            avg = 0
            std = 0
            cov = 0
            med = 0


        return {"num_absent": num_absent, "num_test_takers": num_test_takers, "num_missing": num_missing, "total_points": total_points, "avg_score": avg, "std_scores": std, "coef_of_variation": cov, "median_score": med, "data": question_data, "freq_table": freq_table, "discriminant_index_list": discriminant_index_list}


    #=============================================
    # accepts: int offer id,
    #          int exam id
    # returns: list of report data
    #
    def get_overall_section_stats(self, offer_id, exam_id):
        section_responses = self.get_overall_scores(offer_id, exam_id)

        return self.get_overall_stats_from_data(exam_id, section_responses)


    #=============================================
    # accepts: int exam id
    # returns: list of report data
    #
    def get_overall_course_stats(self, exam_id):
        course_responses = self.get_course_overall_scores(exam_id)

        return self.get_overall_stats_from_data(exam_id, course_responses)


    #=============================================
    # accepts: int exam id,
    #          list data for report
    # returns: list of report data
    #
    def get_overall_stats_from_data(self, exam_id, data):
        key_version = self.get_exam_key_version(exam_id)
        mc_answer_key = self.get_mc_key(exam_id, key_version)
        fr_questions = self.get_fr_questions(exam_id, key_version)
        total_points = 0

        for question in mc_answer_key:
            total_points += question.get('points')

        for question in fr_questions:
            total_points += question.get('points')

        total_points = float(total_points)

        num_absent = 0
        num_missing = 0
        num_test_takers = 0

        scores = []
        freq_table = [0 for i in range(0, 11)]

        for student in data:
            if student.get('absent'):
                num_absent += 1
            else:
                if (student.get('mc_version') is None and len(mc_answer_key) > 0) \
                   or (student.get('fr_version') is None and len(fr_questions) > 0) \
                   or (student.get('mc_version') is not None and student.get('fr_version') is not None \
                       and student.get('mc_version') != student.get('fr_version')):
                    num_missing += 1
                else:
                    num_test_takers += 1

                    scores.append(float(student.get('total_points')))
                    bucket = int((float(student.get('total_points')) / total_points) * 10)
                    # TODO: if they get over a 100% lump them into the last bucket also change for other frequency tables)
                    if (bucket > 10):
                        freq_table[10] += 1
                    else:
                        freq_table[bucket] += 1

        avg = np.average(scores)
        std = np.std(scores)

        return {"num_absent": num_absent, "num_test_takers": num_test_takers, "num_missing": num_missing, "total_points": total_points, "avg_score": avg, "std_scores": std, "coef_of_variation": (std / avg), "median_score": np.median(scores), "freq_table": freq_table}


    #=============================================
    # accepts: none
    # returns: str html for error message
    #
    def print_error_msg_page(self, user=""):
        #TODO render templates and test this function
        if len(user)>0:
            html = f"""You are logged in as <span style="font-weight: bold; color: #F66733;">{user}</span> and you are not authorized to access this page. <!--If you are a graduate student you should be logging in with your student username as your old employee username has been phased out.--> Click <a href=".">here</a> to return to the main page."""
        else:
            html = """You are not authorized to access this page. Click <a href=".">here</a> to return to the main page."""

        return html


    #=============================================
    # accepts: number num,
    #          optional boolean strip
    # returns: str representing the number
    #
    def pretty_print_number(self, num, strip=True):
        if isinstance(num, str):
            return ""
        elif num is None:
            return "None"
        elif int(num) != num:
            temp = "{0:.2f}".format(num)
            if strip:
                return temp.rstrip("0")
            else:
                return temp
        else: #value is an integer
            return "{:.0f}".format(num)

    #=============================================
    # accepts: string info
    #
    # returns: html code for info box
    def info_box(self, info):
        return f"""
<div style="text-align: center; margin-top: 6px; margin-bottom: 3px; min-height: 35px;">
    <span id="info_box" style="display: inline-block; padding: 5px; background: #FFFF99; border: solid 2px #000000;">{info}</span>
</div>
"""
