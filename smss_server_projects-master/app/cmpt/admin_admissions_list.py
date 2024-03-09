#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/admin_admissions_list',methods=['GET','POST'])
@htpasswd.required
def admin_admissions_list(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: Admissions List"

    form   = request.form
    update = form.get('update_list',False)

    info = ""
    if update:
        term = cmpt.get_current_term()
        cmpt.download_admissions_list_from_daps(term)
        content = "<div style=\"text-align: center; color: #FF0000;\">The list has been successfully updated.</div>"
    else:
        content = """
<form action="admin_admissions_list" method="POST" enctype="multipart/form-data">
    <input type="submit" value="Update Admissions List">
    <input type="hidden" name="update_list" value=True>
</div>
</form>
"""

    # render template
    kwargs['content'] = Markup(content)
    kwargs['menu']    = Markup(cmpt.get_admin_menu())
    kwargs['info']    = Markup(info)

    return render_template("admin_admissions_list.html", **kwargs)
