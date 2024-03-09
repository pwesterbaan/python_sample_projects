#!/var/www/mthsc/common/venv/bin/python3

import csv
import os
import re
import requests
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, json, jsonify, session, redirect, url_for
from werkzeug.utils import secure_filename

## course_manager_lib is an instance of grade_collection_lib                     ##
## some functions use 'gl' since 'gl.get_cursor()' and 'cml.get_cursor()' differ ##
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from .course_manager_lib import course_manager_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()
cml=course_manager_lib()

@grade_collection_bp.route('/course_manager',methods=['GET','POST'])
@htpasswd.required
def course_manager(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Course Manager"
    kwargs['user']=user #TODO

    if not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", content=html)

    form              =request.form
    data_file         =request.files.get('file_add_coords')
    action            =form.get('action')
    add_teacher       =form.get('add_teachers','')
    coord_id          =form.get('coord_id')
    coord_id_del      =form.get('coord_id_del')
    coords_data       =form.get('coords_data')
    course_desc       =form.get('course_desc')
    course_id         =form.get('course_id')
    course_num        =form.get('course_num')
    course_prefix     =form.get('course_prefix','')
    crn               =form.get('crn','')
    del_teachers      =form.get('delete_teachers','').split(",")
    employee_username =form.get('employee_username','')
    form_name         =form.get('form_name')
    import_semester   =form.get('import_semester')
    import_year       =form.get('import_year')
    new_semester      =form.get('new_semester')
    new_year          =form.get('new_year')
    offer_id          =form.get('offer_id',0)
    section_num       =form.get('section_num','')
    semester          =form.get('semester',cml.get_current_semester())
    usernames         =form.get('usernames','')
    year              =form.get('year',cml.get_current_year())

    # print(form)
    
    html=""
    info=""
    if request.method=="POST":
        if form_name == "view_offers":
            if action=='edit_offer_submit':
                cml.edit_course_offer(offer_id, section_num, new_semester, new_year, del_teachers, add_teacher)
            elif action=='delete_offers':
                info=cml.delete_course_offers(offer_id)

            if action == "edit_offers":
                #Generate edit offer page
                html=cml.get_html_for_edit_offers(offer_id, semester, year)
            else:
                #Generate view_offers page
                html=cml.get_html_for_view_offers(semester, year,info)
        elif form_name == "add_offers":
            if action =="add_offer_upload":
                info=cml.add_offer_upload(usernames,course_prefix,course_num,section_num,crn,semester,year)
            elif action =="get_banner_offers":
                result=cml.download_Banner_course_offers(semester,year)
                info=f"""{'Data successfully uploaded.' if result[0] else result[1]}"""
            html=cml.get_html_for_add_offers(semester,year,info)
        elif form_name == "view_coords":
            coord_edit=''
            if action == "upload_edit_coords":
                result = cml.delete_coordinator(coord_id)
                cml.add_course_coordinator(course_prefix,course_num,employee_username,semester,year)
                info='Course coordinator updated'
            elif action == "delete_coords":
                info=cml.delete_course_coords(int(coord_id))
            elif action == "edit_coords":
                coord_edit=coord_id
            elif action == "import_coords":
                info=cml.import_course_coordinators(semester,year,import_semester,import_year)
            html=cml.get_html_for_view_coords(semester,year,info,coord_edit)
        elif form_name == "add_coord":
            if action == "upload_new_coord":
                if data_file.filename:
                    #read csv, decode from bytes to str, put contents into coords_data
                    coords_data=data_file.stream.read().decode('UTF-8')
                if len(coords_data)==0:
                    info="Form is empty. Either upload a file or use the text box below."
                else:
                    info=cml.upload_add_coords(semester,year,coords_data)
            html=cml.get_html_for_add_coords(semester,year,info)
        elif form_name == "create_course":
            if action == "upload_new_course":
                info=cml.create_course(course_prefix,course_num,course_desc)
                pass
            # html=cml.info_box('form_name=create_course')
            html=cml.get_html_for_create_course(semester,year,info)
        elif form_name == "settings":
            if action == "save_settings":
                cml.set_current_semester(semester)
                cml.set_current_year(year)
                info = "Settings were updated"
                pass
            html=cml.get_html_for_settings_page(semester, year, info)
        else:
            html=cml.info_box('Invalid choice')
        return html

    # render empty template
    # 'role_box' and 'menu' depend on grade_collection db
    kwargs['role_box'] = Markup(gl.get_role_box(user,"admin"))
    kwargs['menu'] = Markup(gl.get_admin_menu())
    kwargs['semester_dropdown'] = Markup(gl.get_semester_dropdown(semester, " onchange=\"try_action()\""))
    kwargs['year_dropdown'] = Markup(gl.get_year_dropdown(year, " onchange=\"try_action()\"", first_year=2008))

    return render_template("course_manager.html", **kwargs)
