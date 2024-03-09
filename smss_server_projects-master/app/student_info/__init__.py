from flask import Flask, Blueprint

student_info_bp = Blueprint('student_info', __name__,
                            template_folder='templates/',
                            static_folder='static',
                            url_prefix='/student_info/')

#import routes

from . import load_info, settings
