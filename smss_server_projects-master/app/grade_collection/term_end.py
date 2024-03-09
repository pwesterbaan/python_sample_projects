#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, session, send_from_directory

from app import excel
from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()

@grade_collection_bp.route('/term_end',methods=['GET','POST'])
@htpasswd.required
def term_end(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: End of Term"
    kwargs['user']=user #TODO

    if not gl.is_current_coordinator(user) and not gl.is_admin(user):
        html=Markup(gl.print_error_msg_page(user))
        render_template("msg.html", html=html)

    form          =request.form
    offer_id      =form.get('offer_id', session.get('offer_id'))
    form_name     =form.get("form_name")
    action        =form.get("action")
    data          =form.get('data')

    session['offer_id']=offer_id
    offer_info = gl.get_offer_info(offer_id)

    if form_name == "term_end":
        local_filename=os.path.join(current_app.config.get('GC_REPORTS_FOLDER'),f'offer_{str(offer_id)}_{action}_report.csv')
        if action == "term_summary":
            html=gl.get_html_to_print_instructor_term_summary(offer_id,local_filename)
        elif action == "view_data":
            html=gl.get_html_to_print_instructor_term_data(offer_id,local_filename)
        elif action == "submit_data":
            html=gl.get_html_to_submit_end_of_term_data(offer_id)
        return html
    elif form_name == "submit_data":
        html=gl.submit_end_of_term_data(data, offer_id)
        return html


    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"instructor"))
    kwargs['menu'] = Markup(gl.get_menu())
    kwargs['offers_dropdown'] = Markup(gl.get_current_offers_dropdown_html(user,offer_id, "onchange=\"try_action()\""))

    return render_template("term_end.html", **kwargs)

@grade_collection_bp.route('/get_term_end', methods=['POST'])
@htpasswd.required
def get_term_end(user):
    if not gl.is_admin(user) and not gl.is_staff(user): #TODO check instructors only?
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", html=html)

    form     =request.form
    action   =form.get('action')
    filename =form.get('filename')
    offer_id=form.get('offer_id')

    local_filename=f'offer_{str(offer_id)}_{action}_report.csv'
    return send_from_directory(current_app.config['GC_REPORTS_FOLDER'], local_filename, attachment_filename=filename, as_attachment=True)
