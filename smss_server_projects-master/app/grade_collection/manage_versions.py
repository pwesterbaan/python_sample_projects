#!/var/www/mthsc/common/venv/bin/python3

import datetime
import os
import json
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, jsonify, session

from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()

@grade_collection_bp.route('/manage_versions',methods=['GET','POST'])
@htpasswd.required
def manage_versions(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: Manage Versions"
    kwargs['user']=user #TODO

    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        render_template("msg.html", html=html)

    form       =request.form
    action     =form.get("action")
    course_id  =form.get("course_id", session.get('course_id'))
    exam_id    =form.get("exam_id")
    form_name  =form.get("form_name")
    new_version=form.get('new_version','')
    old_version=form.get('old_version','')

    session['course_id']=course_id

    if form_name == "manage_versions":
        if action == "view":
            # generate tables to display stored keys for requested exam
            html=gl.get_view_version_and_keys_table(course_id, exam_id)
        elif action == "submit_key":
            html= gl.get_html_to_submit_key(course_id, exam_id)
        elif action == "modify_version":
            html= gl.get_html_to_modify_version(course_id, exam_id, old_version)
        else:
            
            html = "Invalid action"
        return html

    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"coordinator"))
    kwargs['menu'] = Markup(gl.get_coord_menu())
    if gl.is_admin(user):
        courses_dropdown = gl.get_active_courses_dropdown_html(user, course_id, " onchange=\"get_exams()\"")
    else: #coordinator
        courses_dropdown = gl.get_current_coord_courses_dropdown_html(user, course_id, " onchange=\"get_exams()\"")
    kwargs['courses_dropdown'] = Markup(courses_dropdown)

    return render_template("manage_versions.html", **kwargs)


@grade_collection_bp.route('/versions_ajax', methods=['GET', 'POST'])
@htpasswd.required
def versions_ajax(user):

    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
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
    elif func_name == "delete_version":
        exam_id = form.get("exam_id")
        version = form.get("version")

        result = gl.delete_version(exam_id, version)
        
        html = json.dumps({"error": not result[0], "data": result[1]})
    elif func_name == "update_version":
        exam_id = form.get("exam_id")
        old_version = form.get("old_version")
        old_version = form.get("old_version").upper()
        mc_data = form.get("mc_data")

        error = False
        error_msg = ""

        # we assume that the choices will all be capital letters
        mc_data = mc_data.upper().replace("\r", "").replace("\t",",").strip()

        if len(mc_data) > 0:
            mc_data = mc_data.split("\n")
            choices = []

            for i in range(0, len(mc_data)):
                choice = mc_data[i].split(",")
                if len(choice) != len(set(choice)):
                    error = True
                    error_msg = "For the MC data, question %i has a duplicated choice. The data provided was \"%s\"." % (i + 1, mc_data[i])
                    break
                answer_choices = "".join(choice[1:])
                choices.append({"question_num": i + 1, "key_version_question_num": choice[0].strip(), "choices": answer_choices})
        else:
            choices = []

        fr_data = form.get("fr_data")

        # TODO: check to make sure the FR data doesn't have any problems
        fr_data = fr_data.replace("\r", "").replace("\t",",").strip()
        fr_questions = []

        if not error and len(fr_data) > 0:
            fr_data = fr_data.split("\n")

            for i in range(0, len(fr_data)):
                question = fr_data[i].split(",")
                if len(question) != 2:
                    error = True
                    error_msg = "FR line %s (%s) should have 2 columns." % (i, fr_data[i])
                    break

                fr_questions.append({"question_num": question[0].strip(), "key_version_question_num": question[1].strip()})
        else:
            fr_data = []

        if not error:
            new_version=form.get('new_version')
            old_version=form.get('old_version')
            if old_version == "":
                result = gl.add_version(exam_id, new_version, choices, fr_questions)
            else:
                result = gl.update_version(exam_id, old_version, new_version, choices, fr_questions)

            if result[0]:
                # regrade the responses as they may have changed
                gl.grade_mc_responses(exam_id)

            html = json.dumps({"error": not result[0], "data": result[1]})
        else:
            html = json.dumps({"error": True, "data": error_msg})

    elif func_name == "update_key":
        exam_id = form.get("exam_id")
        key_version = form.get("key_version")

        mc_data = form.get("mc_data")
        # TODO: check to make sure the MC data doesn't have any problems
        mc_data = mc_data.replace("\r", "").replace("\t",",").strip()

        key = []
        if len(mc_data) > 0:
            mc_data = mc_data.split("\n")
            

            for i in range(0, len(mc_data)):
                question = mc_data[i].split(",")
                key.append({"question_num": i + 1, "points": question[0].strip(), "correct_answers": question[1].strip()})

        fr_data = form.get("fr_data")
        fr_data = fr_data.replace("\r", "").replace("\t",",").strip()

        error = False
        error_msg = ""

        if len(fr_data) > 0:
            fr_data = fr_data.split("\n")
            fr_questions = []

            for i in range(0, len(fr_data)):
                question = fr_data[i].split(",")

                if len(question) != 2:
                    error = True
                    error_msg = f"""Line {i} ({fr_data[i]}) should have 2 columns."""
                    break

                fr_questions.append({"question_num": question[0].strip(), "points": question[1].strip()})
        else:
            fr_questions = []

        if not error:
            gl.update_key(exam_id, key_version, key, fr_questions)

            # regrade the responses as the key may have changed
            gl.regrade_mc_responses(exam_id)

            html = json.dumps({"error": False, "data": ""})
        else:
            html = json.dumps({"error": True, "data": error_msg})

    return html
