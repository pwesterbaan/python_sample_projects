#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, json, jsonify, session, redirect, url_for, flash

# from app import excel
from . import ug_course_pages_bp
from .course_page_lib import course_page_lib
from extensions import htpasswd, sess

cpl=course_page_lib()

@ug_course_pages_bp.route('/view_course_page.py',methods=['GET'])
@ug_course_pages_bp.route('/view_course_page/',methods=['GET'])
def redirect_view_course_page():
    course_id=request.args.get('course_id')
    if course_id:
        return redirect(f'view_course_page/{course_id}')
    else:
        flash(f"No course page specified")
        return redirect(url_for('ug_course_pages.course_pages'))

@ug_course_pages_bp.route('/view_course_page/<course_id>',methods=['GET'])
@htpasswd.required
def view_course_page(user,course_id):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['course_id']=course_id
    course_title=cpl.get_course_title(str(course_id))
    kwargs['course_title']=course_title
    kwargs['title']=f"{course_title} Course Page"
    kwargs['user']=user #TODO

    # render template
    kwargs['content']            = Markup(cpl.get_html_view_course_page(course_id))
    kwargs['coord_email']        = Markup(f"{cpl.get_course_coord_username(course_id)}@clemson.edu")
    kwargs['course_description'] = Markup(cpl.get_course_description(course_id))
    kwargs['last_updated']       = Markup(cpl.get_last_course_update(course_id))

    return render_template("view_course_page.html", **kwargs)

