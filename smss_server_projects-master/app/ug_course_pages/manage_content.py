#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, session, redirect, url_for, flash

# from app import excel
from . import ug_course_pages_bp
from .course_page_lib import course_page_lib
from extensions import htpasswd, sess

cpl=course_page_lib()

@ug_course_pages_bp.route('/manage_content.py',methods=['GET'])
@ug_course_pages_bp.route('/manage_content',methods=['GET'])
def redirect_manage_content():
    course_id=request.args.get('course_id')
    if course_id:
        return redirect(f'manage_content/{course_id}')
    else:
        flash(f"No such course exists")
        return redirect(url_for("ug_course_pages.course_page_list"))

@ug_course_pages_bp.route('/manage_content/<course_id>',methods=['GET','POST'])
@htpasswd.required
def manage_content(user,course_id):
    kwargs={}
    kwargs['debug']        = "use this to print vars when debugging"
    kwargs['course_id']    = course_id
    course_title           = cpl.get_course_title(str(course_id))
    kwargs['course_title'] = course_title
    kwargs['title']        = f"Manage Content for {course_title}"
    kwargs['user']         = user #TODO

    form        = request.form
    action      = form.get('action')
    description = form.get('description','').replace("\t","").replace("\n","")
    file_data   = request.files.get('item_file')
    item_id     = form.get('item_id')

    if not str(course_id) in [str(course.get('course_id')) for course in cpl.get_course_list(user)]:
        flash(f"""You can't edit the {course_title or "selected"} course page.""")
        return redirect(url_for("ug_course_pages.course_page_list"))

    if action == "add":
        simple_filename=file_data.filename
        #generate a unique, random filename and record in database
        uploaded_filename=cpl.add_new_uploaded_item(course_id,simple_filename,description)
        #save file locally
        file_data.save(os.path.join(current_app.config.get('COURSE_PAGE_FILE_PATH'), uploaded_filename))
        flash("File successfully uploaded.")
    elif action == "delete":
        cpl.delete_uploaded_item(item_id,current_app.config.get('COURSE_PAGE_FILE_PATH'))
        flash("item deleted")

    # render template
    kwargs['content'] = Markup(cpl.get_html_manage_content(course_id))

    return render_template("manage_content.html", **kwargs)
