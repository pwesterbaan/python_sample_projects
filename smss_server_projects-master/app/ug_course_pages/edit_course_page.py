#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, session, redirect, url_for, flash, jsonify

# from app import excel
from . import ug_course_pages_bp
from .course_page_lib import course_page_lib
from extensions import htpasswd, sess

cpl=course_page_lib()

@ug_course_pages_bp.route('/edit_course_page.py',methods=['GET'])
@ug_course_pages_bp.route('/edit_course_page',methods=['GET'])
def redirect_edit_course_page():
    course_id=request.args.get('course_id')
    if course_id:
        return redirect(f'edit_course_page/{course_id}')
    else:
        flash(f"No such course exists")
        return redirect(url_for("ug_course_pages.course_page_list"))

@ug_course_pages_bp.route('/edit_course_page/<course_id>',methods=['GET','POST'])
@htpasswd.required
def edit_course_page(user,course_id):
    # print(f"""course_id: {course_id}""")
    # print(request.form)

    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['course_id']=course_id
    course_title=cpl.get_course_title(str(course_id))
    kwargs['course_title']=course_title
    kwargs['title']=f"{course_title} Course Page"
    kwargs['user']=user #TODO

    form        = request.form
    form_name   = form.get('form_name')
    added_list  = form.get('add_list')
    delete_list = form.get('delete_list')
    update_list = form.get('update_list')

    if not str(course_id) in [str(course.get('course_id')) for course in cpl.get_course_list(user)]:
        flash(f"""You can't edit the {course_title or "selected"} course page.""")
        return redirect(url_for("ug_course_pages.course_page_list"))

    if form_name == "course_page":
        if added_list:
            added_list=added_list.split(";")
            for content in added_list:
                print(f"added_list content:{content}")
                content_info = content.split("_")
                # format: new_cat_#_item_#
                cat_id = content_info[2]
                content_text = form.get(content)

                # TODO: trim off the <br>
                cpl.add_course_content(cat_id, course_id, content_text)

        if delete_list:
            delete_list=delete_list.split(";")
            for content in delete_list:
                content_info = content.split("_")
                # format: old_#
                content_id = content_info[1]

                cpl.delete_course_content(content_id)

        if update_list:
            update_list=update_list.split(";")
            for content in update_list:
                content_info = content.split("_")
                content_id =content_info[1]
                content_text = form.get(f"old_{content_id}")

                # TODO: trim off the <br>
                cpl.update_course_content(course_id, content_id, content_text)

        # if any info changed, timestamp the course
        if len(added_list) + len(update_list) + len(delete_list) > 0:
            cpl.set_last_update(course_id)

    # render template
    kwargs['content']            = Markup(cpl.get_html_edit_course_page(course_id))
    kwargs['coord_email']        = Markup(f"{cpl.get_course_coord_username(course_id)}@clemson.edu")
    kwargs['course_description'] = Markup(cpl.get_course_description(course_id))
    kwargs['last_updated']       = Markup(cpl.get_last_course_update(course_id))

    return render_template("edit_course_page.html", **kwargs)


@ug_course_pages_bp.route('/edit_course_page/<int:course_id>/course_page_ajax',methods=['GET'])
@htpasswd.required
def course_page_ajax(user,course_id):
    html=[]

    # check if this person can edit this course
    if course_id in [course.get("course_id") for course in cpl.get_course_list(user)]:
        html = [{"title": str(item.get('description')), "value": f"""/ug_course_pages/view_item.php?id={item.get('item_id')}"""} for item in cpl.get_course_items(course_id)]

    return jsonify(html)
