#ferpa_upload.py
import datetime
import os
from pathlib import Path
import sys

from flask import Flask, render_template, request, redirect, url_for, Markup, flash, Blueprint, current_app, session

from . import ferpa_repository_bp
from .ferpa_lib import ferpa_lib
from extensions import htpasswd, sess

fl=ferpa_lib()

@ferpa_repository_bp.route('/ferpa_upload', methods=['GET', 'POST'])
@htpasswd.required
def ferpa_upload(user):
    # passing all arguments in a single dictionary named 'kwargs'
    # if dictionary key is not defined, page renders and ignores KeyError
    # https://stackoverflow.com/questions/334655/passing-a-dictionary-to-a-function-as-keyword-parameters
    kwargs={} #Dictionary that contains variables passed to template
    kwargs['debug']="use this to print vars when debugging" 

    #TODO use .htaccess for local username, SSO on opal
    # username = fl.get_employee_username(user)
    kwargs['username']=user
    # Check if admin, else check if staff. Must at least be an employee to access repo #TODO
    kwargs['user_role']="admin" if fl.is_admin(user) else ("staff" if fl.is_staff(user) else "employee")
    kwargs['nav']=fl.nav
    kwargs['title']="Upload FERPA Forms";
    kwargs['description']="Upload FERPA Forms"
    semesters = ["spring", "summer I", "summer II", "fall"]
    semesters_text = ["Spring", "Summer I", "Summer II", "Fall"]
    kwargs['semester_dict']=dict(zip(semesters, semesters_text))

    now = datetime.datetime.now()
    years = list(range(now.year + 1,2011,-1))
    years_text = list(years)
    kwargs['year_dict']=dict(zip(years,years_text))
    kwargs['selected_year']=session.get('selected_year',now.year)
    kwargs['selected_semester']=session.get('selected_semester',"spring")

    session['selected_year']=kwargs.get('selected_year',now.year)
    session['selected_semester']=kwargs.get('selected_semester')
    
    kwargs['directions']=" Fill out this form with information about the student and choose a PDF file of their FERPA form to upload. Once you are done, click the button at the bottom of the page."

    if len(user) == 0:
        # user is not an employee of the math department
        kwargs['message'] = Markup("You are not listed as an employee in the Mathematical Sciences department. Please contact one of the departmental secretaries if you believe this is incorrect. Click <a href=http://www.clemson.edu/ces/math/index.html>here</a> to go to the Mathematical Sciences page.")
        kwargs['description']="Upload Forms Error"
        return render_template("ferpa_error.html", **kwargs)

    # Only admin and staff can upload forms
    if not fl.is_admin(user) and not fl.is_staff(user):
        # user is not an admin or staff of the math department
        kwargs['message'] = Markup("You are not listed as an administrator or eligible staff member of this system. Please contact one of the departmental secretaries if you believe this is incorrect. Click <a href=http://www.clemson.edu/ces/math/index.html>here</a> to go to the Mathematical Sciences page.")
        kwargs['description']="Upload Forms Error"
        return render_template("ferpa_error.html", **kwargs)

    forms=""
    form_data=""

    ## logic for ferpa upload
    
    if request.method == 'POST':
        # Dictionary entries default to ""
        # form_data.get(KEY,*default value)
        form_data=request.form
        cuid       = form_data.get('cuid')
        first_name = form_data.get('first_name')
        last_name  = form_data.get('last_name')
        semester   = form_data.get('semester')
        year       = form_data.get('year',session.get('year'))
        session['selected_year']=year

        file = request.files.get('ferpa_file')
        filename=file.filename

        if filename=='':
            flash("You need to specify a file")
            return render_template("ferpa_upload.html", **kwargs)

        file_type = file.content_type
        # file_extension = filename.split(".")[-1].lower()
        file_extension = "pdf"
        if file_type in current_app.config.get('ALLOWED_FILETYPE'):
            new_filename=fl.save_ferpa_form(semester, year, cuid, first_name, last_name, file, file_extension, user)

            # Since current_app.config is used to set the upload folder,
            # saving the file happens outside of ferpa_lib.py
            # secure_filename() is not used since we create a random name

            # We could add a try-catch block to check if this is successful
            file.save(os.path.join(current_app.config.get('FERPA_UPLOAD_PATH'), new_filename))
            flash("FERPA form was successfully saved.")
        else:
            flash("The file type is not valid.")

    kwargs['forms']=forms
    kwargs['form_data']=form_data
    kwargs['fileRequired']='required'
    kwargs['buttonAction']=url_for('ferpa_repository.ferpa_upload')
    kwargs['buttonValue']='ferpa_upload'
    kwargs['buttonText']='Upload FERPA form'

    return render_template("ferpa_upload.html", **kwargs)
