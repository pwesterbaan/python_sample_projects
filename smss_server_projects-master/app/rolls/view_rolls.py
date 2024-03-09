#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io

from flask import Flask, render_template, request, Markup, Blueprint, make_response, redirect

from . import rolls_bp
from .rolls_lib import rolls_lib
from extensions import htpasswd, sess

rl=rolls_lib()

@rolls_bp.route('/view_rolls',methods=['GET','POST'])
@htpasswd.required
def view_rolls(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Rolls: View"
    kwargs['user']=user #TODO

    current_semester=rl.get_current_semester()
    current_year=rl.get_current_year()

    form      =request.form
    form_name =form.get('form_name')

    if form_name == "download_Banner_rolls":
        #Download rolls from Banner to our database
        #then redisplay page
        rl.download_full_rolls()

    # render empty template
    kwargs['semester'] = current_semester.title().replace("Ii","II")
    kwargs['year'] = current_year
    kwargs['content'] = Markup(rl.get_html_for_view_rolls())

    return render_template("view_rolls.html", **kwargs)

@rolls_bp.route('/settings')
def redirect_gc_settings():
    #grade collection settings control the rolls settings
    return redirect("/grade_collection/settings")

#PW 2022-05-23: Following endpoints were copied from the old system.
#               Not sure if these are still in use
@rolls_bp.route('/view_rolls_download',methods=['GET'])
@htpasswd.required
def view_rolls_download(user):
    rolls=rl.get_current_rolls()
    csvList=[]
    for record in rolls:
        csvList.append([record.get('term_code'), record.get('subject_code'), record.get('course_number'), record.get('section_number'), record.get('xid')])

    si = io.StringIO()
    cw = csv.writer(si,delimiter='\t')
    cw.writerows(csvList)
    output = make_response(si.getvalue())

    today=datetime.datetime.now()
    filename=f"""rolls_{today.strftime("%Y%m%d_%H%M")}.txt"""
    # print(f"""filename: {filename}""")
    output.headers["Content-Disposition"] = f"""attachment; filename={filename}"""
    output.headers["Content-type"] = "text/csv"
    return output

@rolls_bp.route('/view_full_rolls',methods=['GET'])
@htpasswd.required
def view_full_rolls(user):
    rolls=rl.get_full_rolls()
    csvList=[]
    for record in rolls:
        csvList.append([record[0],record[1],record[2],record[3],record[4],record[6],record[5]])

    si = io.StringIO()
    cw = csv.writer(si,delimiter='\t')
    cw.writerows(csvList)
    output = make_response(si.getvalue())

    today=datetime.datetime.now()
    filename=f"""rolls_{today.strftime("%Y%m%d_%H%M")}.txt"""
    output.headers["Content-Disposition"] = f"""attachment; filename={filename}"""
    output.headers["Content-type"] = "text/csv"
    return output
