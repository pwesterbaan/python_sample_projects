#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, json, jsonify, session, redirect, url_for

# from app import excel
from . import ug_course_pages_bp
from .course_page_lib import course_page_lib
from extensions import htpasswd, sess

cpl=course_page_lib()

@ug_course_pages_bp.route('/calculator.html',methods=['GET'])
@ug_course_pages_bp.route('/calculator',methods=['GET'])
@htpasswd.required
def calculator(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Calculators for Clemson Math Courses"
    kwargs['user']=user #TODO

    return render_template("calculator.html", **kwargs)
