#!/var/www/mthsc/common/venv/bin/python3

import daemon
import os
# import subprocess
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, session, redirect, url_for, flash

# from app import excel
from . import student_info_bp
from .student_info_lib import student_info_lib
from extensions import htpasswd, sess

sil=student_info_lib()

@student_info_bp.route('/load_info',methods=['GET','POST'])
@student_info_bp.route('/<action>_student_info',methods=['GET','POST'])
@htpasswd.required
def load_info(user,action="download"):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Student Info"
    kwargs['header_title']="Load Student Info"
    kwargs['user']=user #TODO

    form        = request.form
    downloading = form.get('load_info_submit',False)

    content = ""
    selected = 0
    print(form)
    
    if downloading:
        os.system("/var/www/mthsc/html/app/student_info/download_Banner_info.py")
        content="""<div style="text-align: center;">Info download has been started. Click the "Download Data" tab above to check if it is done.</div>"""

    elif action == "view":
        selected = 1
        content = sil.get_view_student_info_html()
    elif sil.get_Banner_download_running_status()=='True':
        content = """<div style="margin: 10px;">The info is currently being downloaded from Banner. It should be done shortly.</div>"""
    else:
        content = sil.get_download_student_info_html()

    kwargs['content']  = Markup(content)
    kwargs['tab_menu'] = Markup(sil.get_tab_menu(selected))

    return render_template("load_info.html", **kwargs)
