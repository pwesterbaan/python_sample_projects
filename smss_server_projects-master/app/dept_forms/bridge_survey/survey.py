#!/var/www/mthsc/common/venv/bin/python3

import os
from pathlib import Path

from flask import Flask, render_template, request, Markup, Blueprint, make_response, current_app

from . import bridge_survey_bp
from .survey_lib import survey_lib
from extensions import htpasswd, sess

sl=survey_lib()

@bridge_survey_bp.route('/survey',methods=['GET','POST'])
@htpasswd.required
def survey(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Bridge Course Survey"

    form      = request.form
    form_name = form.get('form_name')
    
    content = ''
    if form_name == "bridge_survey":
        sl.save_response(form)
        content = "Your response has been recorded. You can close this page."

        kwargs['content'] = Markup(content)
        return render_template('bridge_survey/messages.html', **kwargs)
    else:
        #templates directory where questions.html resides
        content=open(os.path.join(Path(os.path.dirname(__file__)).parent,\
                                  'templates/bridge_survey/questions.html'),"r").read()
        kwargs['content'] = Markup(content)
        return render_template('bridge_survey/survey.html', **kwargs)
