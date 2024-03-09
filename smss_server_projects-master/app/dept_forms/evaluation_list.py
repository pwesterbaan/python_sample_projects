#!/var/www/mthsc/common/venv/bin/python3

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import dept_forms_bp
from .candidate_lib import candidate_lib
from .dept_forms_lib import dept_forms_lib
from extensions import htpasswd, sess

cand_lib=candidate_lib()
dfl=dept_forms_lib()

@dept_forms_bp.route('/evaluation_list',methods=['GET','POST'])
@htpasswd.required
def evaluation_list(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Candidate\u00A0Evaluations"

    form = request.form

    person_id = dfl.get_person_id_from_username(user)

    # render template
    kwargs['evaluation_list'] = Markup(dfl.get_eval_list_html(person_id,cand_lib))
    
    return render_template("evaluation_list.html", **kwargs)
