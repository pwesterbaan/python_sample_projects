#!/var/www/mthsc/common/venv/bin/python3

import datetime
import os
import json
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, jsonify, session

from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()

@grade_collection_bp.route('/manage_grades',methods=['GET','POST'])
@htpasswd.required
def manage_grades(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: Manage Grades"
    kwargs['user']=user #TODO

    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", html=html)

    form          =request.form
    offer_id      =form.get('offer_id', session.get('offer_id'))
    form_name     =form.get("form_name")
    action        =form.get("action")
    exam_id       =form.get('exam_id')
    data          =form.get('data')
    response_type =form.get('response_type')

    session['offer_id']=offer_id
    offer_info = gl.get_offer_info(offer_id)

    if form_name == "manage_grades":
        if action == "exam_absences":
            html = gl.get_html_to_mark_absences(offer_id, offer_info, exam_id)
        elif action == "view_mc":
            html = gl.get_html_to_view_mc(offer_id, offer_info, exam_id)
        elif action == "submit_mc":
            html = gl.get_html_to_submit_mc(offer_id, exam_id)
        elif action == "original_mc":
            html = gl.get_html_to_revert_scantron_scores(offer_id, exam_id)
        elif action == "view_fr":
            html = gl.get_html_to_view_fr(offer_id, offer_info, exam_id)
        elif action == "submit_fr":
            html = gl.get_html_to_submit_fr(offer_id, offer_info, exam_id)
        elif action == "view_overall":
            html = gl.get_html_to_view_overall(offer_id, offer_info, exam_id)
        else:
            html = "Invalid action"
        return html
    elif form_name == "submit_responses":
        html = gl.get_html_to_submit_responses(data, response_type, offer_id, exam_id)
        return html


    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"instructor"))
    kwargs['menu'] = Markup(gl.get_menu())
    kwargs['offers_dropdown'] = Markup(gl.get_current_offers_dropdown_html(user,offer_id, "onchange=\"get_exams()\""))

    return render_template("manage_grades.html", **kwargs)

@grade_collection_bp.route('/grades_ajax', methods=['GET', 'POST'])
@htpasswd.required
def grades_ajax(user):

    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        render_template("msg.html", html=html)

    html = ""
    
    form=request.form
    offer_id  = form.get('offer_id')
    offer_info= form.get('offer_info')
    func_name = form.get('func_name')
    
    if func_name == "get_exams":
        course_id = gl.get_course_id_from_offer_id(offer_id)
    
        exams = gl.get_current_exams(course_id)

        dthandler = lambda obj: obj.isoformat() if isinstance(obj, datetime.datetime) else None

        html = json.dumps({"error": False, "data": exams}, default = dthandler)
    elif func_name == "save_absences":
        exam_id  = form.get('exam_id')
        absences = form.get('absence_data').strip()

        if len(absences) > 0:
            absences = absences.split(",")
        else:
            absences = []

        error = False
        msg = ""
        # we check to make sure the exam is for this offer
        if not gl.did_section_take_exam(offer_id, exam_id):
            error = True
            msg = f"This section ({offer_id}) did not take this exam ({exam_id})."
        else:
            # we check to make sure these students are in the offer
            for xid in absences:
                if not gl.is_student_in_section(xid, offer_id):
                    error = True
                    msg += f"{xid} is not in this section\n"

        if error:
            html = json.dumps({"error": True, "data": msg})
        else:
            gl.update_exam_absences(offer_id, exam_id, absences)

            html = json.dumps({"error": False, "data": "absences saved"})
    return html
