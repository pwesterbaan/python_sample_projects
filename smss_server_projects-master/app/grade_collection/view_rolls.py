#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, current_app, jsonify, session
# from flask_cors import CORS

from . import grade_collection_bp
from .grade_collection_lib import grade_collection_lib
from extensions import htpasswd, sess

gl=grade_collection_lib()
# CORS(manage_versions_bp)

@grade_collection_bp.route('/view_rolls',methods=['GET','POST'])
@htpasswd.required
def view_rolls(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Grades: View Rolls"
    kwargs['user']=user #TODO

    form=request.form
    form_name =form.get("form_name")
    offer_id  =form.get("offer_id", session.get('offer_id'))
    offer_info=gl.get_offer_info(offer_id)

    session['offer_id']=offer_id

    isTeaching=gl.is_teaching(user, offer_id)
    isAdmin   =gl.is_admin(user)
    isCoord   =gl.is_coordinating(user, offer_info.get('semester'), offer_info.get('year'), offer_info.get('course_id'))

    if offer_id != 0 and not isTeaching and not isAdmin and not isCoord:
        html=Markup(gl.print_error_msg_page(user))
        return render_template("msg.html", html=html)

    if form_name == "view_rolls":
        kwargs['content']=Markup(gl.get_html_to_print_course_roll(offer_id, offer_info))

    # render empty template
    kwargs['role_box'] = Markup(gl.get_role_box(user,"instructor"))
    kwargs['menu'] = Markup(gl.get_menu())
    kwargs['offers_dropdown'] = Markup(gl.get_current_offers_dropdown_html(user, offer_id))
    return render_template("view_rolls_gc.html", **kwargs)
