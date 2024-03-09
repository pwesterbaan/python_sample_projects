#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/admin_lookup_scores',methods=['GET','POST'])
@htpasswd.required
def admin_lookup_scores(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: Lookup Scores"

    form         = request.form
    xid          = form.get('xid','')
    username     = form.get('username','')

    scores_html=""

    if len(xid)>0 or len(username)>0:
        scores_html,xid,username=cmpt.get_html_for_admin_lookup_scores(xid,username)

    # render template
    kwargs['menu']    = Markup(cmpt.get_admin_menu())
    kwargs['scores']  = Markup(scores_html)
    kwargs['username']= Markup(username)
    kwargs['xid']     = Markup(xid)

    return render_template("admin_lookup_scores.html", **kwargs)
