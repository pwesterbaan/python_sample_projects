from flask import Flask, Blueprint

ug_course_pages_bp = Blueprint('ug_course_pages', __name__,
                                template_folder='templates/',
                                static_folder='static',
                                url_prefix='/ug_course_pages/')

#TODO: convert course names to course id's:
#      e.g. view_course_page/math_1080 -> view_course_page/8

#import routes

from . import calculator, \
    course_page_list, \
    course_pages, \
    edit_course_page, \
    manage_content, \
    view_course_page
