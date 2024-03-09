#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/admin_view_students',methods=['GET','POST'])
@htpasswd.required
def admin_view_students(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: View Students"

    form             = request.form
    form_name        = form.get('form_name')
    mthscID          = form.get('mthscID',"")
    student_type     = form.get('student_type','new')

    cur_semester = cmpt.get_current_semester()
    cur_year     = cmpt.get_current_year()

    info=""

    if mthscID != "":
        #cmpt.remove_from_eligibility_list(delete, student_type, cur_semester, cur_year)
        cmpt.remove_from_eligibility_list(mthscID, student_type, cur_semester, cur_year)
        info = "Person(s) removed from list."

    content=cmpt.get_html_for_admin_view_students(student_type,cur_semester,cur_year)

    student_types = cmpt.get_current_student_types()
    student_types = [temp.get('student_type') for temp in student_types]
    student_types.insert(0, "all")
    student_type_dropdown = cmpt.get_dropdown_html(student_types, student_types, student_type, "student_type")

    # render template
    kwargs['content']               = Markup(content)
    kwargs['info']                  = Markup(info)
    kwargs['menu']                  = Markup(cmpt.get_admin_menu())
    kwargs['student_type_dropdown'] = Markup(student_type_dropdown)

    return render_template("admin_view_students.html", **kwargs)
