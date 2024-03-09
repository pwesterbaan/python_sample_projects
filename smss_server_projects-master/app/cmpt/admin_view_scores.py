#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/<any(admin,engr):access_type>_view_scores',methods=['GET','POST'])
@cmpt_bp.route('/<any(admin,engr):access_type>_view_<any(all,best):score_type>_scores',methods=['GET','POST'])
@htpasswd.required
def admin_view_scores(user,access_type,score_type='all'):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    if "admin" == access_type:
        kwargs['title']="CMPT: View Scores"
    else:
        kwargs['title']="CMPT: View ENGR Scores"

    form             = request.form
    form_name        = form.get('form_name')
    score_type       = form.get('type',score_type)
    cohort_selection = form.get('cohort','none')

    print(form)

    cur_semester = cmpt.get_current_semester()
    cur_year     = cmpt.get_current_year()

    cohorts_html = cmpt.get_html_for_cohort_html(access_type,score_type,cohort_selection)
    scores_html, count_html = cmpt.get_html_for_scores_and_count_html(access_type,score_type, cohort_selection)

    # render template
    if "admin" == access_type:
        kwargs['menu']      = Markup(cmpt.get_admin_menu())

    kwargs['cohort_picker'] = Markup(cohorts_html)
    kwargs['scores']        = Markup(scores_html)
    kwargs['total_count']   = Markup(count_html)

    return render_template("admin_view_scores.html", **kwargs)
