from flask import Flask, Blueprint

dept_info_update_bp = Blueprint('dept_info_update', __name__,
                                url_prefix='/dept_info_update/')

from . import info_update
