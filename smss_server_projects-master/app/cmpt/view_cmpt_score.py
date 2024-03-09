#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/view_cmpt_score',methods=['GET','POST'])
@htpasswd.required
def view_cmpt_score(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: View / Interpret Score"
    mthscID=cmpt.get_mthscID_from_username(user)

    form        = request.form
    form_name   = form.get('form_name')
    score_check = form.get('new_score_check',False)

    #TODO#
    login_msg = """<p>You are not logged in. Click <a href="score_login">here</a> to login and view your scores.</p>"""
    score_info = ""
    ######

    #TODO: add if statement to check for student who's logged in
    # if "REMOTE_USER" in os.environ:
    login_msg = f"""<p>You are logged in as <span class="username">{user.lower()}</span>. If this is not your username you need to close your browser and return to this page.</p>"""
    
    if score_check:
        student_info = cmpt.get_student_info(mthscID)
        class_code = cmpt.get_ALEKS_class_code(student_info.get('cur_student_type'), student_info.get('cur_semester'), student_info.get('cur_year'))

        if class_code != "":
            #cmpt.download_single_ALEKS_score(mthscID)
            cmpt.download_recent_ALEKS_scores(class_code)

    score_info=cmpt.get_html_for_score_info_table(mthscID)

    # render empty template
    kwargs['header']     = Markup(cmpt.get_header("View / Interpret Score"))
    kwargs['menu']       = Markup(cmpt.get_menu())
    kwargs['mthscID']    = mthscID
    kwargs['username']   = user.lower()
    kwargs['login_msg']  = Markup(login_msg)
    kwargs['score_info'] = Markup(score_info)
    kwargs['footer']     = Markup(cmpt.get_footer())

    return render_template("view_cmpt_score.html", **kwargs)
