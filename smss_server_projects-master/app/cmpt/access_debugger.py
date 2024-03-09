#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io
import os

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/access_debugger',methods=['GET','POST'])
@htpasswd.required
def access_debugger(user):
    kwargs={}
    kwargs['username']=user
    kwargs['debug']=os.environ
    kwargs['title']="CMPT: ALEKS DEBUGGER"
    mthscID=cmpt.get_mthscID_from_username(user)
    mthscID="47270d192d997524d712cc3a7243d2fd" #TODO: pulled from cmpt db 2022-05-27
    # mthscID=4
    kwargs['mthscID']=mthscID
    kwargs['class_code']= "Not defined"

    if cmpt.is_eligible(mthscID):
        student_info = cmpt.get_student_info(mthscID)
        class_code = cmpt.get_ALEKS_class_code(student_info.get('cur_student_type'), student_info.get('cur_semester'), student_info.get('cur_year'))
        kwargs['class_code']=class_code
        kwargs['eligibility']=Markup("<p>You are eligible</p>")

    return render_template("access_debugger.html", **kwargs)
