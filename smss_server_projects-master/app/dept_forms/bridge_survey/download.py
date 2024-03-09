#!/var/www/mthsc/common/venv/bin/python3

import os
from pathlib import Path

from flask import Flask, render_template, request, Markup, Blueprint, make_response, current_app
import flask_excel as excel

from . import bridge_survey_bp
from .survey_lib import survey_lib
from extensions import htpasswd, sess

sl=survey_lib()

@bridge_survey_bp.route('/NewDownload',methods=['GET'])
@htpasswd.required
def download_survey_responses(user):
    data = sl.get_survey_data()

    fileContents=[]

    if(len(data)>0):
        fileContents.append(list(data[0].keys()))
        for row in data:
            fileContents.append(list(row.values()))
    response=excel.make_response_from_array(fileContents,
                                            "csv",
                                            filename="bridge_course_survey_data.csv")
    return response
