from flask import Flask, Blueprint

cmpt_bp = Blueprint('cmpt',__name__,
                    template_folder='templates/',
                    static_folder='static',
                    url_prefix='/cmpt/')

from . import access_debugger,\
    access_test,\
    admin_add_students,\
    admin_admissions_list,\
    admin_download_ids,\
    admin_download_scores,\
    admin_excel_download,\
    admin_lookup_scores,\
    admin_manage_cohorts,\
    admin_settings,\
    admin_view_scores,\
    admin_view_students,\
    claim_credit,\
    cmpt_redirects,\
    cmpt_scores,\
    course_credit,\
    faq,\
    information,\
    maintenance,\
    tech_support,\
    view_cmpt_score
