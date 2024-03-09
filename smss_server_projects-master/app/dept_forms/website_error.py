#!/var/www/mthsc/common/venv/bin/python3

import os

from flask import Flask, render_template, request, Markup, Blueprint, flash

from . import dept_forms_bp
from .dept_forms_lib import dept_forms_lib
from extensions import htpasswd, sess

dfl=dept_forms_lib()

@dept_forms_bp.route('/website_error',methods=['GET','POST'])
@htpasswd.required
def website_error(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Website\u00A0Error"

    form       = request.form
    email      = form.get('email')
    email_text = form.get('email_text')
    ref_url    = form.get('ref_url',request.headers.get('referer',''))
    uploading  = form.get('submit_website_error')

    if uploading:
        email_text = "Referer URL: " + ref_url + "\n\n" + email_text

        dfl.send_email("Website Error", email_text, "hedetni@clemson.edu", False, email)
        
        flash("Report submitted, you can close this window.")
    
    # render template
    kwargs['ref_url'] = Markup(ref_url)
    
    return render_template("website_error.html", **kwargs)
