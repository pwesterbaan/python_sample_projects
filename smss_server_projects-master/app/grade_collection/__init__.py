from flask import Flask, Blueprint

grade_collection_bp = Blueprint('grade_collection', __name__,
                                template_folder='templates/',
                                static_folder='static',
                                url_prefix='/grade_collection/')

#import routes

from . import actions, \
    admin_reports, \
    admin_term_end, \
    coord_reports, \
    coord_rolls, \
    coord_term_end, \
    course_manager, \
    gc_help, \
    main, \
    manage_exams, \
    manage_grades, \
    manage_scantrons, \
    manage_versions, \
    reports, \
    settings, \
    term_end, \
    view_grades, \
    view_rolls
    # gc_redirects
