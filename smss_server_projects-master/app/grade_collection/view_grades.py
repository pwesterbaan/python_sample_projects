#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, jsonify, session

from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()

@grade_collection_bp.route('/view_grades',methods=['GET','POST'])
@htpasswd.required
def view_grades(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: View Student Grades"
    kwargs['user']=user #TODO

    if not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", html=html)

    form      =request.form
    form_name =form.get('form_name')
    xid       =form.get('xid')

    print(f"form: {form}")

    content = ""
    if request.method == "POST":
        if form_name == "grade_report":
            content = gl.get_grade_report(xid)
        else:
            content = "Invalid choice"
    
    form_contents = """<p>To see a student's grades and scores, enter their XID</p>
<form method="POST" action="view_grades" name="view_grades_form">
<input type="text" style="width: 120px;" name="xid" value="" required>
<div style="margin-top: 25px;">
    <input name="submit" type="submit" id="view_grades" value="View Grades" />
    <input type="hidden" name="form_name" value="grade_report">
</div>
</form>"""

    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"admin"))
    kwargs['menu'] = Markup(gl.get_admin_menu())
    kwargs['form_contents'] = Markup(form_contents)
    kwargs['content'] = Markup(content)

    return render_template("view_grades.html", **kwargs)
