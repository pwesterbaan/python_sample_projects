#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, send_from_directory, session

from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd

gl=grade_collection_lib()

@grade_collection_bp.route('/coord_term_end',methods=['GET','POST'])
@htpasswd.required
def coord_term_end(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: End of Term"
    kwargs['user']=user #TODO

    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", html=html)

    form     =request.form
    semester =form.get('semester', gl.get_current_semester())
    year     =form.get('year', gl.get_current_year())
    course_id=form.get('course_id', session.get('course_id'))
    form_name=form.get('form_name')
    action   =form.get('action')

    session['course_id']=course_id

    if form_name == "term_end":
        local_filename=os.path.join(current_app.config.get('GC_REPORTS_FOLDER'),f'course_{str(course_id).zfill(3)}_{action}_report.csv')
        if action == "term_summary":
            html=gl.get_html_to_print_term_summary(semester, year, course_id, local_filename)
        elif action == "view_data":
            html=gl.get_html_to_print_view_data(semester, year, course_id, local_filename)
        return html

    kwargs['role_box'] = Markup(gl.get_role_box(user,"coordinator"))
    kwargs['menu'] = Markup(gl.get_coord_menu())
    kwargs['semester_dropdown'] = Markup(gl.get_semester_dropdown(semester, " onchange=\"try_action()\""))
    kwargs['year_dropdown'] = Markup(gl.get_year_dropdown(year, " onchange=\"try_action()\""))
    if gl.is_admin(user):
        courses_dropdown = gl.get_active_courses_dropdown_html(user, course_id, " onchange=\"try_action()\"")
    else: #coordinator
        courses_dropdown = gl.get_current_coord_courses_dropdown_html(user, course_id, " onchange=\"try_action()\"")
    kwargs['coord_courses_dropdown'] = Markup(courses_dropdown)

    return render_template("coord_term_end.html", **kwargs)

@grade_collection_bp.route('/get_coord_term_end', methods=['POST'])
@htpasswd.required
def get_coord_term_end(user):
    if not gl.is_admin(user) and not gl.is_staff(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", html=html)

    form     =request.form
    action   =form.get('action')
    course_id=form.get('course_id')
    filename =form.get('filename')
    form_name=form.get('form_name')
    semester =form.get('semester', gl.get_current_semester())
    year     =form.get('year', gl.get_current_year())

    local_filename=f"course_{str(course_id).zfill(3)}_{action}_report.csv"
    return send_from_directory(current_app.config['GC_REPORTS_FOLDER'], local_filename, attachment_filename=filename, as_attachment=True)
