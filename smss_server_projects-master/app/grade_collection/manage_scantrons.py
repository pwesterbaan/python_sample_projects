#!/var/www/mthsc/common/venv/bin/python3

import datetime
import os
import json
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, jsonify
from flask_cors import CORS

from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd

gl=grade_collection_lib()
# CORS(grade_collection_bp)

@grade_collection_bp.route('/manage_scantrons',methods=['GET','POST'])
@htpasswd.required
def manage_scantrons(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: Manage Scantrons"
    kwargs['user']=user #TODO

    if not gl.is_staff(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        render_template("msg.html", html=html)

    form_data=""

    form     =request.form
    course_id=form.get("course_id")
    exam_id  =form.get("exam_id")
    action   =form.get("action")
    form_name=form.get("form_name")
    form_data=request.files.get('data')


    if form_name == "manage_scantrons":
        if action == "download_rolls":
            # generate html for download link
            course_info = gl.get_course_info(course_id)
            filename=f"{course_info.get('prefix')}_{course_info.get('course_num')}_rolls.csv"
            html = gl.get_download_rolls_link(course_info, filename)
        elif action == "view_scantrons":
            # generate html for view scantron table
            responses = gl.get_current_course_responses(course_id, exam_id)
            html = gl.get_view_scantrons_table(responses)
        elif action == "submit_scantrons":
            # use course_info, course_id and exam_info to build textbox for upload
            course_info = gl.get_course_info(course_id)
            exam_info = gl.get_exam(exam_id)
            html = gl.get_upload_scantron_form(course_info, exam_info)
        else:
            html = "Invalid action"
        return html

    elif form_name == "submit_scantrons":
        html=Markup(gl.process_and_get_scantron_table(form, form_data))
        return html

    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"staff"))
    kwargs['menu'] = Markup(gl.get_staff_menu())
    kwargs['courses_dropdown'] = Markup(gl.get_active_courses_dropdown_html(user, course_id, " onchange=\"get_exams()\""))

    return render_template("manage_scantrons.html", **kwargs)

@grade_collection_bp.route('/get_course_rolls', methods=['POST'])
@htpasswd.required
def get_course_rolls(user):
    if not gl.is_admin(user) and not gl.is_staff(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", html=html)

    form=request.form
    course_id = form.get("course_id")
    course_info = gl.get_course_info(course_id)
    roster = gl.get_current_course_roll(course_id)

    fileContents=[]
    for student in roster:
        cuid = "0" + student["xid"][1:]
        fileContents.append([cuid, student.get("last_name"), student.get("first_name"),"","","","","","","",""])

    response=excel.make_response_from_array(fileContents, "csv")
    return response

@grade_collection_bp.route('/scantrons_ajax', methods=['GET', 'POST'])
@htpasswd.required
def scantrons_ajax(user):

    if not gl.is_staff(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        render_template("msg.html", html=html)

    html = ""
    
    form=request.form
    func_name = form.get("func_name")
    
    if func_name == "get_exams":
        course_id = form.get("course_id")
        
        exams = gl.get_current_exams(course_id)
        
        dthandler = lambda obj: obj.isoformat() if isinstance(obj, datetime.datetime) else None
        # dthandler = lambda obj: obj.isoformat() if isinstance(obj, datetime) else None

        html = json.dumps({"error": False, "data": exams}, default = dthandler)
    elif func_name == "update_key":
        exam_id = form.get("exam_id")
        key_version = form.get("key_version")
        data = form.get("data")
        
        data = data.replace("\r", "").replace("\t",",").strip()
        
        if len(data) > 0:
            data = data.split("\n")
            key = []

            for i in range(0, len(data)):
                question = data[i].split(",")
                key.append({"question_num": i + 1, "points": question[0].strip(), "correct_answers": question[1].strip()})
        else:
            key = []

        gl.update_key(exam_id, key_version, key)

        html = json.dumps({"error": False, "data": ""})

    return html
