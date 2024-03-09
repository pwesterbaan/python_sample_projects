#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, json, jsonify, session, redirect, url_for, flash

# from app import excel
from . import ug_course_pages_bp
from .course_page_lib import course_page_lib
from extensions import htpasswd, sess

cpl=course_page_lib()

@ug_course_pages_bp.route('/course_page_list',methods=['GET'])
@htpasswd.required
def course_page_list(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Course Pages"
    kwargs['user']=user #TODO

    courses = cpl.get_course_list(user.upper())

    if len(courses) == 0:
        course_list = "<p>There are no courses that you can edit.</p>"
    else:
        course_list = "<ul>"

        for course in courses:
            course_list += f"""
    <li><a href="edit_course_page/{course.get('course_id')}" target="_blank">{course.get('prefix')} {course.get('course_num')}</a></li>
"""

        course_list += "</ul>"

    course_list += """<br><p><a href="/ug_course_pages/course_pages">View All Course Pages</a></p>"""

    admins = ('HEDETNI','JDYKEN','EHEPFER','PWESTER')
    if admins.count(user.upper()) > 0:
        course_list += """<br><p><a href="admin.php">Manage Course Page Editor Lists</a></p>"""

    # render template
    kwargs['course_list'] = Markup(course_list)

    return render_template("course_page_list.html", **kwargs)
