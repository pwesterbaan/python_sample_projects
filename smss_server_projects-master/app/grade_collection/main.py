#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, flash, send_from_directory, Blueprint, current_app, url_for, redirect

from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd

gl=grade_collection_lib()

@grade_collection_bp.route('/',methods=['GET'])
@grade_collection_bp.route('/main',methods=['GET'])
@htpasswd.required
def main(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    # kwargs['debug']=sys.version #print version of python to page
    kwargs['title']="Grades: Main"
    kwargs['user']=user
    roles_menu=[]

    num_roles = 0
    last_role = ""

    isAdmin=gl.is_admin(user)
    isStaff=gl.is_staff(user)
    isCoord=gl.is_coordinator(user, gl.get_current_semester(), gl.get_current_year())
    isInstr=gl.is_instructor(user, gl.get_current_semester(), gl.get_current_year())

    if isAdmin:
        num_roles += 1
        last_role = "admin"
        roles_menu.append({"text":"Admin Section","url":"manage_exams"})

    if isStaff or isAdmin:
        num_roles += 1
        last_role = "staff"
        roles_menu.append({"text":"Staff Section","url":"manage_scantrons"})

    if isCoord or isAdmin:
        num_roles += 1
        last_role = "coordinator"
        roles_menu.append({"text":"Coordinator Section","url":"manage_versions"})

    if isInstr or isAdmin:
        num_roles += 1
        last_role = "instructor"
        roles_menu.append({"text":"Instructor Section","url":"view_rolls"})

    if num_roles == 1:
        # we redirect them to the only section they can access
        # we should make the redirect respect if this is on mthsc1.clemson.edu
        #TODO

        if last_role == "admin":
            # manage_exams.py
            return redirect(url_for('manage_exams.manage_exams'))
        elif last_role == "staff":
            # manage_scantrons.py
            return redirect(url_for('manage_scantrons.manage_scantrons'))
        elif last_role == "coordinator":
            # manage_versions.py
            return redirect(url_for('manage_versions.manage_versions'))
        elif last_role == "instructor":
            # manage_grades.py
            return redirect(url_for('manage_grades.manage_grades'))

    kwargs['roles_menu']=roles_menu
    
    return render_template("main.html", **kwargs)
