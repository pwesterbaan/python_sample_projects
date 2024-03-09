#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/maintenance',methods=['GET'])
@htpasswd.required
def maintenance(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: Unavailable"

    # render empty template
    kwargs['header'] = Markup(cmpt.get_header("Unavailable"))
    kwargs['menu']   = Markup(cmpt.get_menu())
    kwargs['footer'] = Markup(cmpt.get_footer())

    return render_template("maintenance.html", **kwargs)
