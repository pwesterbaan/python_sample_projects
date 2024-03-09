#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/access_test',methods=['GET','POST'])
@htpasswd.required
def access_test(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CMPT: Go To ALEKS"
    mthscID=cmpt.get_mthscID_from_username(user)

    if cmpt.is_eligible(mthscID):
        student_info = cmpt.get_student_info(mthscID)
        class_code = cmpt.get_ALEKS_class_code(student_info.get('cur_student_type'), student_info.get('cur_semester'), student_info.get('cur_year'))
        if class_code != "":
            cmpt_link = f"""<div style="text-align: center; margin-top: 40px; margin-bottom: 40px; font-family: sans-serif;"><a class="button_link" href="https://secure.aleks.com/shiblogon/sso?entityID=urn:mace:incommon:clemson.edu&class_code={class_code}">Go To ALEKS</a></div>"""
        else:
            cmpt_link = """<div style="border: solid #FF6633; background-color: #FFFFCC; padding: 5px; width: 600px; margin: 0px auto;">You are eligible to take the CMPT, but we are missing some other information. Please contact Connie McClain (<a href="mailto:vmcclai@clemson.edu">vmcclai@clemson.edu</a>) for assistance.</div>"""

        content = cmpt_link
    else:
        content = """<div style="border: solid #FF6633; background-color: #FFFFCC; padding: 5px; width: 600px; margin: 0px auto;">You are not eligible to take the CMPT. If you believe that you should be, please email Connie McClain (<a href="mailto:vmcclai@clemson.edu">vmcclai@clemson.edu</a>) with your Clemson ID number and username. </div>"""
        

    # render empty template
    kwargs['header']   = Markup(cmpt.get_header("Go To ALEKS"))
    kwargs['menu']     = Markup(cmpt.get_menu())
    kwargs['mthscID']  = mthscID
    kwargs['username'] = user.lower()
    kwargs['content']  = Markup(content)
    kwargs['footer']   = Markup(cmpt.get_footer())

    return render_template("access_test.html", **kwargs)
