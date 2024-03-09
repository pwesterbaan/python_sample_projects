#!/var/www/mthsc/common/venv/bin/python3

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import dept_forms_bp
from .candidate_lib import candidate_lib
from .dept_forms_lib import dept_forms_lib
from extensions import htpasswd, sess

cand_lib=candidate_lib()
dfl=dept_forms_lib()

@dept_forms_bp.route('/evaluate_candidate',methods=['POST'])
@htpasswd.required
def evaluate_candidate(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Candidate\u00A0List"

    form                 = request.form
    form_id              = form.get('form_id')
    candidate_id         = form.get('candidate_id')
    evaluate_form_submit = form.get('evaluate_form_submit')

    person_id = dfl.get_person_id_from_username(user)

    if evaluate_form_submit==1:
        content=dfl.evaluate_candidate_form_submit(person_id,candidate_id,form_id,form,cand_lib)
        return content
    
    name, school, visit_dates, form_id, form_html=dfl.get_form_html(person_id,candidate_id,cand_lib)

    # render empty template
    kwargs['candidate_name']       = Markup(name)
    kwargs['candidate_school']     = Markup(school)
    kwargs['candidate_vist_dates'] = Markup(visit_dates)
    kwargs['candidate_id']         = Markup(str(candidate_id))
    kwargs['form_id']              = Markup(str(form_id))
    kwargs['form_date']            = Markup(form_html)
    
    return render_template("evaluate_candidate.html", **kwargs)
