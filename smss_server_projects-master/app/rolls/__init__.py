from flask import Flask, Blueprint

rolls_bp = Blueprint('rolls', __name__,
                     template_folder='templates/',
                     static_folder='static',
                     url_prefix='/rolls/')

#import routes

from . import view_rolls#, view_full_rolls
