#!/var/www/mthsc/common/venv/bin/python3

import datetime
import os
import json
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, jsonify, session

from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd

gl=grade_collection_lib()

@grade_collection_bp.route('/coord_reports',methods=['GET','POST'])
@htpasswd.required
def coord_reports(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: Reports"
    kwargs['user']=user #TODO

    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", html=html)

    form=request.form
    semester =form.get('semester', gl.get_current_semester())
    year     =form.get('year', gl.get_current_year())
    course_id=form.get("course_id",session.get('course_id'))
    exam_id  =form.get("exam_id")
    action   =form.get("action")
    form_name=form.get("form_name")

    session['course_id']=course_id

    if form_name == "reports":
        # Set default message
        html="This report does not exist for this course and exam."
        if action == "mc_course_report":
            html=gl.get_html_to_print_course_report('MC', exam_id, course_id)
        elif action == "fr_course_report":
            html=gl.get_html_to_print_course_report('FR', exam_id, course_id)
        elif action == "overall_course_report":
            html=gl.get_html_to_print_course_report('Overall', exam_id, course_id)
        elif action == "overall_course_grades":
            html, csv_file_contents = gl.get_html_for_overall_course_grades(exam_id)
        elif action == "overall_course_raw_data":
            html, csv_file_contents = gl.get_html_for_overall_course_raw_data(exam_id)
        return html
                
    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"coordinator"))
    kwargs['menu'] = Markup(gl.get_coord_menu())
    kwargs['semester_dropdown'] = Markup(gl.get_semester_dropdown(semester, " onchange=\"get_exams()\""))
    kwargs['year_dropdown'] = Markup(gl.get_year_dropdown(year, " onchange=\"try_action()\""))
    if gl.is_admin(user):
        courses_dropdown = Markup(gl.get_active_courses_dropdown_html(user, course_id, " onchange=\"get_exams()\""))
    else: #coordinator
        courses_dropdown = Markup(gl.get_current_coord_courses_dropdown_html(user, course_id, " onchange=\"get_exams()\""))
    kwargs['coord_courses_dropdown'] = courses_dropdown

    return render_template("coord_reports.html", **kwargs)

@grade_collection_bp.route('/get_coord_reports',methods=['POST'])
@htpasswd.required
def get_coord_reports(user):
    if not gl.is_admin(user) and not gl.is_staff(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", html=html)

    form     =request.form
    form_name=form.get('form_name')
    action   =form.get('action')
    exam_id  =form.get('exam_id')
    semester =form.get('semester')
    year     =form.get('year')
    course_id=form.get('course_id')
    
    # course_info=gl.get_course_info(
    if action=="overall_course_grades":
        html, fileContents = gl.get_html_for_overall_course_grades(exam_id)
    elif action == "overall_course_raw_data":
        html, fileContents = gl.get_html_for_overall_course_raw_data(exam_id)
    response=excel.make_response_from_array(fileContents, "csv")
    return response

@grade_collection_bp.route('/coord_reports_ajax', methods=['GET', 'POST'])
@htpasswd.required
def coord_reports_ajax(user):

    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        render_template("msg.html", html=html)

    html = ""

    form=request.form
    func_name = form.get("func_name")
    semester  = form.get('semester')
    year      = form.get('year')
    course_id = form.get('course_id')

    if func_name == "get_exams":

        exams = gl.get_exams(course_id, semester, year)
        #exams = gl.get_current_exams(course_id)

        dthandler = lambda obj: obj.isoformat() if isinstance(obj, datetime.datetime) else None
        # dthandler = lambda obj: obj.isoformat() if isinstance(obj, datetime) else None

        html = json.dumps({"error": False, "data": exams}, default = dthandler)
    return html
