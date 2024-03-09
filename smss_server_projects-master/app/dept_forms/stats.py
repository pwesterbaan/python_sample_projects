#!/var/www/mthsc/common/venv/bin/python3

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import dept_forms_bp
from .candidate_lib import candidate_lib
from extensions import htpasswd, sess

cand_lib=candidate_lib()

@dept_forms_bp.route('/stats',methods=['GET','POST'])
@htpasswd.required
def stats(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Evaluation\u00A0Stats"

    form         = request.form
    candidate_id = form.get('candidate_id')

    candidate_info = cand_lib.get_candidate_info(candidate_id)

    questions = cand_lib.get_stats(candidate_id, 1)
    employee_stats_html = cand_lib.generate_stat_form(questions)

    questions = cand_lib.get_stats(candidate_id, 2)
    student_stats_html = cand_lib.generate_stat_form(questions)


    # render empty template
    kwargs['candidate_name']      = Markup(f"""{candidate_info.get('first_name')} {candidate_info.get('last_name')}""")
    kwargs['employee_stats_html'] = Markup(employee_stats_html)
    kwargs['student_stats_html']  = Markup(student_stats_html)
    
    return render_template("stats.html", **kwargs)
