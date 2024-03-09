from flask import Flask, Blueprint

ferpa_repository_bp = Blueprint('ferpa_repository', __name__,
                                template_folder='templates/',
                                static_folder='static',
                                url_prefix='/ferpa_repository/')

#import routes

from . import ferpa_edit, ferpa_upload, ferpa_view
