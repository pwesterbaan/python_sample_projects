#!/var/www/mthsc/common/venv/bin/python3

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import dept_forms_bp
from .candidate_lib import candidate_lib
from extensions import htpasswd, sess

cand_lib=candidate_lib()

@dept_forms_bp.route('/candidate_list',methods=['GET','POST'])
@htpasswd.required
def candidate_list(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Candidate\u00A0List"

    form       = request.form
    pool_id    = form.get('pool_id', cand_lib.get_current_pool_id())

    pool_description, cand_list_html = cand_lib.get_cand_list_html(pool_id)

    # render empty template
    kwargs['pool_dropdown']    = Markup(cand_lib.get_pool_dropdown_html(pool_id))
    kwargs['pool_description'] = Markup(pool_description)
    kwargs['candidate_list']   = Markup(cand_list_html)
    
    return render_template("candidate_list.html", **kwargs)
