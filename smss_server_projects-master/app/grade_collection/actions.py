#!/var/www/mthsc/common/venv/bin/python3

import csv
import os
import re
import requests
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, json, jsonify, session, redirect, url_for

# from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from .course_manager_lib import course_manager_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()
cml=course_manager_lib()

@grade_collection_bp.route('/actions',methods=['GET','POST'])
@htpasswd.required
def actions(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: Actions"
    kwargs['user']=user #TODO

    if not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", content=html)

    form                  =request.form
    # data_file             =request.files.get('data_file') #TODO
    MATH_1040_avg_item_id =int(form.get('MATH_1040_avg_item_id',0))
    action                =form.get('action')
    form_name             =form.get('form_name')
    only_missing          =int(form.get('only_missing',0))
    semester              =form.get('semester',cml.get_current_semester())
    year                  =form.get('year',cml.get_current_year())

    html=""
    info=""

    if form_name == "1040_grade_import":
        info=gl.import_1040_grades(semester,year,MATH_1040_avg_item_id,only_missing)

    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"admin"))
    kwargs['menu'] = Markup(gl.get_admin_menu())
    kwargs['content'] = Markup(gl.get_html_for_actions_page(semester,year,MATH_1040_avg_item_id,only_missing,info))

    return render_template("actions.html", **kwargs)
