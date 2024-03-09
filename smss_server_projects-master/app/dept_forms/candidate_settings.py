#!/var/www/mthsc/common/venv/bin/python3

from flask import Flask, render_template, request, Markup, Blueprint, flash

from . import dept_forms_bp
from .candidate_lib import candidate_lib
from extensions import htpasswd, sess

cand_lib=candidate_lib()

@dept_forms_bp.route('/candidate_settings',methods=['GET','POST'])
@htpasswd.required
def candidate_settings(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Candidate\u00A0Settings"

    form       = request.form
    form_name  = form.get('form_name')
    pool_id    = form.get('pool_id', cand_lib.get_current_pool_id())

    if form_name == "settings_form":
        cand_lib.set_current_pool(pool_id)
        flash("""settings saved""")

    # render empty template
    kwargs['pool_dropdown']  = Markup(cand_lib.get_pool_dropdown_html(pool_id))
    
    return render_template("candidate_settings.html", **kwargs)
