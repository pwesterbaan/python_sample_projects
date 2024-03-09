#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, json, jsonify, session, redirect, url_for

# from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()

@grade_collection_bp.route('/settings',methods=['GET','POST'])
@htpasswd.required
def settings(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: Settings"
    kwargs['user']=user #TODO

    if not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", content=html)

    form            =request.form
    form_name       =form.get('form_name')
    action          =form.get('action')
    access_to_add   =form.get('access_to_add')
    user_to_add     =form.get('user_to_add','').upper()
    access_to_revoke=form.get('access_to_revoke')
    user_to_revoke  =form.get('user_to_revoke','').upper()
    cur_semester    =form.get('semester')
    cur_year        =form.get('year')


    info=""
    if form_name == "settings":
        gl.set_current_semester(cur_semester)
        gl.set_current_year(cur_year)

        # save BST settings
        bst_setting_names = ["1040_bst_exam_id",
                             "1060_bst_exam_id",
                             "1070_bst_exam_id",
                             "1080_bst_exam_id"]
        for name in bst_setting_names:
            value = form.get(name)
            gl.set_setting(name, value)

        # save cutoff settings
        cutoff_names = ["discriminant_index_cutoff",
                        "FR_score_lower_cutoff",
                        "FR_score_upper_cutoff",
                        "MC_score_lower_cutoff",
                        "MC_score_upper_cutoff",
                        "normalized_score_min",
                        "normalized_score_max"]
        for name in cutoff_names:
            value = form.get(name)
            gl.set_setting(name, value)
        
        info="Settings saved"
    elif form_name == "add_access":
        gl.add_access(user_to_add,access_to_add)
        info="User Added"
    elif form_name == "revoke_access":
        gl.revoke_access(user_to_revoke,access_to_revoke)
        info="Access Revoked"

    kwargs['content']=Markup(gl.get_html_settings_content(info))
    # following lines are for managing admin/staff access
    kwargs['extra_content']=Markup(gl.get_html_extra_content())
    kwargs['access_table']=Markup(gl.get_html_access_table())
    
    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"admin"))
    kwargs['menu'] = Markup(gl.get_admin_menu())

    return render_template("settings_gc.html", **kwargs)
