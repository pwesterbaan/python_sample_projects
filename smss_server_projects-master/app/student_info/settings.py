#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, session, redirect, url_for, flash

# from app import excel
from . import student_info_bp
from .student_info_lib import student_info_lib
from extensions import htpasswd, sess

sil=student_info_lib()

@student_info_bp.route('/settings',methods=['GET','POST'])
@htpasswd.required
def settings(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Student Info: Settings"
    kwargs['header_title']="Student Info Settings"
    kwargs['user']=user #TODO

    form     = request.form
    semester = form.get('semester')
    saving   = form.get('form_submitted')
    year     = form.get('year')

    if saving == "student_info_settings":
        sil.set_current_semester(semester)
        sil.set_current_year(year)

        flash("Settings were updated")

    kwargs['semester'] = Markup(sil.get_semester_dropdown(sil.get_current_semester()))
    kwargs['year']     = Markup(sil.get_year_dropdown(sil.get_current_year()))

    return render_template("settings.html", **kwargs)
