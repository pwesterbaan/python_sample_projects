#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, jsonify, session, send_from_directory

from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()

@grade_collection_bp.route('/admin_term_end',methods=['GET','POST'])
@htpasswd.required
def admin_term_end(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: End of Term"
    kwargs['user']=user #TODO

    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        render_template("msg.html", html=html)

    form      =request.form
    action    =form.get('action')
    form_name =form.get('form_name')
    xid       =form.get('xid')
    semester  =form.get('semester',gl.get_current_semester())
    year      =form.get('year',gl.get_current_year())

    session['semester']=semester
    session['year']    =year
    
    if request.method == "POST":
        local_filename=os.path.join(current_app.config.get('GC_REPORTS_FOLDER'),f'all_sections_{action}.csv')
        if action == "term_summary":
            html=gl.get_html_to_print_admin_term_summary(semester, year, local_filename)
        elif action == "view_data":
            html=gl.get_html_to_print_admin_view_data(semester, year, local_filename)
        else:
            html = "Invalid action"
        return html

    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"admin"))
    kwargs['menu'] = Markup(gl.get_admin_menu())
    kwargs['semester_dropdown'] = Markup(gl.get_semester_dropdown(semester, " onchange=\"try_action()\""))
    kwargs['year_dropdown'] = Markup(gl.get_year_dropdown(year, " onchange=\"try_action()\""))

    return render_template("admin_term_end.html", **kwargs)

@grade_collection_bp.route('/get_admin_term_end', methods=['POST'])
@htpasswd.required
def get_admin_term_end(user):
    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        render_template("msg.html", html=html)

    form     =request.form
    action   =form.get('action')
    course_id=form.get('course_id')
    filename =form.get('filename')
    form_name=form.get('form_name')
    semester =form.get('semester', gl.get_current_semester())
    year     =form.get('year', gl.get_current_year())

    local_filename=f'all_sections_{action}.csv'
    return send_from_directory(current_app.config['GC_REPORTS_FOLDER'], local_filename, attachment_filename=filename, as_attachment=True)
