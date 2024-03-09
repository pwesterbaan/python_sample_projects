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

@grade_collection_bp.route('/reports',methods=['GET','POST'])
@htpasswd.required
def reports(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: Reports"
    kwargs['user']=user #TODO

    if not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", content=html)

    form     =request.form
    semester =form.get('semester', gl.get_current_semester())
    year     =form.get('year', gl.get_current_year())
    offer_id =form.get('offer_id', session.get('offer_id'))
    exam_id  =form.get('exam_id')
    action   =form.get("action")
    form_name=form.get("form_name")

    session['semester']=semester
    session['year']    =year
    session['offer_id']=offer_id

    offer_info = gl.get_offer_info(offer_id)

    if form_name == "reports":
        # set default error message
        html = "This report does not exist for this course and exam."
        if action == "view_keys":
            html = gl.get_html_for_instructor_view_keys(exam_id,offer_info)
        elif action.endswith('item_report'):
            #{mc,fr}_{section,course}_item_report
            html=gl.get_html_for_instructor_mc_and_fr_item_reports(exam_id, offer_id, action)
        elif action.endswith('course_report'):
            #{mc,fr,overall}_course_report
            html = gl.get_html_for_instructor_course_report(exam_id, offer_id, user, action)
        return html

    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"instructor"))
    kwargs['menu'] = Markup(gl.get_menu())
    kwargs['semester_dropdown'] = Markup(gl.get_semester_dropdown(semester, " onchange=\"get_offers()\""))
    kwargs['year_dropdown'] = Markup(gl.get_year_dropdown(year, " onchange=\"get_offers()\""))
    kwargs['offers_dropdown'] = Markup(gl.get_current_offers_dropdown_html(user,offer_id, "onchange=\"get_exams()\""))

    return render_template("reports.html", **kwargs)

@grade_collection_bp.route('/reports_ajax', methods=['GET', 'POST'])
@htpasswd.required
def reports_ajax(user):

    form      =request.form    
    action    =form.get('action')
    func_name =form.get("func_name")
    offer_id  =form.get('offer_id')
    semester  =form.get('semester')
    year      =form.get('year')

    offer_info = gl.get_offer_info(offer_id)

    is_teaching=gl.is_teaching(user, offer_id)
    is_admin=gl.is_admin(user)
    is_coord=gl.is_coordinating(user, offer_info.get('semester'), offer_info.get('year'), offer_info.get('course_id'))
    
    if offer_id != 0 and not is_teaching and not is_admin and not is_coord:
        html=Markup(gl.print_error_msg_page(user))
        render_template("msg.html", html=html)

    html = ""
    
    if func_name == "get_offers":

        if gl.is_admin(user):
            offers = gl.get_all_offers(semester, year)
        else:
            courses = []
            # we only show course offers if they are the current coordinator
            if gl.is_current_coordinator(user):
                courses = gl.get_coord_courses(user, gl.get_current_semester(), gl.get_current_year())
                courses = [str(course["course_id"]) for course in courses]

            offers = gl.get_offers(user, semester, year, courses) 

        html = json.dumps({"error": False, "data": offers})
    elif func_name == "get_exams":
        exams = gl.get_exams(offer_info["course_id"], offer_info["semester"], offer_info["year"])

        dthandler = lambda obj: obj.isoformat() if isinstance(obj, datetime.datetime) else None
        # dthandler = lambda obj: obj.isoformat() if isinstance(obj, datetime) else None

        html = json.dumps({"error": False, "data": exams}, default = dthandler)

    return html
