from flask import Flask, Blueprint

cgsg_senators_bp = Blueprint('cgsg_senators', __name__,
                             url_prefix='/cgsg_senators/')

from . import ballot, stats
