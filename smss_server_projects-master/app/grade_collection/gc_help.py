#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Blueprint, url_for, redirect, Markup

from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd

gl=grade_collection_lib()

@grade_collection_bp.route('/help', methods=['GET'])
@grade_collection_bp.route('/<role>_help',methods=['GET'])
@htpasswd.required
def help(user, role=''):
    
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"

    current_role=role.capitalize()

    kwargs['title']=f"Grades: {current_role} Help"
    kwargs['user']=user

    isAdmin=gl.is_admin(user)
    isStaff=gl.is_staff(user)
    isCoord=gl.is_coordinator(user, gl.get_current_semester(), gl.get_current_year())
    isInstr=gl.is_instructor(user, gl.get_current_semester(), gl.get_current_year())

    colorCode="#000000"
    if role == 'admin' and isAdmin:
        kwargs['role_box'] = Markup(gl.get_role_box(user,"admin"))
        kwargs['menu'] = Markup(gl.get_admin_menu())
        colorCode="#A60000"
    elif role == 'staff' and (isAdmin or isStaff):
        kwargs['role_box'] = Markup(gl.get_role_box(user,"staff"))
        kwargs['menu'] = Markup(gl.get_staff_menu())
        colorCode="#009933"
    elif role == 'coord' and (isAdmin or isCoord):
        kwargs['role_box'] = Markup(gl.get_role_box(user,"coordinator"))
        kwargs['menu'] = Markup(gl.get_coord_menu())
        colorCode="#F66733"
    elif role == '' and (isAdmin or isInstr):
        kwargs['role_box'] = Markup(gl.get_role_box(user,"instructor"))
        kwargs['menu'] = Markup(gl.get_menu())
        colorCode="#522D80"
    else: #page is not implemented/doesn't exist --> Redirect to main
        return redirect(url_for('main.main'))

    helpMessage=f"""<div style="width: 600px; margin: 30px auto; border: solid 2px {colorCode}; padding: 20px;">
If you have a technical problem with the grade collection site, please contact Kevin Hedetniemi (hedetni@clemson.edu). If you think there is something wrong with your course or grades please contact Ellen Breazel (ehepfer@clemson.edu).
</div>"""

    kwargs['content'] = Markup(helpMessage)
    return render_template("help.html", **kwargs)
