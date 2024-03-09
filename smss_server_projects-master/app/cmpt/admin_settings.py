#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response, session, redirect, current_app

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/admin_settings',methods=['GET','POST'])
@htpasswd.required
def admin_settings(user):
    # print(session)
    # if 'user' not in session:
        # return redirect(current_app.config['SSO_LOGIN_URL'])

    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: Settings"

    form         = request.form
    form_name    = form.get('form_name')
    semester     = form.get('current_semester')
    year         = form.get('current_year')


    content=''
    msg=''
    if form_name == "save_settings":
        term = f'{int(year)}08' if semester.lower() == "fall" else f'{int(year)}01'
        cmpt.set_current_term(term)
        cmpt.set_current_semester(semester.lower())
        cmpt.set_current_year(year)

        msg = f"""<div style="margin: 15px 0px;"><span style="background: #FFFF99; border: solid 2px #000000; padding: 10px;">settings saved: {semester.title()} {year}</span></div>"""

    content += cmpt.get_html_for_admin_settings()

    # render template
    kwargs['content'] = Markup(content)
    kwargs['menu']    = Markup(cmpt.get_admin_menu())
    kwargs['msg']     = Markup(msg)

    return render_template("admin_settings.html", **kwargs)
