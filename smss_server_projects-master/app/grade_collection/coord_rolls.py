#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, jsonify, session

from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()

@grade_collection_bp.route('/coord_rolls',methods=['GET','POST'])
@htpasswd.required
def coord_rolls(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: Rolls"
    kwargs['user']=user #TODO

    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        render_template("msg.html", html=html)

    form=request.form
    semester =form.get('semester', gl.get_current_semester())
    year     =form.get('year', gl.get_current_year())
    course_id=form.get("course_id",session.get('course_id'))
    exam_id  =form.get("exam_id")
    action   =form.get("action")
    form_name=form.get("form_name")

    session['course_id']=course_id

    if form_name == "coord_rolls":
        if action == "section_summary":
            html=gl.get_coord_section_summary(form)
        #TODO the following if statments are unreachable from the coord_rolls page
        elif action == "fr_course_report":
            html=gl.get_coord_fr_course_report_table(form)
            pass
        elif action == "overall_course_report":
            html=gl.get_coord_course_report(form)
        elif action in ["overall_course_grades", "overall_course_grades_download","overall_course_raw_data", "overall_course_raw_data_download"]:
            exam_info   = gl.get_exam(exam_id)
            course_info = gl.get_course_info(exam_info.get('course_id'))
            sections    = gl.get_course_sections(exam_info.get('course_id'), exam_info.get('semester'), exam_info.get('year'))
            if action == "overall_course_grades":
                html= gl.get_coord_overall_course_grades_table(exam_info, course_info, exam_id, sections)
            elif action == "overall_course_grades_download":
                fileContents=gl.get_coord_overall_course_grades_download(exam_info, course_info, exam_id, sections)
                html=excel.make_response_from_array(fileContents, "csv")
            elif action == "overall_course_raw_data":
                html=gl.get_coord_overall_course_raw_data_table(exam_info, course_info, exam_id, sections)
            elif action == "overall_course_raw_data_download":
                fileContents=gl.get_coord_overall_course_raw_data_download(exam_info, course_info, exam_id, sections)
                html=excel_make_response_from_array(fileContents, "csv")
        return html
                
    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"coordinator"))
    kwargs['menu'] = Markup(gl.get_coord_menu())
    kwargs['semester_dropdown'] = Markup(gl.get_semester_dropdown(semester, " onchange=\"try_action()\""))
    kwargs['year_dropdown'] = Markup(gl.get_year_dropdown(year, " onchange=\"try_action()\""))
    if gl.is_admin(user):
        courses_dropdown = Markup(gl.get_active_courses_dropdown_html(user, course_id, " onchange=\"try_action()\""))
    else: #coordinator
        courses_dropdown = Markup(gl.get_current_coord_courses_dropdown_html(user, course_id, " onchange=\"try_action()\""))
    kwargs['coord_courses_dropdown'] = courses_dropdown

    return render_template("coord_rolls.html", **kwargs)
