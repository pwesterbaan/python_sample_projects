#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/admin_add_students',methods=['GET','POST'])
@htpasswd.required
def admin_add_students(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: Add Students"

    form         = request.form
    form_name    = form.get('form_name');

    cur_semester = cmpt.get_current_semester()
    cur_year     = cmpt.get_current_year()

    content=''
    info=''
    if form_name == "single_add":
        day          = form.get('day')
        delete       = form.get('delete')
        month        = form.get('month')
        student_type = form.get('student_type')
        username     = form.get('username',"")
        xid          = form.get('xid')
        year         = form.get('year')
        eligible_until = f"{year}-{str(month).zfill(2)}-{str(day).zfill(2)}"
        #content = """username = %s, xid = %s, student_type = %s, eligible_until = %s""" % (username,xid,student_type,eligible_until)
        try:
            cmpt.add_to_eligibility_list(username, xid, student_type, cur_semester, cur_year, eligible_until)

            content = f"""<div style="text-align: center;">The student ({username}) was successfully added.</div>"""
        except:
            content = """<div style="text-align: center; color: #ff0000;">An error has occurred. Please review the previous request made.</div>"""
    elif form_name in ["upload_add","copy_add"]:
        if form_name == "upload_add":
            student_data = request.files.get('student_data_file').read().decode('utf-8')
        else:
            student_data = form.get('student_data')
        content = cmpt.get_html_for_add_student_upload(student_data)
    else:
        content = cmpt.get_html_for_blank_add_student_form()

    # render template
    kwargs['content'] = Markup(content)
    kwargs['menu']    = Markup(cmpt.get_admin_menu())
    kwargs['info']    = Markup(info)

    return render_template("admin_add_students.html", **kwargs)
