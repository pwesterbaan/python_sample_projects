#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, json, jsonify, session, redirect, url_for

# from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()

@grade_collection_bp.route('/manage_exams',methods=['GET','POST'])
@htpasswd.required
def manage_exams(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: Manage Exams"
    kwargs['user']=user #TODO

    if not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", content=html)

    form      =request.form
    action    =form.get('action') #TODO: don't save action
    course_id =form.get('course_id')
    date_given=gl.get_date_value(form,'date_given')
    exam_id   =form.get('exam_id')
    form_name =form.get('form_name')
    grades_due=gl.get_date_value(form,'grades_due')
    title     =form.get('title')
    weight    =form.get('weight')

    #show all exams
    kwargs['content']=Markup(gl.get_html_to_list_all_exams())
    
    if form_name == "delete_exam":
        result = gl.delete_exam(exam_id)
        if not result[0]:
            html = json.dumps({"error": not result[0], "data": result[1]})
            return html
    elif form_name in ["add","edit"]:
        html=gl.get_html_for_exam_add_edit_pages(form_name, exam_id, course_id)
        return html
    if action in ["edit_exam","add_exam"]:
        if action == "add_exam":
            result = gl.add_exam(course_id, title, date_given, grades_due, weight)
        else:
            result = gl.update_exam(exam_id, title, date_given, grades_due, weight)
        html = json.dumps({"error": not result[0], "data": result[1]})
        return html

    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"admin"))
    kwargs['menu'] = Markup(gl.get_admin_menu())

    return render_template("manage_exams.html", **kwargs)
