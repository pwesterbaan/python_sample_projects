#ferpa_view.py
import datetime
import os
from pathlib import Path
import sys

from flask import Flask, render_template, request, Markup, flash, send_from_directory, Blueprint, current_app, url_for, session

from . import ferpa_repository_bp
from .ferpa_lib import ferpa_lib
from extensions import htpasswd, sess

fl=ferpa_lib()

@ferpa_repository_bp.route('/admin_ferpa_view', methods=['GET', 'POST'])
@ferpa_repository_bp.route('/ferpa_view', methods=['GET', 'POST'])
@htpasswd.required
def ferpa_view(user):
    # passing all arguments in a single dictionary named 'kwargs'
    # if dictionary key is not defined, page renders and ignores KeyError
    # https://stackoverflow.com/questions/334655/passing-a-dictionary-to-a-function-as-keyword-parameters
    kwargs={} #Dictionary that contains variables passed to template
    kwargs['debug']="use this to print vars when debugging"

    username=user.upper() #TODO -- remove this and use dept_info db
    kwargs['username']=username
    # Check if admin, else check if staff. Must at least be an employee to access repo #TODO
    role="admin" if fl.is_admin(username) else ("staff" if fl.is_staff(username) else "employee")
    kwargs['user_role']=role
    kwargs['nav']=fl.nav

    if len(username)== 0:
        # Markup needed here to escape the string otherwise the html code gets displayed inline
        # Marks a string as being safe for inclusion in HTML/XML output without needing to be escaped 
        kwargs['message'] = Markup("You are not listed as an employee in the Mathematical Sciences department. Please contact one of the departmental secretaries if you believe this is incorrect. Click <a href=http://www.clemson.edu/ces/math/index.html>here</a> to go to the Mathematical Sciences page.")
        return render_template("ferpa_error.html", **kwargs)

    forms=""
    form_data=""

    ## logic for ferpa view
    kwargs['title']="View FERPA Forms";
    kwargs['description']="View FERPA Forms"
    semesters = ["any", "spring", "summer I", "summer II", "fall"]
    semesters_text = ["Any", "Spring", "Summer I", "Summer II", "Fall"]
    kwargs['semester_dict']=dict(zip(semesters, semesters_text))

    now = datetime.datetime.now()
    years = list(range(now.year + 1,2011,-1))
    years_text = list(years)
    years.insert(0, 0)
    years_text.insert(0, "Any")
    kwargs['year_dict']=dict(zip(years,years_text))

    if request.method == 'GET':
        kwargs['selected_semester']=session.get('selected_semester',"Any")
        kwargs['selected_year']=session.get('selected_year',"Any")
        kwargs['last_name']=""
        kwargs['cuid']=""

    if request.method == 'POST':
        form_data=request.form
        if form_data.get('method')=='download':
            form_id    = form_data.get('form_id')
            form_info  = fl.get_form_info(form_id)
            last_name  = form_info.get('last_name')
            first_name = form_info.get('first_name')
            download_filename=f"{last_name}_{first_name}.pdf".replace(" ","_")
            return send_from_directory(current_app.config.get('FERPA_UPLOAD_PATH'), form_info["filename"], attachment_filename=download_filename, as_attachment=True)

        if form_data.get('method')=='delete':
            #Hit delete button. Nothing to upload or update
            form_id=fl.get_int_value(form_data, "form_id")
            fl.delete_ferpa_form(form_id)
            flash("FERPA form deleted")

        semester=form_data.get('semester')
        year=fl.get_int_value(form_data,'year')
        last_name=form_data.get('last_name')
        cuid=form_data.get('cuid')

        session['selected_semester']=semester
        session['selected_year']=str(year)

        kwargs['selected_semester']=semester
        kwargs['selected_year']=str(year)
        kwargs['last_name']=last_name
        kwargs['cuid']=cuid
        forms = fl.view_forms(semester, year, last_name, cuid)

    kwargs['forms']=forms
    kwargs['form_data']=form_data

    return render_template("ferpa_view.html",  **kwargs)
