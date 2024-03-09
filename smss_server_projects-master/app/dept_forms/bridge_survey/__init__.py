from flask import Flask, Blueprint

bridge_survey_bp = Blueprint('bridge_survey', __name__,
                             url_prefix='/bridge_survey/')

from . import survey, download
