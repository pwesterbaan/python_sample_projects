#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io
import os

from flask import Flask, request, Blueprint, Response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/cmpt_scores',methods=['GET','POST'])
@htpasswd.required
def cmpt_scores(user):
    kwargs={}

    form       = request.form
    key        = form.get('key')

    if key == "mthsc_cmpt_scores_101010":
        output_data = []
        # this gets the best score that we have for each student
        # only pulls students who took the test in the last year
        cmpt_scores = cmpt.get_orientation_scores()

        for score in cmpt_scores: 
            output_data.append(f"""<student><xid>{score.get('xid')}</xid><score>{score.get('score')}</score></student>""")

        xml=f"""<?xml version="1.0"?><students>{"".join(output_data)}</students>
"""
    else:
        xml=f"""<error_msg>Error: You are not authorized to view this page.</error_msg>"""

    return Response(xml,mimetype='text/xml')
