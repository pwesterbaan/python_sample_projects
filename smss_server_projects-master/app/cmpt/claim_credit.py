#!/var/www/mthsc/common/venv/bin/python3

import os
import sys

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/claim_credit',methods=['GET','POST'])
@htpasswd.required
def claim_credit(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: Claim Course Credit"

    form         = request.form
    comments     = form.get('Details')
    course_num   = form.get('CreditCourse')
    credit_type  = form.get('CreditType')
    form_name    = form.get('form_name')
    full_name    = form.get('RealName')
    xid          = form.get('XID')

    print(form)

    content=""
    error_msg=""
    error=0
    if form_name == "claim_credit":
        print(f'form_name: {form_name}\n')
        if course_num == 0:
            error_msg += "<li>You need to choose a course that you are claiming credit for.</li>\n"
            error = 1

        if (len(xid) != 9) or xid[0].upper() != "C" or not xid[1:].isdigit():
            error_msg += "<li>The XID you entered was invalid. A valid XID consists of a C and 8 numbers.</li>\n"
            error = 1

        if error == 0:
            # store the data
            cmpt.save_credit_claim(user, xid, full_name, course_num, credit_type, comments)

            # send an email to the student
            subject="Unofficial Course Credit Claim"
            email_content=f"""
You have successfully submitted an unofficial course credit claim. This information will be used in determining which math classes you are allowed to register for until your course credit claim is processed by the university. It is your responsibility to supply the required course credit information to the university. The details of your unofficial course credit claim are listed below.

        XID: {xid}
        course number: MATH {course_num}
        credit type: {credit_type}
        details: {comments}
"""
            send_to=user+"@clemson.edu"
            copy_email=""
            sent_from="Jennifer E. Van Dyken <jdyken@clemson.edu>"
            cmpt.send_email(subject, email_content, send_to, copy_email, sent_from)
            ##PW 2022-09-22: Replaced with send_email defined in common_lib
            # cmpt.send_email(user, xid, full_name, course_num, credit_type, comments)

            # show confirmation message
            content="""Your unofficial transfer credit claim has been stored. A confirmation email has been sent to your Clemson email account. If you need to claim credit for another course, fill out the form again by clicking <a href="claim_credit">here</a>."""
        else:
            # show error message
            content=f"""Your unofficial credit claim could not be processed due to the following error(s):
<ul>
{error_msg}
</ul>
Please return to the form by clicking the back button on your browser and correct these errors then resubmit your unofficial credit claim."""

    # render template
    kwargs['content'] = Markup(content)
    kwargs['header']  = Markup(cmpt.get_header("Claiming Unofficial Transfer Credit"))
    kwargs['footer']  = Markup(cmpt.get_footer())

    if error == 0:
        return render_template("claim_credit.html", **kwargs)
    else:
        return render_template("claim_credit_msg.html", **kwargs)
