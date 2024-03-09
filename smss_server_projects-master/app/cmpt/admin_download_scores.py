#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io
import os
from pathlib import Path

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/admin_download_scores',methods=['GET','POST'])
@htpasswd.required
def admin_download_scores(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: Download ALEKS Scores"

    form             = request.form
    form_name        = form.get('form_name')

    msg="""
<p>Scores are downloaded every morning from ALEKS, but you can manually download them now by clicking on the button below. It may take several seconds to download the data.</p>
<div>
<form action="admin_download_scores" method="POST">
<input type="submit" value="Download scores from ALEKS">
<input type="hidden" name="form_name" value="download_scores">
</form>
</div>"""
    if form_name == "download_scores":
        msg=cmpt.admin_download_ALEKS_scores()

    # render template
    kwargs['menu']   = Markup(cmpt.get_admin_menu())
    kwargs['msg']    = Markup(msg)

    return render_template("admin_download_scores.html", **kwargs)
