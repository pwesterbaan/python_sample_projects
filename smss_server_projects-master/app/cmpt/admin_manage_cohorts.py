#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/admin_manage_cohorts',methods=['GET','POST'])
@htpasswd.required
def admin_manage_cohorts(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: Manage Cohorts"

    form             = request.form
    delete           = form.get('delete')
    form_name        = form.get('form_name')
    student_type     = form.get('student_type')
    semester         = form.get('semester')
    year             = form.get('year')
    ALEKS_class_code = form.get('ALEKS_class_code')

    content=''
    info=''
    if form_name == "add_cohort":
        cmpt.add_cohort(student_type, semester, year, ALEKS_class_code)

        info = f"""<div style="color: #FF0000;">The cohort ({student_type}, {semester.capitalize()} {year}, {ALEKS_class_code}) was successfully created.</div>"""


    content += cmpt.get_html_for_admin_manage_cohorts(info)

    # render template
    kwargs['content'] = Markup(content)
    kwargs['menu']    = Markup(cmpt.get_admin_menu())
    kwargs['info']    = Markup(info)

    return render_template("admin_manage_cohorts.html", **kwargs)
