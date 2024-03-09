#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import dept_info_update_bp
# from dept_forms.dept_forms_lib import dept_forms_lib
from .dept_info_update_lib import dept_info_update_lib
from extensions import htpasswd, sess

dil=dept_info_update_lib()

@dept_info_update_bp.route('/info_update',methods=['GET','POST'])
@htpasswd.required
def info_update(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Information Update"

    content=""
    kwargs['content'] = Markup(content)
    return render_template("dept_info_update/info_update.html",**kwargs)
