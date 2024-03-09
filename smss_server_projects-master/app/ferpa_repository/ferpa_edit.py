#ferpa_edit.py
import datetime
import os
from pathlib import Path
import sys

from flask import Flask, render_template, request, redirect, url_for, Markup, flash, current_app, Blueprint

from . import ferpa_repository_bp
from .ferpa_lib import ferpa_lib
from extensions import htpasswd

fl=ferpa_lib()

@ferpa_repository_bp.route('/admin_ferpa_edit', methods=['GET','POST'])
@ferpa_repository_bp.route('/ferpa_edit', methods=['GET','POST'])
@htpasswd.required
def ferpa_edit(user):
    # passing all arguments in a single dictionary named 'kwargs'
    # if dictionary key is not defined, page renders and ignores KeyError
    # https://stackoverflow.com/questions/334655/passing-a-dictionary-to-a-function-as-keyword-parameters
    kwargs={} #Dictionary that contains variables passed to template
    kwargs['debug']="use this to print vars when debugging" 

    username = user #TODO 
    # username = fl.get_employee_username(username)
    kwargs['username']=username
    kwargs['nav']=fl.nav
    kwargs['title']='Edit FERPA Form'
    kwargs['description']="Edit FERPA Forms"

    if len(username) == 0:
        kwargs['message'] = Markup("You are not listed as an employee in the Mathematical Sciences department. Please contact one of the departmental secretaries if you believe this is incorrect. Click <a href=http://www.clemson.edu/ces/math/index.html>here</a> to go to the Mathematical Sciences page.")
        kwargs['description']="Edit Forms Error"
        return render_template("ferpa_error.html", **kwargs)

    semesters = ["spring", "summer I", "summer II", "fall"]
    semesters_text = ["Spring", "Summer I", "Summer II", "Fall"]
    kwargs['semester_dict']=dict(zip(semesters, semesters_text))

    now = datetime.datetime.now()
    years = list(range(2012, now.year + 2))
    years_text = list(years)
    kwargs['year_dict']=dict(zip(years,years_text))

    if request.method == 'POST':
        forms=""
        form_data=""

        kwargs['directions']=" Edit the information for this form and choose a new PDF file of the form if needed. Once you are done, click the button at the bottom of the page."
        kwargs['uploadMessage']= Markup("<div>There is a file uploaded already. If you want to <br/>replace it with another file, select one from your <br/>computer otherwise the current file will be saved.</div>")

        form_data=request.form

        #Info to populate webpage
        form_id=fl.get_int_value(form_data, "form_id")
        form_info=fl.get_form_info(form_id)
        kwargs['selected_semester']=form_info.get('semester')
        kwargs['first_name']=form_info.get('first_name')
        kwargs['last_name']=form_info.get('last_name')
        kwargs['cuid']=form_info.get('CUID')
        kwargs['selected_year']=str(fl.get_int_value(form_info,"year"))
        kwargs['form_id']=form_id

        #Info to update form
        new_semester=form_data.get('semester')
        new_year=form_data.get('year')
        new_first_name=form_data.get('first_name')
        new_last_name=form_data.get('last_name')
        new_cuid=form_data.get('cuid')
        uploader=form_info.get('uploaded_by')

        #Template details
        kwargs['nav']=fl.nav
        kwargs['forms']=forms
        kwargs['form_data']=form_data
        kwargs['buttonAction']=url_for('ferpa_repository.ferpa_edit')
        kwargs['buttonValue']='ferpa_edit'
        kwargs['buttonText']='Save FERPA form'

        if form_data.get('method') in ['edit','delete'] and uploader != username.upper():
            kwargs['message'] = Markup("You can only edit forms that you uploaded. Click <a href=ferpa_view>here</a> to return to viewing forms.")
            kwargs['description']="Edit Forms Error"
            return render_template("ferpa_error.html", **kwargs)
        else: #Correct user or is admin
            # Delete logic moved into ferpa_view so search query is re-displayed PW 2021-05-06
            if form_data.get('method')=='edit':
                #Hit edit button. Has not uploaded or updated yet
                return render_template("ferpa_upload.html", **kwargs)
            else:
                #Is on the ferpa_upload.html page editing an existing file
                file = request.files['ferpa_file']
                if str(file) != '<FileStorage: \'\' (\'application/octet-stream\')>':
                    #New file exist
                    filename=file.filename
                    file_type=file.content_type #MIME type
                    if file_type in current_app.config['ALLOWED_FILETYPE']:
                        #Correct filetype
                        file_extension = 'pdf' # already checking if pdf
                        #file_extension = filename.split(".")[-1].lower()
                        fl.delete_ferpa_form(form_id)
                        new_filename=fl.save_ferpa_form(new_semester, new_year, new_cuid, new_first_name, new_last_name, file, file_extension, username)

                        # We could add a try-catch block to check if this is successful
                        file.save(os.path.join(current_app.config['UPLOAD_FOLDER'], new_filename))
                        flash("FERPA form pdf updated")
                        return redirect(url_for('ferpa_repository.ferpa_view'))
                    else:
                        #wrong file type
                        flash("The file type is not valid")
                        return render_template("ferpa_repository.ferpa_upload.html", **kwargs)
                else:
                    #No file. Updating info only
                    fl.update_ferpa_form(form_id, new_semester, new_year, new_cuid, new_first_name, new_last_name, username)
                    flash("FERPA form info updated")
                    return redirect(url_for("ferpa_repository.ferpa_view"))
    else:
        #GET requests not allowed
        return redirect(url_for('ferpa_view'))
