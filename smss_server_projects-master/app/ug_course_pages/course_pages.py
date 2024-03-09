#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, json, jsonify, session, redirect, url_for

# from app import excel
from . import ug_course_pages_bp
from .course_page_lib import course_page_lib
from extensions import htpasswd, sess

cpl=course_page_lib()

@ug_course_pages_bp.route('/',methods=['GET'])
@ug_course_pages_bp.route('/course_pages',methods=['GET'])
@htpasswd.required
def course_pages(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Course Pages"
    kwargs['user']=user #TODO

    page_list = cpl.get_page_list()

    page_list = [f"""\t<li><a href="view_course_page/{course.get('course_id')}">{course.get('prefix')} {course.get('course_num')}</a>: {course.get('description')}</li>\n""" for course in page_list]

    page_list_html = f"""
<ul>
{"".join(page_list)}
</ul>
"""

##PW: 2022-10-23 Snippet of old code:
# we will hard code the page list for the time being until everyone is fully converted over
# page_list_html = """
#<ul>
#  <li><a href="view_course_page/course_id/1">MATH 1010</a>: Essential Mathematics for the Informed Society</li>
#  <li><a href="view_course_page/course_id/2">MATH 1020</a>: Business Calculus I</li>
#  <li><a href="https://mthsc.clemson.edu/ug/MthSc103/">MATH 1030</a>: Elementary Functions</li>
#  ...
#</ul>
# """

    # render empty template
    kwargs['content'] = Markup(page_list_html)

    return render_template("course_pages.html", **kwargs)

