from flask import Flask, Blueprint

dept_forms_bp = Blueprint('dept_forms', __name__,
                          template_folder='templates/',
                          static_folder='static',
                          url_prefix='/dept_forms/')

#import routes
from . import candidate_list, \
    candidate_settings, \
    evaluate_candidate, \
    evaluation_list, \
    manage_candidate, \
    manage_pool, \
    pool_list, \
    stats, \
    website_error

#import subdirs and blueprints
from .bridge_survey import bridge_survey_bp
from .cgsg_senators import cgsg_senators_bp
from .dept_info_update import dept_info_update_bp
# from .dept_info_update import dept_info_update_bp
# from .dept_vote_130430 import dept_vote_130430_bp
# from .tpr_vote_130504 import tpr_vote_130504_bp

pages=[
    bridge_survey_bp,
    cgsg_senators_bp,
    dept_info_update_bp,
    ]

for page in pages:
    dept_forms_bp.register_blueprint(page)
